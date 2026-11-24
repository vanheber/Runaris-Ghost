#!/bin/bash
set -e

# Runaris Ghost - Build do pacote Windows (runtime FrankenPHP embutido)
# Gera runaris-ghost-win.zip: extrair e dar dois cliques em start.bat.
# Nao gera pacote universal. Outros SOs rodam a partir do codigo-fonte.

FRANKEN_VERSION="${FRANKEN_VERSION:-latest}"
if [ "$FRANKEN_VERSION" = "latest" ]; then
    FRANKEN_URL="https://github.com/php/frankenphp/releases/latest/download/frankenphp-windows-x86_64.zip"
else
    FRANKEN_URL="https://github.com/php/frankenphp/releases/download/${FRANKEN_VERSION}/frankenphp-windows-x86_64.zip"
fi

BUILD_DIR="build_temp"
ZIP_NAME="runaris-ghost-win.zip"

echo "🚀 Build Runaris Ghost (Windows) — FrankenPHP ${FRANKEN_VERSION}"

echo "📦 Compilando assets (Vite)..."
npm run build

rm -rf "$BUILD_DIR" "$ZIP_NAME"
mkdir -p "$BUILD_DIR"

echo "📂 Preparando arquivos..."
cp -R app bootstrap config database lang mcp public resources routes docs "$BUILD_DIR"/
cp artisan version composer.json LICENSE .env.example "$BUILD_DIR"/

# Remover o que só serve ao desenvolvimento
rm -rf "$BUILD_DIR"/database/factories "$BUILD_DIR"/database/seeders
rm -rf "$BUILD_DIR"/resources/css "$BUILD_DIR"/resources/js
rm -rf "$BUILD_DIR"/public/storage   # symlink -> storage/app/public (levaria dados locais)

# Estrutura de storage vazia + banco limpo
mkdir -p "$BUILD_DIR"/storage/app/public \
         "$BUILD_DIR"/storage/framework/cache/data \
         "$BUILD_DIR"/storage/framework/sessions \
         "$BUILD_DIR"/storage/framework/testing \
         "$BUILD_DIR"/storage/framework/views \
         "$BUILD_DIR"/storage/logs
rm -f "$BUILD_DIR"/database/*.sqlite "$BUILD_DIR"/database/*.sqlite-wal \
      "$BUILD_DIR"/database/*.sqlite-shm "$BUILD_DIR"/database/*.sqlite-journal
touch "$BUILD_DIR"/database/.gitkeep

echo "📥 Instalando dependências de produção (Composer)..."
( cd "$BUILD_DIR" && composer install --no-dev --optimize-autoloader --no-interaction --quiet )

rm -f "$BUILD_DIR"/.env
rm -f "$BUILD_DIR"/composer.lock
rm -f "$BUILD_DIR"/bootstrap/cache/config.php "$BUILD_DIR"/bootstrap/cache/routes.php "$BUILD_DIR"/bootstrap/cache/events.php

echo "⬇️  Baixando runtime FrankenPHP..."
mkdir -p "$BUILD_DIR"/runtime
curl -fL "$FRANKEN_URL" -o "$BUILD_DIR"/runtime/frankenphp-win.zip
( cd "$BUILD_DIR"/runtime && unzip -o -q frankenphp-win.zip && rm frankenphp-win.zip )

echo "🧩 Injetando launcher e php.ini..."
cp scripts/dist/win/start.bat scripts/dist/win/start-mcp.bat scripts/dist/win/rollback.bat "$BUILD_DIR"/
cp scripts/dist/win/php.ini "$BUILD_DIR"/runtime/php.ini

# Página de entrada com as instruções
cp docs/index.html "$BUILD_DIR"/index.html
cp -R docs/assets "$BUILD_DIR"/assets

echo "🗜️  Gerando pacote ZIP..."
( cd "$BUILD_DIR" && zip -r -q "../$ZIP_NAME" . -x "*.DS_Store*" )

rm -rf "$BUILD_DIR"

echo "✅ Build concluído: $ZIP_NAME"
echo "💡 Instalar: extraia o ZIP e dê dois cliques em start.bat."
