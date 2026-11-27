# 🤖 MCP do Runaris Ghost

Servidor **MCP (Model Context Protocol)** para um agente de IA operar o santuário de fora da interface: ler o manuscrito, escrever cenas, mexer nas fichas, cuidar da Bíblia, disparar a IA literária, gerar exports/snapshots e avaliar a instalação.

> **Regra que não muda:** o markdown do autor é sagrado. O agente só grava em `.md` quando o autor pede — `manuscript_write`, `card_write` e `ai_write_scene` com `save=true` são as únicas portas de escrita, e as destrutivas exigem confirmação explícita.

---

## Como funciona

O MCP é **parte do app**, não um serviço paralelo:

| Camada | Arquivo |
|---|---|
| Front controller HTTP | `mcp/index.php` (único arquivo exposto) |
| Protocolo JSON-RPC / MCP | `app/Mcp/Server.php` |
| Catálogo de ferramentas | `app/Mcp/Tools.php` |
| Implementação | `app/Mcp/Handlers.php` |
| Token e URL | `app/Console/Commands/McpTokenCommand.php` (`php artisan mcp:token`) |
| Modo stdio (mesma máquina) | `php artisan mcp:serve` |
| Launcher Windows | `scripts/dist/win/start-mcp.bat` (pacote) |
| Launcher macOS/Linux | `start-mcp.sh` (pacote ou código-fonte) |

Consequências boas: zero dependência nova (nem Python, nem Node), e as ferramentas usam **os mesmos Services** da interface — mesmos arquivos `.md`, mesmo índice, mesmo SQLite por projeto. Não existe um "segundo banco" para divergir.

### Processo separado, porta separada

O launcher do app (`start.bat`, `start.command` ou `start.sh`) sobe a interface em `127.0.0.1`. O launcher do MCP (`start-mcp.bat` / `start-mcp.sh`) sobe outro processo, com raiz `mcp/`, em `0.0.0.0:7788`. Ou seja:

- a **UI não fica exposta** na rede;
- o MCP só responde `POST /mcp` com token;
- `GET /` mostra a ficha do servidor (útil para conferir no navegador).

---

## Instalação (pacote instalado)

1. Extraia o pacote do seu SO — `runaris-ghost-win.zip`, `runaris-ghost-mac-arm64.zip` ou `runaris-ghost-linux-x64.zip` / `-arm64` — e rode o launcher do app uma vez (`start.bat`, `start.command` ou `./start.sh`), para a instalação normal.
2. Rode o launcher do MCP do seu SO: **`start-mcp.bat`** no Windows, **`./start-mcp.sh`** no macOS/Linux.
3. A janela mostra:

```
  Runaris Ghost — servidor MCP
  URL:    http://192.168.1.50:7788/mcp
  Token:  rg_9f2c...

  Para o agente usar, no opencode.json:
  { "mcp": { "runaris-ghost": { ... } } }
```

4. Firewall: no Windows, permita em **"Redes privadas"** (não marque "Redes públicas"); no macOS o aviso é por aplicativo (Ajustes > Rede > Firewall); no Linux libere a porta na regra de LAN — ex.: `ufw allow from 192.168.0.0/16 to any port 7788`.
5. Cole o bloco impresso no seu `opencode.json` (seção `mcp`).

O token fica no `.env` (`MCP_TOKEN`) e **nunca** vai para o repositório. Ele **sobrevive a atualização**, porque o `.env` não entra no zip.

## Instalação (código-fonte, sem pacote)

Rodando do código, o app usa o PHP do sistema (8.3+). O MCP tem **dois caminhos**, e a escolha depende de onde o agente roda.

### A) Agente na mesma máquina — stdio (recomendado no dia a dia)

Sem porta, sem token, sem firewall: o agente sobe o processo e conversa por stdin/stdout.

```bash
php artisan mcp:serve
```

`opencode.json` (use o caminho **absoluto** do `artisan` — o cwd do agente pode ser outro):

```json
{
  "mcp": {
    "runaris-ghost": {
      "type": "local",
      "command": ["php", "/caminho/para/runaris-ghost/artisan", "mcp:serve"],
      "enabled": true
    }
  }
}
```

### B) Agente em outra máquina — HTTP na rede

```bash
./start-mcp.sh                  # porta 7788
MCP_PORT=7799 ./start-mcp.sh    # outra porta
```

O script confere PHP, `vendor/`, gera o token e sobe o servidor. Com o runtime do pacote presente ele usa o FrankenPHP; no código-fonte, o **servidor embutido do PHP** (`php -S`), que é single-thread: chamadas simultâneas entram em fila (para uso pessoal, resolve).

Alternativa sem o launcher:

```bash
php artisan mcp:token
php -S 0.0.0.0:7788 mcp/index.php
```

