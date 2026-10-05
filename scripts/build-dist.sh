#!/bin/bash

# Runaris Ghost - Build Distribution Script
# Gera um pacote ZIP pronto para instalação (sem arquivos de desenvolvimento).

echo "🚀 Iniciando build de distribuição..."

# 1. Compilar Assets (Vite)
echo "📦 Compilando assets (Vite)..."
npm run build

BUILD_DIR="build_temp"
ZIP_NAME="runaris-ghost-dist.zip"

rm -rf $BUILD_DIR
rm -f $ZIP_NAME
mkdir $BUILD_DIR

echo "📂 Preparando arquivos..."

# 2. Copiar apenas o necessário para executar a aplicação
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
cp LICENSE $BUILD_DIR/
cp .env.example $BUILD_DIR/.env.example

# 3. Remover o que só serve ao desenvolvimento
rm -rf $BUILD_DIR/database/factories $BUILD_DIR/database/seeders
rm -rf $BUILD_DIR/resources/css $BUILD_DIR/resources/js
rm -rf $BUILD_DIR/public/storage   # symlink -> storage/app/public (carregaria dados locais)

# 4. Página de entrada com as instruções de instalação
cp docs/index.html $BUILD_DIR/index.html
cp -R docs/assets $BUILD_DIR/assets

# 5. Criar estrutura de storage necessária
mkdir -p $BUILD_DIR/storage/app/public
mkdir -p $BUILD_DIR/storage/framework/cache/data
mkdir -p $BUILD_DIR/storage/framework/sessions
mkdir -p $BUILD_DIR/storage/framework/testing
mkdir -p $BUILD_DIR/storage/framework/views
mkdir -p $BUILD_DIR/storage/logs

# 6. Limpar banco de dados local da dist (garantir que vá limpo)
rm -f $BUILD_DIR/database/*.sqlite $BUILD_DIR/database/*.sqlite-wal $BUILD_DIR/database/*.sqlite-shm $BUILD_DIR/database/*.sqlite-journal
touch $BUILD_DIR/database/.gitkeep

# 7. Instalar dependências de produção no diretório de build
echo "📥 Instalando dependências de produção (Composer)..."
cd $BUILD_DIR
composer install --no-dev --optimize-autoloader --no-interaction --quiet
cd ..

# 8. Limpar resíduos gerados durante o build
rm -f $BUILD_DIR/.env               # criado ao subir o app no composer install — o instalador gera um novo
rm -f $BUILD_DIR/composer.lock      # não é necessário em runtime
rm -f $BUILD_DIR/bootstrap/cache/config.php $BUILD_DIR/bootstrap/cache/routes.php $BUILD_DIR/bootstrap/cache/events.php

# 9. Gerar o ZIP (conteúdo na raiz, sem pasta interna)
echo "🗜️ Gerando pacote ZIP..."
cd $BUILD_DIR
zip -r ../$ZIP_NAME . -x "*.DS_Store*" > /dev/null
cd ..

# 10. Limpeza final
rm -rf $BUILD_DIR

echo "✅ Build concluído com sucesso!"
echo "📦 Arquivo gerado: $ZIP_NAME"
echo "💡 Para instalar: extraia o ZIP, leia o index.html e rode 'php artisan serve'."
