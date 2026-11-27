#!/usr/bin/env bash
#
# Runaris Ghost — launcher macOS / Linux
#
# Dois modos, mesma porta de entrada:
#   pacote   runtime/frankenphp embutido — nada a instalar (PHP já vem dentro)
#   fonte    usa o PHP do sistema (8.3+) e o `php artisan serve`
#
#   1) aplica atualização pendente   2) acha porta livre
#   3) serve public/                 4) abre o navegador
#
#   ./start.sh                 # uso normal
#   ./start.sh --rollback      # restaura a versão anterior e inicia
#   NO_BROWSER=1 ./start.sh    # sem abrir navegador (VPS/SSH)
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

die() {
  echo "[ERRO] $*" >&2
  exit 1
}

RUNTIME="$ROOT/runtime/frankenphp"

if [ -f "$RUNTIME" ]; then
  # zip baixado da internet chega com quarentena e o macOS mata o binário.
  [ -x "$RUNTIME" ] || chmod +x "$RUNTIME" 2>/dev/null || true
  if [ "$(uname -s)" = "Darwin" ] && command -v xattr >/dev/null 2>&1; then
    xattr -dr com.apple.quarantine "$ROOT/runtime" >/dev/null 2>&1 || true
  fi
  # cwd/PHPRC em runtime/ para as extensões e os limites do php.ini valerem.
  export PHPRC="$ROOT/runtime"
  php_run() { "$RUNTIME" php-cli "$@"; }
  # artisan pelo shim: o php-cli do FrankenPHP entrega o binário em $argv[0].
  php_artisan() { "$RUNTIME" php-cli "$ROOT/runtime/artisan.php" "$@"; }
  serve() { "$RUNTIME" php-server --root "$ROOT/public" --listen "127.0.0.1:$1"; }
  MODE="pacote"
else
  command -v php >/dev/null 2>&1 \
    || die "PHP não encontrado. Instale o PHP 8.3+ (docs/INSTALACAO.html) ou use o pacote com runtime embutido."
  php_run() { php "$@"; }
  php_artisan() { php artisan "$@"; }
  serve() { php artisan serve --host=127.0.0.1 --port="$1"; }
  MODE="código-fonte"
fi

php_run -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' \
  || die "PHP $(php_run -r 'echo PHP_VERSION;') encontrado — o Runaris Ghost exige 8.3+."

[ -f vendor/autoload.php ] \
  || die "Pacote incompleto (vendor/ ausente). Extraia o zip de novo ou rode: composer install"

# ---- Reverter (opcional, antes de subir) -----------------------------------
if [ "${1:-}" = "--rollback" ]; then
  php_artisan app:rollback || die "Não foi possível reverter. Nada foi iniciado."
fi

# ---- 1) Aplicar atualização pendente (servidor parado neste momento) ------
if [ -f storage/updates/pending.zip ]; then
  echo "Aplicando atualização..."
  php_artisan app:apply-update \
    || die "A atualização falhou e a versão anterior foi restaurada. .env, storage/ e os bancos estão intactos."
fi

# ---- 2) Porta livre, escolhida pelo próprio PHP ---------------------------
PORT=$(php_run -r '$s = @stream_socket_server("tcp://127.0.0.1:0", $e, $m); if (!$s) { echo 8000; exit; } $n = stream_socket_get_name($s, false); fclose($s); echo substr($n, strrpos($n, ":") + 1);')
[ -n "$PORT" ] || PORT=8000
APP_URL="http://127.0.0.1:${PORT}"

echo
echo "  Runaris Ghost (${MODE})  >>  ${APP_URL}"
echo "  Ctrl+C encerra o programa."
echo

# ---- 3) Servir + abrir o navegador ----------------------------------------
if [ -z "${NO_BROWSER:-}" ]; then
  (
    sleep 1
    if command -v open >/dev/null 2>&1; then
      open "$APP_URL"
    elif command -v xdg-open >/dev/null 2>&1; then
      xdg-open "$APP_URL" >/dev/null 2>&1 || true
    fi
  ) &
fi

serve "$PORT"
