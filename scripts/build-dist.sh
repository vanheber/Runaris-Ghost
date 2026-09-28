#!/bin/bash

# Runaris Ghost - Build Distribution Script
# Este script gera um pacote ZIP pronto para produção/instalação.

echo "🚀 Iniciando build de distribuição..."

# 1. Compilar Assets (Vite)
echo "📦 Compilando assets (Vite)..."
npm run build

# 2. Criar diretório temporário de build
BUILD_DIR="build_temp"
ZIP_NAME="runaris-ghost-dist.zip"

rm -rf $BUILD_DIR
rm -f $ZIP_NAME
mkdir $BUILD_DIR

echo "📂 Preparando arquivos..."

# 3. Copiar arquivos essenciais
cp -R app $BUILD_DIR/
cp -R bootstrap $BUILD_DIR/
cp -R config $BUILD_DIR/
cp -R database $BUILD_DIR/
cp -R lang $BUILD_DIR/
cp -R public $BUILD_DIR/
cp -R resources $BUILD_DIR/
cp -R routes $BUILD_DIR/
cp -R docs $BUILD_DIR/
cp artisan $BUILD_DIR/
cp version $BUILD_DIR/version
cp composer.json $BUILD_DIR/
cp composer.lock $BUILD_DIR/
cp package.json $BUILD_DIR/
cp vite.config.js $BUILD_DIR/
cp README.md $BUILD_DIR/
cp PROTOCOL.md $BUILD_DIR/
cp LICENSE $BUILD_DIR/
cp .env.example $BUILD_DIR/.env.example

# 4. Criar estrutura de storage necessária
mkdir -p $BUILD_DIR/storage/app/public
mkdir -p $BUILD_DIR/storage/framework/cache/data
mkdir -p $BUILD_DIR/storage/framework/sessions
mkdir -p $BUILD_DIR/storage/framework/testing
mkdir -p $BUILD_DIR/storage/framework/views
mkdir -p $BUILD_DIR/storage/logs

# 5. Limpar banco de dados local da dist (garantir que vá limpo)
rm -f $BUILD_DIR/database/database.sqlite $BUILD_DIR/database/*.sqlite-wal $BUILD_DIR/database/*.sqlite-shm $BUILD_DIR/database/*.sqlite-journal
touch $BUILD_DIR/database/.gitkeep

# 6. Instalar dependências de produção no diretório de build
echo "📥 Instalando dependências de produção (Composer)..."
cd $BUILD_DIR
composer install --no-dev --optimize-autoloader --no-interaction --quiet
cd ..

# 7. Gerar o ZIP (conteúdo na raiz, sem pasta interna)
echo "🗜️ Gerando pacote ZIP..."
cd $BUILD_DIR
zip -r ../$ZIP_NAME . -x "*.DS_Store*" > /dev/null
cd ..

# 8. Limpeza final
rm -rf $BUILD_DIR

echo "✅ Build concluído com sucesso!"
echo "📦 Arquivo gerado: $ZIP_NAME"
echo "💡 Para instalar: extraia o ZIP, aponte o Herd/Servidor para a pasta 'public' e acesse via navegador para iniciar o Wizard."
