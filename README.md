# 🕯️ Runaris Ghost - Project Documentation

**Runaris Ghost** é uma plataforma de escrita criativa focada em imersão, organização e suporte de IA para escritores.  
A interface é projetada para ser livre de distrações, com foco total na construção de mundos (worldbuilding) e na trama principal.

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

---

## 🛠️ Stack Tecnológica
*   **Backend**: Laravel 11.
*   **Frontend**: JS Vanilla + Blade.
*   **CSS**: Bootstrap 5.3 + Custom App.css.
*   **Editor**: EasyMDE.
*   **Layout**: Split.js.

---

## 📌 Prompt de Retomada (Para Próxima Sessão)

> "Olá! Estamos trabalhando no projeto **Runaris Ghost**. 
> Acabamos de finalizar a estabilização da UI/UX usando **Pure Bootstrap 5.3**. O Manuscrito e o Worldbuilding estão operacionais com temas (Dark/Light/Solarized Light) estáveis.
>
> **Próximos Passos Sugeridos:**
> 1.  **AI Integration**: Iniciar a injeção do contexto de Worldbuilding (Cards) no pipeline do Gemini para suporte à escrita.
> 2.  **Export Engine**: Configurar o Pandoc para exportação em EPUB/PDF.
> 3.  **Card Details**: Refinar os templates de fichas de personagens e cenários.
>
> Por favor, leia o `README.md` e o `resources/css/app.css` para entender o sistema de design antes de começar."
