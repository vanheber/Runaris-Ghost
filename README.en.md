# 🕯️ Runaris Ghost — v1.4-Alpha

[Português (BR)](README.md) | **English**

**Runaris Ghost** is a Digital Sanctuary for Authors. A local-first creative writing platform, focused on immersion, manuscript organization, and optionally, collaboration with Artificial Intelligence as a writing assistant.

> [!CAUTION]
> **Alpha Version — Assume the risk.** This is the first public release. Many bugs, edges, and unexpected behaviors may occur. Use with caution and keep backups of your projects.

> [!IMPORTANT]
> **AI is optional and supplementary.** Runaris Ghost **does not write your story for you.** If enabled, AI functions exclusively as:
> - **Writing Assistant** — generates paragraphs based on your planning
> - **Reviewer** — corrects Portuguese and removes mechanical AI slop
> - **Organizer** — suggests ideas, conflicts, and plot twists based on what you wrote
>
> Your creative brain is **always yours.**

> [!NOTE]
> **Open Source & Donation-Ware**: This software is free and open-source under the **GPL-3.0** license. If you appreciate the work and would like to support the continuation of the project, consider making a donation: [paypal.com/donate/?business=vanheber@gmail.com](https://www.paypal.com/donate/?business=vanheber@gmail.com).

---

## Philosophy

1. **Open Source (GPL-3.0)**: You are free to audit, modify, and redistribute. The software is yours just as much as it is ours.
2. **Data Sovereignty**: Your manuscript is stored in **pure Markdown (.md)** — a simple, human-readable, non-proprietary, and uncompiled format. No cloud service holds your texts. You can open, edit, and transport your files with any text editor.
3. **No cloud dependency**: Install on your local machine, on a VPS, or on a shared hosting — you choose. Zero telemetry, zero lock-in.

---

## 📦 Installation (End User)

Download the `runaris-ghost-dist.zip` package (root of the repository — already contains code + dependencies), extract, and follow the step-by-step in **[docs/INSTALACAO.html](docs/INSTALACAO.html)**. Single requirement: PHP 8.3+. In the extracted folder, there is also an `index.html` with the 3 quick steps (links for PHP installation + button for the installer).

---

## 🏛️ Guide for Developers

If you are a developer and want to run the project locally:

### Requirements
- PHP 8.3+
- Composer
- Node.js & NPM
- SQLite

### Manual Installation
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
php artisan serve
```

*Note: We do not offer support for environment or manual compilation issues. If you find a **real technical bug** in the code, open an Issue.*

---

## 🚀 Current Features

### 1. Workspace & Editorial
*   **Split Sidebars**: Resizable sidebars via `Split.js`.
*   **Markdown Editor**: Integrated with `EasyMDE`, with resilient auto-save.
*   **Plot Tree**: Hierarchical organization (Section > Chapter > Scene).

### 2. Artificial Intelligence (Magic Buttons)
*   **Ghost Writer**: Literary scene writing based on your Plot Bible, with confirmation to avoid content loss.
*   **Reviewer**: Portuguese correction and removal of AI Slop (clichés, em dashes, adverbs) with configurable checkboxes.
*   **Suggest Ideas**: Generation of conflicts and plot twists based on context.

### 3. Sanctuary & Security
*   **Local Onboarding**: Private setup, without mandatory cloud accounts.
*   **Sovereign Backup**: Readable export of all your progress in structured Markdown.
*   **Snapshots & Rollback**: Local restore points for the project (database and files), allowing to revert changes with a click.
*   **Factory Reset**: Safe and thorough cleaning of all local data.

---

## 🛠️ Technical Stack
*   **Core**: Laravel 12.
*   **Frontend**: Vanilla JS + Bootstrap 5.3 (Strict Design).
*   **Database**: SQLite.
*   **AI**: Google Gemini (selectable Flash/Pro models, via Local API Key).

---

## 🤝 Contributions
Interested in helping out? Check out our [CONTRIBUTING.md](CONTRIBUTING.md) file to learn how contributions are accepted.

---

&copy; 2024-2026 Runaris Ghost. Developed with soul by [vanheber](https://github.com/vanheber).

**Disclaimer**: This software is provided "as is" (AS IS), without warranties of any kind, express or implied. The developer does not offer direct technical support and is not responsible for data loss or malfunction resulting from the use or modification of the code.
