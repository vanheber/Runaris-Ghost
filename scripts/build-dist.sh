#!/bin/bash
set -euo pipefail

# Runaris Ghost — build dos pacotes por SO (runtime FrankenPHP embutido)
#
#   ./scripts/build-dist.sh               # Windows (padrão)
#   ./scripts/build-dist.sh mac           # macOS Apple Silicon
#   ./scripts/build-dist.sh linux-x64     # Linux x86_64
#   ./scripts/build-dist.sh linux-arm64   # Linux aarch64/arm64
#   ./scripts/build-dist.sh all
#
# Cada pacote sai completo — runtime do SO, launcher, assets compilados e vendor:
# o autor extrai e roda (start.bat / start.command / start.sh), sem instalar PHP.
# storage/, .env e o banco nunca entram no zip; é isso que faz a atualização
# (Guardião → pending.zip) preservar os dados do autor.

# Pinado: build reprodutível. v1.13.0 = PHP 8.5.11 (testado com o app; a v1.14
# traz PHP 8.6 e emite deprecation do Container do Laravel no console).
FRANKEN_VERSION="${FRANKEN_VERSION:-1.13.0}"
TAG="v${FRANKEN_VERSION#v}"   # releases são tagueadas com "v"; aceita 1.13.0 ou v1.13.0
BASE_URL="https://github.com/php/frankenphp/releases/download/${TAG}"

PAYLOAD="build_payload"
WORK="build_temp"
CACHE="build_cache"

TARGETS=("$@")
if [ ${#TARGETS[@]} -eq 0 ]; then
    TARGETS=(win)
fi
if [ "${TARGETS[0]}" = "all" ]; then
    TARGETS=(win mac linux-x64 linux-arm64)
fi

asset_for() {
    case "$1" in
        win)         echo "frankenphp-windows-x86_64.zip" ;;
        mac)         echo "frankenphp-mac-arm64" ;;
        linux-x64)   echo "frankenphp-linux-x86_64" ;;
        linux-arm64) echo "frankenphp-linux-aarch64" ;;
        *)           return 1 ;;
    esac
}

zip_for() {
    case "$1" in
        win)         echo "runaris-ghost-win.zip" ;;
        mac)         echo "runaris-ghost-mac-arm64.zip" ;;
        linux-x64)   echo "runaris-ghost-linux-x64.zip" ;;
        linux-arm64) echo "runaris-ghost-linux-arm64.zip" ;;
    esac
}

# O binário é grande (~170-200 MB); o cache evita baixar de novo a cada build.
fetch_runtime() {
    local asset="$1" dest="$CACHE/$1"
    mkdir -p "$CACHE"
    if [ ! -s "$dest" ]; then
        echo "⬇️  Baixando $asset (FrankenPHP ${FRANKEN_VERSION})..." >&2
        curl -fL --retry 2 "$BASE_URL/$asset" -o "$dest"
    fi
    echo "$dest"
}

build_payload() {
    echo "📦 Compilando assets (Vite)..."
    npm run build

    rm -rf "$PAYLOAD"
    mkdir -p "$PAYLOAD"
    cp -R app bootstrap config database lang mcp public resources routes docs "$PAYLOAD"/
    cp artisan version composer.json composer.lock LICENSE .env.example "$PAYLOAD"/

    # Remover o que só serve ao desenvolvimento
    rm -rf "$PAYLOAD"/database/factories "$PAYLOAD"/database/seeders
    rm -rf "$PAYLOAD"/resources/css "$PAYLOAD"/resources/js
    rm -rf "$PAYLOAD"/public/storage   # symlink -> storage/app/public (levaria dados locais)

    # Estrutura de storage vazia + banco limpo
    mkdir -p "$PAYLOAD"/storage/app/public \
             "$PAYLOAD"/storage/framework/cache/data \
             "$PAYLOAD"/storage/framework/sessions \
             "$PAYLOAD"/storage/framework/testing \
             "$PAYLOAD"/storage/framework/views \
             "$PAYLOAD"/storage/logs
    rm -f "$PAYLOAD"/database/*.sqlite "$PAYLOAD"/database/*.sqlite-wal \
          "$PAYLOAD"/database/*.sqlite-shm "$PAYLOAD"/database/*.sqlite-journal
    touch "$PAYLOAD"/database/.gitkeep

    echo "📥 Instalando dependências de produção (Composer)..."
    ( cd "$PAYLOAD" && composer install --no-dev --optimize-autoloader --no-interaction --quiet )

    rm -f "$PAYLOAD"/.env
    rm -f "$PAYLOAD"/composer.lock
    rm -f "$PAYLOAD"/bootstrap/cache/config.php "$PAYLOAD"/bootstrap/cache/routes.php "$PAYLOAD"/bootstrap/cache/events.php
}

build_target() {
    local target="$1" asset zip_name runtime
    asset="$(asset_for "$target")" || { echo "❌ Alvo desconhecido: $target" >&2; exit 1; }
    zip_name="$(zip_for "$target")"

    rm -rf "$WORK" "$zip_name"
    cp -R "$PAYLOAD" "$WORK"
    mkdir -p "$WORK/runtime"

    runtime="$(fetch_runtime "$asset")"

    if [ "$target" = "win" ]; then
        cp "$runtime" "$WORK/runtime/frankenphp-win.zip"
        ( cd "$WORK/runtime" && unzip -o -q frankenphp-win.zip && rm frankenphp-win.zip )
        cp scripts/dist/win/php.ini "$WORK/runtime/php.ini"
        cp scripts/dist/win/start.bat scripts/dist/win/start-mcp.bat scripts/dist/win/rollback.bat "$WORK"/
    else
        # Build estática do FrankenPHP: binário único, extensões compiladas.
        cp "$runtime" "$WORK/runtime/frankenphp"
        cp scripts/dist/posix/php.ini "$WORK/runtime/php.ini"
        cp scripts/dist/posix/artisan.php "$WORK/runtime/artisan.php"
        cp start.sh start.command start-mcp.sh "$WORK"/
        chmod 755 "$WORK/runtime/frankenphp" "$WORK/start.sh" "$WORK/start.command" "$WORK/start-mcp.sh"
        chmod 644 "$WORK/runtime/php.ini" "$WORK/runtime/artisan.php"
    fi

    # Página de entrada com as instruções e os links de download de todos os SOs
    cp docs/index.html "$WORK"/index.html
    cp -R docs/assets "$WORK"/assets

    echo "🗜️  Gerando $zip_name..."
    ( cd "$WORK" && zip -r -q -y "../$zip_name" . -x "*.DS_Store*" )

    rm -rf "$WORK"
    echo "✅ $zip_name — $(du -h "$zip_name" | cut -f1)"
}

echo "🚀 Runaris Ghost — alvo(s): ${TARGETS[*]} — FrankenPHP ${FRANKEN_VERSION}"

build_payload
for target in "${TARGETS[@]}"; do
    build_target "$target"
done
rm -rf "$PAYLOAD"

echo
echo "💡 Instalar: extraia o zip e rode o launcher do seu SO"
echo "   Windows: start.bat · macOS: start.command · Linux: start.sh"
