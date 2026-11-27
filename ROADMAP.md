# 🗺️ Runaris Ghost Roadmap

Este documento delineia a visão de futuro do Runaris Ghost, equilibrando a filosofia Open Source com a sustentabilidade do projeto através de merchandising e apoio da comunidade.

---

## 🔵 Fase 1: Ecossistema e Engajamento (Concluído/Execução)
O foco foi transformar o Runaris Ghost em um canal de comunicação direta entre o autor principal e a comunidade de escritores.

### ✅ The Author's Pulse (News Feed)
- **Status**: Implementado.
- **Visual**: Sidebar Bento Style integrada ao dashboard de projetos.
- **Tecnologia**: Consumo assíncrono de JSON centralizado com cache de segurança.
- **Curadoria**: Divulgação de obras, vídeos no YouTube e tutoriais práticos.
- **Ecossistema de Gestão**: Repositório dedicado (`RunarisGhostNews`) com scripts de validação e deploy FTP automatizado para centralização total.

---

## 🟡 Fase 2: Ghost PRO & Internacionalização
Introdução de um modelo de "Apoio com Benefícios" para usuários que desejam personalizar sua experiência ao máximo e expansão global.

### 🌎 Multilanguage (i18n)
- **Suporte Nativo**: Implementação de suporte completo para **Inglês** e **Espanhol**, permitindo que o Ghost atenda 90% da base global de escritores.
- **Localização**: Tradução de todos os termos técnicos, manuais e placeholders do sistema.

### 🎨 Ghost PRO (Personalização)
- **Whitelabel**: Opção oficial para remover a seção de News e marcas do desenvolvedor mediante ativação premium/doação recorrente.
- **Custom Dashboard**: Ferramentas para reordenar blocos e widgets na Home.
- **Temas Exclusivos**: Paletas de cores e tipografias premium para apoiadores.

### 🛠️ Funcionalidades Avançadas
- **Multi-Cloud Sync**: Integração (opcional e via API Key do usuário) com provedores de nuvem para backup automático.
- **Plugins de Terceiros**: Arquitetura para permitir que a comunidade crie extensões para o editor.

### 🖥️ Distribuição Multiplataforma
- **Windows (pacote)**: `runaris-ghost-win.zip` com runtime FrankenPHP embutido — extrair, dois cliques em `start.bat`, nada a instalar. **Status**: implementado e revalidado no pacote final (PHP 8.5.11 sem warnings, instalador, update e rollback via `artisan`).
- **Launcher macOS/Linux**: `start.command` e `start.sh` — dois modos no mesmo arquivo: pacote (`runtime/frankenphp` embutido) ou código-fonte (PHP do sistema). **Status**: implementado.
- **Atualização fora do `.bat`**: `php artisan app:apply-update` e `php artisan app:rollback`, chamados pelo launcher, com o mesmo layout de `storage/updates` do Windows. **Status**: implementado e testado nos três SOs.
  - O runtime em execução não pode ser sobrescrito (Linux: `Text file busy`; Windows: DLL carregada travada), então o pacote extrai para `storage/updates/staging` e instala por `rename()`, com `copy()` só como fallback.
  - Nem `ZipArchive` nem `copy()` preservam o bit de execução: `ensureExecutables()` reaplica 0755 nos launchers e no runtime a cada update/rollback.
  - O snapshot exclui `runtime/` de propósito — é binário fixo; copiar ~170 MB por update não se paga.
- **macOS (pacote)**: `runaris-ghost-mac-arm64.zip` (~89 MB) — extrair e dar dois cliques em `start.command`, sem instalar PHP. **Status**: implementado e testado em macOS arm64 (instalador, update 1.4→1.5, rollback e MCP). Mac Intel fora do escopo.
  - **Binário**: FrankenPHP `darwin-arm64` pinado na v1.13.0 (PHP 8.5.11) em `runtime/`, com `php.ini` próprio e o shim `runtime/artisan.php`.
  - **Gatekeeper**: o launcher remove a quarentena de `runtime/` na primeira execução; o primeiro duplo clique em `start.command` ainda pede botão direito → Abrir. Notarização Apple segue como melhoria (nice-to-have), não como bloqueio.
  - **MCP**: `start-mcp.sh` sobe o mesmo runtime embutido; token no `.env`, sobrevive à atualização.
- **Linux (pacote)**: `runaris-ghost-linux-x64.zip` e `runaris-ghost-linux-arm64.zip` (~75 MB) — binário estático em `runtime/`, launcher `start.sh`. **Status**: implementado e testado em Ubuntu 26.04 x86_64 (instalador com as 9 extensões exigidas, update com o runtime *dentro* do pacote, rollback e MCP). **arm64 sem máquina para o smoke test** — build conferido, execução não.
- **Build reprodutível**: `./scripts/build-dist.sh [win|mac|linux-x64|linux-arm64|all]` com `FRANKEN_VERSION` pinado (padrão 1.13.0) e cache de download em `build_cache/`.

---

## 🔴 Fase 3: Expansão de Fronteiras (Visão de Longo Prazo)
- **Modo Colaborativo**: Sistema P2P ou via servidor privado para co-escrita em tempo real.
- **App Mobile Companion**: Versão ultra-leve para anotações rápidas sincronizadas com o projeto principal.

---
> **Nota**: Este roadmap é orgânico e pode ser alterado com base no feedback da comunidade e nas necessidades do Santuário Digital. Acompanhe as novidades em [runaris.com.br/ghost](https://runaris.com.br/ghost/).
