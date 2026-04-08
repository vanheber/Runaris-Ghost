# 🕯️ Runaris Ghost - v1.1.0 

**Runaris Ghost** é uma plataforma de escrita criativa focada em imersão, organização e suporte de IA para escritores.  
A interface é projetada para ser livre de distrações, com foco total na construção de mundos (worldbuilding) e na trama principal. Agora com integração profunda com **Google Gemini AI**.

---

## 🏛️ Arquitetura e Padrões de Design

O projeto segue uma filosofia de **"Pure Bootstrap 5.3"**.  
Regras fundamentais para qualquer manutenção visual:

1.  **Strict Bootstrap**: Use exclusivamente classes utilitárias do Bootstrap 5.3 (`.d-flex`, `.bg-body-tertiary`, `.border-0`, etc.).
2.  **Zero Tailwind**: Não utilize Tailwind CSS ou classes personalizadas se o Bootstrap oferecer suporte nativo.
3.  **No Local Styles**: Evite blocos `<style>` dentro de arquivos Blade. Todas as customizações (como Split.js gutters e EasyMDE overrides) devem estar centralizadas em `resources/css/app.css`.
4.  **Tema Reativo**: O sistema suporta **Dark, Light e Solarized Light**. 
    *   As cores são controladas via variáveis de CSS (`--bs-body-bg`, etc.) no atributo `data-bs-theme` da tag `<html>`.
5.  **Z-Index**:
    *   Modais e Dropdowns: Padrão Bootstrap (> 1050).
    *   Botões de Toggle lateral: `z-index: 10`.

---

## 🚀 Funcionalidades Atuais

### 1. Workspace (Painel Principal)
*   **Split Sidebars**: Barras laterais redimensionáveis via `Split.js`.
*   **Modo Sem Distração**: Botões de recolhimento que escondem as barras laterais para foco total no editor.
*   **Editor Markdown**: Integrado com `EasyMDE`, com auto-salvamento a cada 1.5s de inatividade.

### 2. Manuscrito (Trama)
*   **Árvore Hierárquica**: Organização em Seções, Capítulos e Cenas.
*   **CRUD Completo**: 
    *   Criação de novos itens via botão global ou "+" individual na árvore.
    *   Exclusão segura via **Modal do Bootstrap** com confirmação de segurança.
    *   Edição de títulos "inline" clicando diretamente no nome do item.
*   **Drag & Drop**: Reordenamento da árvore via `SortableJS` (integrado com o banco de dados).

### 3. Worldbuilding (Cards)
*   Categorias: **Geografia/Cenários**, **Personagens** e **Objetos**.
*   Fichas separadas do manuscrito para consulta rápida durante a escrita.
*   **IA Suggest**: Geração de fichas baseadas no contexto da história.

### 4. Inteligência Artificial (Magic Buttons)
*   **Escritor Fantasma**: Redação literária de cenas baseada em planejamento e cards.
*   **Sugerir Ideias**: Geração de bullet points e conflitos para estruturação de cenas.
*   **Bíblia Automática**: Sincronização e resumo narrativo via IA para manter a coerência global.

---

## 🛠️ Stack Tecnológica
*   **Backend**: Laravel 11.
*   **Frontend**: JS Vanilla + Blade.
*   **CSS**: Bootstrap 5.3 + Custom App.css.
*   **Editor**: EasyMDE.
*   **Layout**: Split.js.

---

## 📌 Prompt de Retomada (Para Próxima Sessão)

> "Olá! Estamos trabalhando no projeto **Runaris Ghost (v1.1.0)**. 
> A interface está consolidada em **Pure Bootstrap 5.3** e o sistema de **IA via Google Gemini** está totalmente integrado.
>
> **Estado Atual:**
> 1.  **AI Power**: Botões "Escritor Fantasma" e "Sugerir Ideias" operacionais no editor. 
> 2.  **Export Engine**: Suporte a EPUB, PDF e HTML com metadados e folha de rosto.
> 3.  **Bíblia & Worldbuilding**: Grafo de conexões e sincronização de lore funcional.
>
> **Próximos Passos:**
> 1.  **Análise de Sentimentos**: IA para avaliar o tom da cena (Alegre, Sombrio, etc).
> 2.  **Timeline**: Visualização cronológica dos eventos da história.
> 3.  **UI Polish**: Refinar micro-animações de transição entre estados de edição."