Libere a porta no firewall do macOS (Ajustes > Rede > Firewall) ou, no Linux, na sua regra de LAN.

---

## Segurança

| Risco | Mitigação |
|---|---|
| Alguém na rede chamar ferramentas | `Authorization: Bearer <MCP_TOKEN>`, comparado com `hash_equals` |
| Token vazando em log/histórico | token só em header, nunca em query string; `system_status` informa apenas `token_set` |
| Expor a UI | a UI continua em `127.0.0.1`; o processo do MCP serve somente `mcp/` |
| Expor na internet | default é LAN + firewall da máquina; **não** faça port-forward desta porta |
| Desligar tudo | `MCP_ENABLED=false` no `.env` (ou simplesmente não abrir o launcher do MCP) |
| Modo stdio (`mcp:serve`) | não passa por rede nem token: quem executa é o próprio agente, com as permissões do usuário. Não use em máquina compartilhada. |
| Confundir o agente | ferramentas destrutivas exigem `confirm` igual ao nome/uuid |

Para rotacionar o token: `php artisan mcp:token --reset` (invalida o anterior; atualize o cliente).

Variáveis no `.env`:

```env
MCP_ENABLED=true
MCP_TOKEN=rg_...
MCP_PORT=7788
```

---

## Ferramentas (41)

**Projetos** — `projects_list`, `project_get`, `project_create`, `project_update`, `project_delete`

**Manuscrito** — `manuscript_tree`, `manuscript_read`, `manuscript_create`, `manuscript_write`, `manuscript_write_planning`, `manuscript_rename`, `manuscript_sort`, `manuscript_delete`, `manuscript_summary`

**Worldbuilding** — `cards_list`, `card_read`, `card_create`, `card_write`, `card_update`, `card_delete`, `cards_search`, `graph_get`, `card_connections_get`, `card_connections_set`, `card_suggest`

**Bíblia** — `bible_read`, `bible_write`, `bible_sync` (`cerebellum` | `manuscript`)

**IA literária** — `ai_write_scene`, `ai_review`, `ai_suggest_card`, `ai_planning`

**Export e snapshots** — `export_generate`, `exports_list`, `snapshots_list`, `snapshot_create`, `snapshot_restore`

**Operação** — `system_status`, `update_check`, `update_apply`, `logs_tail`

### Fluxo típico

```text
projects_list                         -> pega o uuid do projeto
manuscript_tree {project}             -> acha a cena
manuscript_read {project, item}       -> lê texto + planejamento
ai_write_scene {project, title, planning, item, save: false}   -> devolve prosa
ai_review {project, content}          -> devolve texto revisado
manuscript_write {project, item, content, mode: append}        -> só se o autor mandar
```

---

## Atualização

O MCP é atualizado junto com o app:

- **Pacote (qualquer SO)**: o Guardião baixa o zip do SO atual; o launcher aplica no próximo boot — `start.bat` no Windows, `start.sh`/`start.command` no macOS/Linux (via `php artisan app:apply-update`). `.env`, `storage/` e `database/` ficam intactos — logo, o **token continua o mesmo**.
- **Git**: `git pull` normal (os launchers já são versionados no repo).

Depois de atualizar, feche e reabra a janela do MCP.

---

## Depuração

| Sintoma | Causa provável |
|---|---|
| `503 MCP_TOKEN não configurado` | rode `php artisan mcp:token` |
| `401 Token inválido` | token do cliente difere do `.env`; use `--reset` e atualize o cliente |
| Nada responde na rede | firewall da máquina (Windows: "Redes privadas"; Linux: `ufw`/`iptables`) |
| `README` da porta | outra instância na 7788 → `MCP_PORT=7789` e reabra |
| Ferramenta devolve `isError` | mensagem no próprio retorno; `logs_tail` mostra o stack se for bug |

Logs: `storage/logs/laravel.log` (token inválido e falhas de ferramenta entram como `MCP: ...`).

## Limites conhecidos

- Sem streaming (SSE): chamadas de IA literária (`ai_write_scene`) seguram a resposta até o Gemini terminar.
- HTTP: no pacote, o MCP roda no FrankenPHP; no código-fonte, no servidor embutido do PHP (`php -S`, single-thread). Para uso pessoal na LAN, ambos bastam; nenhum dos dois é para expor na internet.
- `export_generate` devolve **caminhos no disco do servidor** (ex.: `storage/app/private/projects/<uuid>/exports/…`), não o binário. Baixe por SMB/rede.
- O MCP não faz o auto-versionamento Git que o `ProjectContextMiddleware` faz na UI — quem versiona é o fluxo do app (Configurações > Git).
- Um projeto por chamada: o agente precisa entrar no projeto pelo uuid antes de tocar em itens.
