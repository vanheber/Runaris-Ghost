<?php

namespace App\Console\Commands;

use App\Mcp\Server;
use App\Mcp\Tools;
use Illuminate\Console\Command;

/**
 * MCP por stdio — para agente rodando na MESMA máquina do Runaris Ghost.
 *
 * Na rede, o caminho é o HTTP (mcp/index.php + start-mcp.bat). Este modo
 * existe porque stdio é o transporte mais simples de testar e depurar.
 */
class McpServeCommand extends Command
{
    protected $signature = 'mcp:serve';

    protected $description = 'Servidor MCP via stdio (JSON-RPC por linha)';

    public function handle(): int
    {
        $server = new Server(app(Tools::class), (string) config('app.version'));
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        while (($line = fgets(STDIN)) !== false) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $message = json_decode($line, true);

            if (!is_array($message)) {
                fwrite(STDOUT, json_encode([
                    'jsonrpc' => '2.0',
                    'id' => null,
                    'error' => ['code' => -32700, 'message' => 'JSON inválido.'],
                ], $flags) . "\n");

                continue;
            }

            $response = $server->handle($message);

            if ($response !== null) {
                fwrite(STDOUT, json_encode($response, $flags) . "\n");
            }
        }

        return self::SUCCESS;
    }
}
