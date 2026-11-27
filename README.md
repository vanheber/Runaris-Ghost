# 🕯️ Runaris Ghost — v1.4-Alpha

**Português (BR)** | [English](README.en.md)

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

## 📦 Instalação (Usuário Final — Windows)

Baixe o pacote **`runaris-ghost-win.zip`** na página de [Releases](https://github.com/vanheber/Runaris-Ghost/releases), extraia e **dê dois cliques em `start.bat`**. O PHP já vem embutido — nada a instalar. Siga o passo a passo em **[docs/INSTALACAO.html](docs/INSTALACAO.html)**.

---

## 🍎🐧 macOS e Linux

**Pacote (recomendado)** — o PHP já vem embutido, nada a instalar:

| Sistema | Arquivo | Como abrir |
|---|---|---|
| macOS (Apple Silicon) | `runaris-ghost-mac-arm64.zip` | extraia e dê dois cliques em **`start.command`** |
| Linux x86_64 | `runaris-ghost-linux-x64.zip` | extraia e rode **`./start.sh`** |
| Linux arm64 | `runaris-ghost-linux-arm64.zip` | extraia e rode **`./start.sh`** |

O launcher aplica a atualização pendente, escolhe uma porta livre, abre o navegador e serve `public/`.
Na primeira abertura o macOS pode avisar que o arquivo é de desenvolvedor não identificado: clique com o **botão direito** em `start.command` → **Abrir** (é o seu programa local).

**Direto do código-fonte** — desenvolvimento, ou arquitetura fora do pacote (ex.: Mac Intel):

```bash
composer install
npm ci && npm run build   # só na primeira vez (gera public/build)
./start.sh                # Linux — ou ./start.command no macOS
```

- Reverter a última atualização: `./start.sh --rollback` (ou `php artisan app:rollback`).
- Sem interface gráfica (VPS/SSH): `NO_BROWSER=1 ./start.sh` — a URL aparece no terminal.
- O `npm run build` é obrigatório no código-fonte: sem `public/build` o app responde **500** (o Laravel procura o manifest do Vite).

> Mac Intel: apenas pelo código-fonte. O pacote embutido é Apple Silicon — o FrankenPHP parou de publicar binário Intel na v1.14 e o macOS 27 já não roda em Macs Intel.

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

## 🤖 Servidor MCP (agentes de IA)

O Runaris Ghost expõe um **servidor MCP** (Model Context Protocol) para que um agente de IA — Claude, Codex, opencode e afins — leia e escreva no santuário: manuscrito, fichas, Bíblia, IA literária, exports, snapshots e saúde da instalação.

- **Pacote (qualquer SO)**: **`start-mcp.bat`** no Windows, **`./start-mcp.sh`** no macOS/Linux. Ele gera o token, imprime a URL e abre o servidor na porta `7788`. O app continua em `127.0.0.1` — só o MCP fica exposto na rede.
- **Melhor, se o agente roda na mesma máquina**: `php artisan mcp:serve`, que fala stdio sem porta nem token.

Sem o token do `.env` (`MCP_TOKEN`) nenhuma ferramenta roda. Guia completo, lista de ferramentas e segurança: **[docs/MCP.md](docs/MCP.md)**.


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
