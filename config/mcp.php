<?php

/*
|--------------------------------------------------------------------------
| Servidor MCP (Model Context Protocol)
|--------------------------------------------------------------------------
| O MCP é um processo separado (mcp/index.php, servido pelo runtime do
| FrankenPHP via start-mcp.bat). Ele lê estas chaves do .env.
|
| MCP_TOKEN é gerado por `php artisan mcp:token` e nunca deve ir para o
| repositório: sem ele o servidor recusa toda requisição.
*/

return [
    'enabled' => (bool) env('MCP_ENABLED', true),
    'token'   => (string) env('MCP_TOKEN', ''),
    'host'    => (string) env('MCP_HOST', '0.0.0.0'),
    'port'    => (int) env('MCP_PORT', 7788),
];
