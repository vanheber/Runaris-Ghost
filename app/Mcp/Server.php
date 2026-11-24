<?php

namespace App\Mcp;

use Illuminate\Support\Facades\Log;

/**
 * Núcleo do protocolo MCP sobre JSON-RPC 2.0.
 *
 * Transporte-agnóstico: o mesmo Server atende o HTTP (mcp/index.php) e o
 * stdio (php artisan mcp:serve). Cobre o subconjunto que importa aqui:
 * initialize, ping, tools/list e tools/call.
 *
 * ponytail: sem prompts, resources, sampling ou SSE. Se um dia precisar
 * de streaming, o caminho é responder text/event-stream no front controller
 * — o protocolo abaixo não muda.
 */
class Server
{
    /** Versões que este servidor entende, da mais nova para a mais antiga. */
    public const PROTOCOL_VERSIONS = ['2025-06-18', '2025-03-26', '2024-11-05'];

    public const NAME = 'runaris-ghost';

    public function __construct(
        protected Tools $tools,
        protected string $version = '0.0.0',
    ) {
    }

    /**
     * Trata uma mensagem JSON-RPC. Devolve a resposta, ou null quando é
     * notificação (o transporte não deve escrever nada).
     */
    public function handle(array $message): ?array
    {
        $id = $message['id'] ?? null;
        $isNotification = !array_key_exists('id', $message);
        $method = $message['method'] ?? null;

        if (!is_string($method) || $method === '') {
            return $this->error($id, -32600, 'Requisição inválida: "method" ausente.');
        }

        try {
            $result = $this->dispatch($method, $message['params'] ?? []);
        } catch (McpException $e) {
            if ($isNotification) {
                return null;
            }

            if ($e->isToolError) {
                return $this->result($id, $this->toolFailure($e->getMessage()));
            }

            return $this->error($id, -32602, $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('MCP: falha inesperada em ' . $method . ': ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            if ($isNotification) {
                return null;
            }

            return $this->error($id, -32603, 'Erro interno do servidor MCP: ' . $e->getMessage());
        }

        if ($isNotification) {
            return null;
        }

        return $this->result($id, $result);
    }

    protected function dispatch(string $method, mixed $params): mixed
    {
        $params = is_array($params) ? $params : [];

        return match ($method) {
            'initialize' => $this->initialize($params),
            'notifications/initialized', 'notifications/cancelled' => null,
            'ping' => new \stdClass(),
            'tools/list' => ['tools' => $this->tools->definitions()],
            'tools/call' => $this->callTool($params),
            default => throw new McpException(
                "Método não suportado: {$method}",
                isToolError: false,
            ),
        };
    }

    protected function initialize(array $params): array
    {
        $requested = $params['protocolVersion'] ?? null;
        $version = in_array($requested, self::PROTOCOL_VERSIONS, true)
            ? $requested
            : self::PROTOCOL_VERSIONS[0];

        return [
            'protocolVersion' => $version,
            'capabilities' => ['tools' => ['listChanged' => false]],
            'serverInfo' => [
                'name' => self::NAME,
                'title' => 'Runaris Ghost',
                'version' => $this->version,
            ],
            'instructions' => 'Santuário Digital para Autores. Use project = uuid do projeto. '
                . 'Projetos: projects_list. Árvore do manuscrito: manuscript_tree. '
                . 'Regra do projeto: arquivos .md são sagrados — só sobrescreva com manuscript_write '
                . 'ou ai_write_scene(save=true) quando o autor pedir explicitamente.',
        ];
    }

    protected function callTool(array $params): array
    {
        $name = $params['name'] ?? null;
        $arguments = $params['arguments'] ?? [];

        if (!is_string($name) || $name === '') {
            throw new McpException('Requisição inválida: "name" da ferramenta ausente.', isToolError: false);
        }

        if (!is_array($arguments)) {
            throw new McpException('Requisição inválida: "arguments" deve ser um objeto.', isToolError: false);
        }

        try {
            $value = $this->tools->call($name, $arguments);
        } catch (McpException $e) {
            return $this->toolFailure($e->getMessage());
        } catch (\Throwable $e) {
            Log::error("MCP: falha na ferramenta {$name}: " . $e->getMessage(), ['exception' => $e]);

            return $this->toolFailure('Erro ao executar ' . $name . ': ' . $e->getMessage());
        }

        return [
            'content' => [[
                'type' => 'text',
                'text' => $this->encode($value),
            ]],
            'isError' => false,
        ];
    }

    protected function toolFailure(string $message): array
    {
        return [
            'content' => [['type' => 'text', 'text' => $message]],
            'isError' => true,
        ];
    }

    protected function encode(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value === null) {
            return 'null';
        }

        return json_encode(
            $value,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION
        ) ?: 'null';
    }

    protected function result(mixed $id, mixed $result): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    }

    protected function error(mixed $id, int $code, string $message): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
    }
}
