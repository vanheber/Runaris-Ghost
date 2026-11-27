#!/usr/bin/env bash
#
# Runaris Ghost — servidor MCP (macOS / Linux)
#
# Equivalente ao start-mcp.bat do pacote Windows. Dois modos:
#   pacote   runtime/frankenphp embutido (nada a instalar)
#   fonte    usa o PHP do sistema (8.3+) com o servidor embutido
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
RUNTIME="$ROOT/runtime/frankenphp"

# ponytail: a deteccao se repete aqui (e no start.sh) de proposito — cada
# launcher do pacote fica autossuficiente, sem include escondido.
if [ -f "$RUNTIME" ]; then
  [ -x "$RUNTIME" ] || chmod +x "$RUNTIME" 2>/dev/null || true
  if [ "$(uname -s)" = "Darwin" ] && command -v xattr >/dev/null 2>&1; then
    xattr -dr com.apple.quarantine "$ROOT/runtime" >/dev/null 2>&1 || true
  fi
  export PHPRC="$ROOT/runtime"
  php_run() { "$RUNTIME" php-cli "$@"; }
  php_artisan() { "$RUNTIME" php-cli "$ROOT/runtime/artisan.php" "$@"; }
  serve() { "$RUNTIME" php-server --root "$ROOT/mcp" --listen "${HOST}:${PORT}"; }
else
  if ! command -v php >/dev/null 2>&1; then
    echo "[ERRO] php nao encontrado no PATH. Instale o PHP 8.3+ (brew install php)." >&2
    exit 1
  fi
  php_run() { php "$@"; }
  php_artisan() { php artisan "$@"; }
  serve() { php -S "${HOST}:${PORT}" "$ROOT/mcp/index.php"; }
fi

if [ ! -f vendor/autoload.php ]; then
  echo "[ERRO] Dependencias ausentes. Rode: composer install" >&2
  exit 1
fi

# Gera/mostra o token e imprime URL + bloco do opencode.json.
# Falha aqui significa .env somente leitura (o mcp:token avisa).
if ! php_artisan mcp:token; then
  echo "[ERRO] Nao foi possivel preparar o token. Se o .env estiver somente leitura:" >&2
  echo "       chmod u+w .env && php artisan mcp:token" >&2
  exit 1
fi

# Porta livre? (o server do PHP morre com bind duplicado sem mensagem clara)
# endereço interpolado no código: o php-cli do FrankenPHP não põe os args em $argv[1]
if ! php_run -r "exit(@stream_socket_server('tcp://${HOST}:${PORT}') ? 0 : 1);"; then
  echo "[AVISO] A porta ${PORT} ja esta em uso. Feche o outro processo ou use MCP_PORT=7799 ./start-mcp.sh" >&2
  exit 1
fi

echo
echo "  MCP ouvindo em ${HOST}:${PORT}. Ctrl+C encerra."
echo "  Se o firewall do macOS perguntar, permita conexoes de entrada."
echo

serve
