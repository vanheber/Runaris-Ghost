# PROTOCOL.md

## ❖ Runaris Ghost Development Protocol ❖

Este documento define o conjunto de diretrizes operacionais e metodológicas para o desenvolvimento e manutenção do Runaris Ghost. Nosso foco primordial é a integridade do Santuário Digital, garantindo máxima performance, segurança e aderência estrita aos princípios de código limpo e eficiência.

---

### 🌐 Diretrizes de Arquitetura e Tecnologia

| Componente | Especificação | Notas de Implementação |
| :--- | :--- | :--- |
| **Backend** | Laravel 12 | Manter aderência aos padrões de Service Layer e Repository Pattern. |
| **Frontend** | Bootstrap 5.3 (Vanilla) | Uso exclusivo de classes nativas do Bootstrap 5.3. **Tailwind CSS é proibido.** |
| **Lógica Cliente** | Vanilla JavaScript | Evitar frameworks complexos desnecessários no frontend. A lógica deve ser o mais direta possível. |
| **Banco de Dados** | SQLite | Padrão absoluto. O instalador é *hardcoded* para SQLite para garantir instalação zero-config e portabilidade total entre servidores. |
| **Modelo de Negócio** | Free Code, Paid Convenience | A base de código (o *free*) deve ser robusta, limpa e livre de funcionalidades de rastreamento. A receita deve vir da conveniência paga. |

#### 📐 Regras de Ouro do Frontend (Design System)
*   **Markdown é Sagrado:** Não editar arquivos .md do conteúdo (manuscrito, planejamento, lore), exceto por ordem expressa do usuário. Toda formatação especial (Ghost Formatting) deve ser feita via camada visual (Overlay/Widget) sem alterar a fonte.
*   **Ghost Formatting (Imagens):** Imagens em Modo Literário são renderizadas como thumbnails interativos (widgets atômicos).
    *   **Troca Rápida:** Clicar na imagem no editor abre o Modal de Seleção para substituição imediata.
    *   **Deleção Natural:** Use Backspace/Delete para remover imagens como se fossem caracteres.
    *   **Raio-X:** O modo Raio-X (Markdown Puro) deve sempre manter as ferramentas de edição (B, I, etc.) ativas.
*   **Sem Styles Inline:** Proibido o uso do atributo `style` em tags HTML.
*   **Sem `!important`:** Proibido o uso de `!important` no CSS. Use especificidade.
*   **Bootstrap First:** Usar estritamente as classes utilitárias nativas do Bootstrap 5.3.
*   **app.css:** Criar classes customizadas apenas para o que o Bootstrap 5.3 não resolve.
*   **Ícones:** Uso exclusivo da biblioteca **Bootstrap Icons**.

### ⚙️ Fluxo de Trabalho de Desenvolvimento (SKILL Protocol)

Todo ciclo de desenvolvimento deve seguir um fluxo rigoroso para garantir a qualidade e a contenção de escopo.

#### 1. Pré-Execução e Planejamento
*   **Pensar Antes de Codificar:** Não iniciar a implementação sem um planejamento claro do estado atual, do objetivo e do resultado esperado.
*   **Execução Orientada a Objetivos:** Definir o *payload* de trabalho em etapas curtas e atingíveis. Cada *commit* deve representar a conclusão de um objetivo específico.
*   **Planejamento:** Criar um plano sucinto (bullet points) que detalhe a função, o arquivo e as classes envolvidas.

#### 2. Implementação (Princípios de Código)
*   **Simplicidade Primeiro (KISS):** Utilizar a menor quantidade de código necessária para atingir a funcionalidade. A complexidade é um *bug potencial*.
*   **Alterações Cirúrgicas:** Modificar **apenas** o código estritamente necessário para cumprir o requisito. Não refatorar blocos de código que não foram solicitados ou que já funcionam corretamente.
*   **Manutenibilidade:** Priorizar a clareza e a legibilidade sobre o *código "genial"*.

### 🛡️ Integridade do Santuário Digital (Security & Privacidade)

Este é o princípio operacional mais importante. O código deve ser auditável, transparente e blindado contra qualquer forma de invasão de privacidade.

1.  **Política de Zero Telemetria:** É terminantemente proibido incluir quaisquer vetores de rastreamento, logs de usuário não essenciais, ou chamadas de API externas que não sejam estritamente necessárias para a funcionalidade paga.
2.  **Integridade de Conteúdo**: É estritamente proibido editar o conteúdo dos arquivos `.md` do manuscrito/lore através de automação ou scripts, exceto por ordem expressa do usuário. Toda formatação visual deve ser feita via CSS/JS no editor (Ghost Formatting).
3.  **Dados:** Tratar todos os dados do usuário como **confidenciais**. O armazenamento e processamento devem respeitar a arquitetura SQLite local ou o padrão de anonimização máxima.
4.  **Resposta de Bugs/Debug:** Em caso de falhas de sintaxe ou problemas de formatação simples (e.g., *boilerplate*, ajustes Bootstrap), delegar a correção inicial e o resumo do erro ao **Gemma Local** (ou LLM de borda). Isso economiza recursos de token de comunicação e mantém o foco na lógica de negócio.

### 📐 Orquestração Metodológica MCP

Para maximizar a eficiência e minimizar o custo de processamento (tokens), a orquestração das tarefas deve seguir o modelo MCP:

*   **M**anutenção e **C**onfiguração (Laravel/Bootstrap boilerplate): Utilizar ferramentas de scaffold e gerar *boilerplate* em massa.
*   **P**rocessamento de Tarefas (Lógica): Aplicação do SKILL Protocol.
*   **Delegação de Tarefas Simples:** Funções que não exigem raciocínio de negócio complexo, como formatação de código, correção de sintaxe simples em HTML/JS, ou resumir *stacks* de logs, devem ser delegadas à inteligência local (Gemma Local).

> **Lembrete:** A arquitetura deve ser limpa, o código deve ser cirúrgico, e o usuário deve permanecer no controle total de seus dados.
