<?php

namespace App\Mcp;

use RuntimeException;

/**
 * Erro controlado do MCP.
 *
 * - $isToolError = true  -> falha de execução da ferramenta: volta como
 *   resultado (isError: true), para o modelo ler a mensagem e se corrigir.
 * - $isToolError = false -> erro de protocolo JSON-RPC (método inexistente etc.).
 */
class McpException extends RuntimeException
{
    public function __construct(string $message, public bool $isToolError = true)
    {
        parent::__construct($message);
    }
}
