# CONTEXT.md — Runaris Ghost

Shared domain vocabulary and stack conventions for agents working on this project.

## What this is

Runaris Ghost is a **Santuário Digital para Autores** — a local-first, open-source creative writing platform focused on immersion, manuscript organization, and AI co-writing (Google Gemini). Everything runs locally via SQLite. Zero telemetry. Donation-ware under GPL-3.0 license.

## Environments

Two instances run in parallel:

| Instance | Machine | Nature |
|---|---|---|
| DEV | this machine (git clone) | development; `storage/app/private/projects` is a symlink to the Runaris-Vault repo; test changes here first |
| User install | another machine | installed via the web installer as a normal user; used daily and updated through the normal user flow (Guardian Update System) |

A change is only done when it works on **both**: never assume a git clone, shell/artisan access, or the vault symlink exists on the user install — and keep the update path working for a non-technical user.

## Key terms

| Term | Meaning |
|---|---|
| Santuário Digital | The app itself; metaphor for "your data is sacred and private" |
| Manuscrito | Manuscript tree: Section > Chapter > Scene (hierarchical organization) |
| Bíblia do Mundo | World Bible: lore rules + narrative summary (cerebelo) |
| Cerebelo | AI-generated master narrative summary consolidating all chapter summaries |
| Ficha | Worldbuilding card: character, scenario, object, lore |
| Worldbuilding | Cards + graph of connections between characters/places/items |
| Escritor Fantasma | Ghost Writer — AI button that writes literary prose based on planning + bible |
| Revisor | Reviewer — AI button that corrects Portuguese grammar and removes AI slop |
| Sugerir Ideias | Suggest Ideas — AI button for plot points, conflicts, twists |
| Ghost Formatting | Visual overlay layer (CodeMirror widgets) — never touches .md source |
| Raio-X | Pure Markdown mode — shows raw text, no visual overlays |
| Snapshots | Local database + files backup points with rollback capability |
| The Author's Pulse | News feed from runaris.com.br — unidirectional GET, no user data sent |

## Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3+, Laravel 12 |
| Frontend | Vanilla JS + Bootstrap 5.3 (strict — Tailwind forbidden) |
| CSS | Bootstrap utilities first, `app.css` only for what Bootstrap doesn't solve |
| Icons | Bootstrap Icons (exclusive) |
| Editor | EasyMDE (CodeMirror) with custom Ghost Formatting overlays |
| Database | SQLite (hardcoded — `sqlite` and `sqlite_project` connections) |
| AI | Google Gemini API (key stored in `system_settings` table) |
| Image processing | Intervention/Image (GD driver) |
| PDF export | DomPDF |
| Markdown parsing | Parsedown, spatie/yaml-front-matter |
| JS libs (CDN) | EasyMDE, SortableJS, Split.js, ForceGraph |
| Build | Vite |

## Build / test / lint commands

```bash
composer install     # PHP dependencies
npm install          # JS dependencies
php artisan serve    # Dev server (port 8000)
npm run dev          # Vite dev server (HMR on port 5173)
php artisan migrate  # Run database migrations
```

```bash
php -l <file>        # PHP syntax check (no formal linter)
phpunit              # PHP tests (limited coverage)
```

## Key files and directories

| Path | Role |
|---|---|
| `app/Services/GeminiService.php` | All Google Gemini API communication |
| `app/Services/LiteraryCraft.php` | Anti-AI-slop literary rules injected as system instructions |
| `app/Http/Controllers/AiController.php` | AI magic buttons (suggestCard, generatePlanning, writeScene, reviewScene) |
| `app/Http/Controllers/ManuscriptController.php` | Manuscript CRUD + summary generation |
| `app/Http/Controllers/CardController.php` | Worldbuilding cards CRUD + connections |
| `app/Http/Controllers/BibleController.php` | World bible + cerebellum sync |
| `app/Http/Controllers/GalleryController.php` | Image gallery CRUD + processing |
| `app/Http/Controllers/SettingsController.php` | System settings + factory reset |
| `app/Http/Controllers/InstallController.php` | Web installer wizard |
| `app/Http/Controllers/SetupController.php` | Post-install setup wizard |
| `app/Http/Middleware/ProjectContextMiddleware.php` | Switches SQLite connection per project |
| `app/Services/ProjectManager.php` | Database switching + migration logic |
| `app/Services/ExporterService.php` | ePub, PDF, HTML, Markdown, Backup export |
| `app/Services/UserSetupService.php` | Shared admin user creation (Install + Setup) |
| `resources/views/projects/dashboard.blade.php` | Main SPA — manuscript editor, cards, gallery, bible, graph (~3500 lines) |
| `resources/views/layouts/app.blade.php` | Base layout with navbar + footer |
| `resources/css/app.css` | Centralized design system CSS (~1100 lines) |
| `routes/web.php` | All routes (web + project-scoped) |
| `design/` | UI mockups from design phase |
| `docs/` | EULA + User Manual |
| `PROTOCOL.md` | Development protocol (design rules, security policy, methodology) |
| `ROADMAP.md` | Product roadmap (Phase 1 done, Phase 2-3 planned) |

## Design rules (from PROTOCOL.md)

- **No `style` attribute** in HTML — use CSS classes only
- **No `!important`** in CSS — use specificity
- **Bootstrap First** — exhaust Bootstrap utilities before writing custom CSS
- **Bootstrap Icons** only — no FontAwesome, no emoji as icons
- **Tailwind CSS forbidden** — Bootstrap 5.3 classes only
- **Markdown is sacred** — never edit user .md files; all visual formatting via Ghost Formatting (CSS/JS overlays)
- **Zero telemetry** — no tracking, no user logs, no external calls beyond Gemini API + News Feed (unidirectional GET)
- **Code style**: Portuguese comments, KISS principle, surgical changes only

## Domain conventions

- **Portuguese (BR)** is the primary language for UI strings, comments, and commit messages
- **UUIDs** are used as primary identifiers throughout (not auto-increment IDs)
- **SQLite project isolation**: each project has its own SQLite database at `storage/app/private/projects/{uuid}/database.sqlite`. The `ProjectContextMiddleware` handles connection switching.
- **Images** are stored at `storage/app/private/projects/{uuid}/assets/` — full images (quality 92, max 1600x2560) + thumbnails (quality 75, 200x240 cover)
- **Manuscript content** is stored as `.md` files at `storage/app/private/projects/{uuid}/manuscript/{uuid}.md`
- **Error handling**: try/catch in controllers, return JSON `{ error: message }` with HTTP 500
- **CSRF**: All POST/PUT/DELETE requests include `X-CSRF-TOKEN` header from meta tag
