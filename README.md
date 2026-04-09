# 🕯️ Runaris Ghost - v1.2.0 

**Runaris Ghost** é uma plataforma de escrita criativa focada em imersão, organização e suporte de IA para escritores.  
A interface é projetada para ser livre de distrações, com foco total na construção de mundos (worldbuilding) e na trama principal. Agora com integração profunda com **Google Gemini AI** e pronto para distribuição comercial.

---

## 🏛️ Arquitetura e Padrões de Design

O projeto segue uma filosofia de **"Pure Bootstrap 5.3"**.  
Regras fundamentais para qualquer manutenção visual:

1.  **Strict Bootstrap**: Use exclusivamente classes utilitárias do Bootstrap 5.3 (`.d-flex`, `.bg-body-tertiary`, `.border-0`, etc.).
2.  **Zero Tailwind**: Não utilize Tailwind CSS ou classes personalizadas se o Bootstrap oferecer suporte nativo.
3.  **No Local Styles**: Evite blocos `<style>` dentro de arquivos Blade. Todas as customizações (como Split.js gutters e EasyMDE overrides) devem estar centralizadas em `resources/css/app.css`.
4.  **Tema Reativo**: O sistema suporta **Dark, Light e Solarized Light**. 
    *   As cores são controladas via variáveis de CSS (`--bs-body-bg`, etc.) no atributo `data-bs-theme` da tag `<html>`.
5.  **Aesthetica Premium**: Uso de Glassmorphism, gradientes suaves e tipografia moderna (Outfit/Inter).

---

## 🚀 Funcionalidades Atuais

### 1. Onboarding & Distribuição (v1.2.0)
*   **Fluxo de Configuração**: Configuração inicial elegante com seleção de idioma, validação de licença e setup de IA.
*   **Suporte a Idiomas**: Totalmente localizado em **Português (BR/PT)**, **Inglês** e **Espanhol**.
*   **Segurança Comercial**: Integração com a API do **Gumroad** para validação de chaves de licença.
*   **Administração do Santuário**: Painel para **Backup Total (ZIP)** e **Reset de Fábrica** protegido por senha.
*   **Auto-Update**: Infraestrutura pronta para atualizações automáticas via GitHub Releases.

### 2. Workspace & Editor
*   **Split Sidebars**: Barras laterais redimensionáveis via `Split.js`.
*   **Modo Sem Distração**: Foco total no editor com um clique.
*   **Editor Markdown**: Integrado com `EasyMDE`, com auto-salvamento a cada 1.5s de inatividade.

### 3. Manuscrito & Trama
*   **Árvore Hierárquica**: Organização em Seções, Capítulos e Cenas com Drag & Drop (`SortableJS`).
*   **CRUD Completo**: Gestão segura de conteúdo com confirmações visuais.
*   **Export Engine**: Geração de **EPUB, PDF, HTML** e pacotes **Web ZIP** estruturados.

### 4. Inteligência Artificial (Magic Buttons)
*   **Escritor Fantasma**: Redação literária de cenas baseada em planejamento e cards.
*   **Sugerir Ideias**: Geração de bullet points e conflitos para estruturação de cenas.
*   **Bíblia Automática**: Sincronização e resumo narrativo via IA para manter a coerência global.

---

## 🛠️ Stack Tecnológica
*   **Backend**: Laravel 11.
*   **Desktop App**: NativePHP + Electron.
*   **Frontend**: JS Vanilla + Blade + Bootstrap 5.3.
*   **Editor**: EasyMDE.
*   **Layout**: Split.js.

---

## 📌 Prompt de Retomada (Para Próxima Sessão)

> "Olá! Estamos trabalhando no projeto **Runaris Ghost (v1.2.0)**. 
> O sistema está pronto para distribuição comercial, com **Onboarding multilíngue** e validação de licença **Gumroad**.
>
> **Estado Atual:**
> 1.  **Commercial Ready**: Sistema de setup, proteção por senha e reset de fábrica operacionais.
> 2.  **i18n**: Interface disponível em PT-BR, PT-PT, EN e ES.
> 3.  **Desktop Ops**: Build de .dmg configurada com ícone premium e sistema de auto-update via GitHub.
>
> **Próximos Passos:**
> 1.  **Testes de Licenciamento**: Validar integração final com chaves reais do Gumroad.
> 2.  **Análise de Sentimentos**: IA para avaliar o tom da cena (Alegre, Sombrio, etc).
> 3.  **Timeline**: Visualização cronológica dos eventos da história."
