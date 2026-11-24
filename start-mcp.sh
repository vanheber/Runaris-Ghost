#!/usr/bin/env bash
#
# Runaris Ghost — servidor MCP (macOS / Linux, rodando do código-fonte)
#
# Equivalente ao start-mcp.bat do pacote Windows. Usa o PHP do sistema:
# o app nao existe sem PHP, entao nao ha dependencia nova a instalar.
#
#   ./start-mcp.sh                 # porta 7788
#   MCP_PORT=7799 ./start-mcp.sh   # outra porta
#
# Para agente na MESMA maquina, prefira stdio (sem porta, sem token):
#   php artisan mcp:serve
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

PORT="${MCP_PORT:-7788}"
HOST="${MCP_HOST:-0.0.0.0}"

if ! command -v php >/dev/null 2>&1; then
  echo "[ERRO] php nao encontrado no PATH. Instale o PHP 8.3+ (brew install php)." >&2
  exit 1
fi

if [ ! -f vendor/autoload.php ]; then
  echo "[ERRO] Dependencias ausentes. Rode: composer install" >&2
  exit 1
fi

# Gera/mostra o token e imprime URL + bloco do opencode.json.
# Falha aqui significa .env somente leitura (o mcp:token avisa).
if ! php artisan mcp:token; then
  echo "[ERRO] Nao foi possivel preparar o token. Se o .env estiver somente leitura:" >&2
  echo "       chmod u+w .env && php artisan mcp:token" >&2
  exit 1
fi

# Porta livre? (o server do PHP morre com bind duplicado sem mensagem clara)
if ! php -r 'exit(@stream_socket_server("tcp://" . $argv[1]) ? 0 : 1);' "${HOST}:${PORT}"; then
  echo "[AVISO] A porta ${PORT} ja esta em uso. Feche o outro processo ou use MCP_PORT=7799 ./start-mcp.sh" >&2
  exit 1
fi

echo
echo "  MCP ouvindo em ${HOST}:${PORT}. Ctrl+C encerra."
echo "  Se o firewall do macOS perguntar, permita conexoes de entrada."
echo

exec php -S "${HOST}:${PORT}" "$ROOT/mcp/index.php"
