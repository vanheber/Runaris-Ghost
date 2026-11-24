<?php

/**
 * Runaris Ghost — ponto de entrada do servidor MCP.
 *
 * Processo próprio, separado da interface: o app continua em 127.0.0.1 e
 * este front controller é o único arquivo exposto na rede (start-mcp.bat).
 *
 *   POST /mcp   Authorization: Bearer <MCP_TOKEN>
 *   GET  /      ficha do servidor (para conferir no navegador)
 *   GET  /health
 *
 * Boota o núcleo do Laravel pelo ConsoleKernel: carrega config, providers,
 * banco e services, mas não passa por rotas, sessão, CSRF nem middleware web.
 */

declare(strict_types=1);

use App\Mcp\Server;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Log;

ini_set('display_errors', '0');
ini_set('log_errors', '1');

$base = dirname(__DIR__);

header('X-Content-Type-Options: nosniff');

function mcp_respond(int $status, array $payload): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

try {
    require $base . '/vendor/autoload.php';

    /** @var \Illuminate\Foundation\Application $app */
    $app = require $base . '/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
} catch (\Throwable $e) {
    error_log('MCP: falha ao iniciar: ' . $e->getMessage());
    mcp_respond(500, ['error' => 'Falha ao iniciar o servidor MCP. Veja storage/logs/laravel.log.']);

    return;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$config = $app['config'];
$version = (string) $config->get('app.version', '0.0.0');

// ------------------------------------------------------------------ diagnóstico

if ($path === '/health') {
    mcp_respond(200, ['ok' => true, 'server' => Server::NAME, 'version' => $version]);

    return;
}

if ($method !== 'POST') {
    mcp_respond(200, [
        'server' => Server::NAME,
        'version' => $version,
        'mcp_version' => \App\Mcp\Tools::VERSION,
        'endpoint' => 'POST ' . $path,
        'protocol' => Server::PROTOCOL_VERSIONS[0],
        'auth' => 'Authorization: Bearer <MCP_TOKEN>',
        'hint' => 'Sem token válido nenhuma ferramenta é executada.',
    ]);

    return;
}

// ------------------------------------------------------------------- autorização

if (!$config->get('mcp.enabled')) {
    mcp_respond(404, ['error' => 'MCP desativado nesta instalação (MCP_ENABLED=false).']);

    return;
}

$expected = (string) $config->get('mcp.token');

if ($expected === '') {
    mcp_respond(503, ['error' => 'MCP_TOKEN não configurado. Rode: php artisan mcp:token']);

    return;
}

$given = '';
$authorization = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');

if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
    $given = trim($matches[1]);
} elseif (!empty($_SERVER['HTTP_X_MCP_TOKEN'])) {
    $given = trim((string) $_SERVER['HTTP_X_MCP_TOKEN']);
}

if ($given === '' || !hash_equals($expected, $given)) {
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '?');
    Log::warning("MCP: token inválido de {$ip} ({$path}).");
    mcp_respond(401, ['error' => 'Token inválido.']);

    return;
}

// ------------------------------------------------------------------ JSON-RPC/MCP

$raw = file_get_contents('php://input') ?: '';
$message = json_decode($raw, true);

if (!is_array($message) || ($message !== [] && array_is_list($message))) {
    mcp_respond(200, [
        'jsonrpc' => '2.0',
        'id' => null,
        'error' => ['code' => -32700, 'message' => 'JSON inválido ou lote não suportado.'],
    ]);

    return;
}

try {
    $server = new Server($app->make(\App\Mcp\Tools::class), $version);
    $response = $server->handle($message);
} catch (\Throwable $e) {
    Log::error('MCP: falha inesperada: ' . $e->getMessage(), ['exception' => $e]);
    mcp_respond(200, [
        'jsonrpc' => '2.0',
        'id' => $message['id'] ?? null,
        'error' => ['code' => -32603, 'message' => 'Erro interno do servidor MCP.'],
    ]);

    return;
}

if ($response === null) {
    http_response_code(202);

    return;
}

mcp_respond(200, $response);
