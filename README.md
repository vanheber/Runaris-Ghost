# 🕯️ Runaris Ghost — v1.0-Alpha

**Runaris Ghost** é um Santuário Digital para Autores. Uma plataforma de escrita criativa local-first, focada em imersão, organização dos seus manuscritos e, opcionalmente, cooperação com Inteligência Artificial como assistente de escrita.

> [!CAUTION]
> **Versão Alpha — Assuma o risco.** Este é o primeiro lançamento público. Muitos bugs, arestas e comportamentos inesperados podem acontecer. Use com cautela e mantenha backups dos seus projetos.

> [!IMPORTANT]
> **I.A. é opcional e coadjuvante.** O Runaris Ghost **não escreve a sua história por você.** Se ativada, a IA funciona exclusivamente como:
> - **Assistente de escrita** — gera parágrafos a partir do seu planejamento
> - **Revisor** — corrige português e remove clichês mecânicos (AI slop)
> - **Organizador** — sugere ideias, conflitos e reviravoltas com base no que você escreveu
>
> O cérebro criativo é **sempre o seu.**

> [!NOTE]
> **Open Source & Donation-Ware**: Este software é gratuito e de código aberto sob licença **GPL-3.0**. Se você aprecia o trabalho e deseja apoiar a continuidade do projeto, considere fazer uma doação: [paypal.com/donate/?business=vanheber@gmail.com](https://www.paypal.com/donate/?business=vanheber@gmail.com).

---

## Filosofia

1. **Código Aberto (GPL-3.0)**: Você é livre para auditar, modificar e redistribuir. O software é seu tanto quanto nosso.
2. **Soberania de Dados**: Seu manuscrito é armazenado em **Markdown puro (.md)** — formato simples, legível por humanos, não proprietário e não compilado. Nenhum serviço cloud detém seus textos. Você pode abrir, editar e transportar seus arquivos com qualquer editor de texto.
3. **Sem dependência de cloud**: Instale na sua máquina local, numa VPS, ou numa hospedagem compartilhada — você escolhe. Zero telemetria, zero lock-in.

---

## 📦 Instalação (Usuário Final)

Baixe o pacote `runaris-ghost-dist.zip` (raiz do repositório — já contém código + dependências), extraia e siga o passo a passo em **[docs/INSTALACAO.html](docs/INSTALACAO.html)**. Requisito único: PHP 8.3+.

---

## 🏛️ Guia para Desenvolvedores

Se você é um desenvolvedor e deseja rodar o projeto localmente:

### Requisitos
- PHP 8.3+
- Composer
- Node.js & NPM
- SQLite

### Instalação Manual
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
php artisan serve
```

*Nota: Não oferecemos suporte para problemas de ambiente ou compilação manual. Se encontrar um **bug técnico real** no código, abra uma Issue.*

---

## 🚀 Funcionalidades Atuais

### 1. Workspace & Editorial
*   **Split Sidebars**: Barras laterais redimensionáveis via `Split.js`.
*   **Editor Markdown**: Integrado com `EasyMDE`, com auto-salvamento resiliente.
*   **Árvore de Trama**: Organização hierárquica (Seção > Capítulo > Cena).

### 2. Inteligência Artificial (Magic Buttons)
*   **Escritor Fantasma**: Redação literária de cenas baseada em sua Bíblia de Trama, com confirmação para evitar perda de conteúdo.
*   **Revisor**: Correção de português e remoção de AI Slop (clichês, travessões, advérbios) com checkboxes configuráveis.
*   **Sugerir Ideias**: Geração de conflitos e reviravoltas baseadas no contexto.

### 3. Santuário & Segurança
*   **Onboarding Local**: Setup privado, sem contas em nuvem obrigatórias.
*   **Backup Soberano**: Exportação legível de todo o seu progresso em Markdown estruturado.
*   **Snapshots & Rollback**: Pontos de restauração locais para o projeto (banco de dados e arquivos), permitindo reverter alterações com um clique.
*   **Reset de Fábrica**: Limpeza profunda e segura de todos os dados locais.

---

## 🛠️ Stack Tecnológica
*   **Core**: Laravel 12.
*   **Frontend**: Vanilla JS + Bootstrap 5.3 (Strict Design).
*   **Database**: SQLite.
*   **IA**: Google Gemini (modelos Flash/Pro escolhíveis, via API Key local).

---

## 🤝 Contribuições
Interessado em ajudar? Veja nosso arquivo [CONTRIBUTING.md](CONTRIBUTING.md) para entender como as contribuições são aceitas.

---

&copy; 2024-2026 Runaris Ghost. Desenvolvido com alma por [vanheber](https://github.com/vanheber).

**Isenção de Garantia**: Este software é fornecido "como está" (AS IS), sem garantias de qualquer tipo, expressas ou implícitas. O desenvolvedor não oferece suporte técnico direto e não se responsabiliza por perda de dados ou mau funcionamento decorrente do uso ou modificação do código.
