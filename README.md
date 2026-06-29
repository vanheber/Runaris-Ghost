# 🕯️ Runaris Ghost - v1.3.0

**Runaris Ghost** é um Santuário Digital para Autores. Uma plataforma de escrita criativa focada em imersão, organização e cooperação com Inteligência Artificial.

> [!NOTE]
> **Open Source & Donation-Ware**: Este software é gratuito e de código aberto. Se você aprecia o trabalho e deseja apoiar a continuidade do projeto, visite o site oficial: [runaris.com.br/ghost](https://runaris.com.br/ghost/).

---

Este projeto é regido pela comunidade e focado na liberdade criativa:

1.  **Código Aberto (Open Source)**: O código fonte está disponível sob a licença [MIT](LICENSE). Você é livre para auditar, modificar e redistribuir.
2.  **Soberania de Dados**: Tudo roda localmente. Suas histórias pertencem a você, não a um servidor em nuvem.
3.  **Comunidade**: O suporte é colaborativo. Bugs e melhorias são discutidos e resolvidos coletivamente.

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
*   **IA**: Google Gemini 2.5 Cloud (via API Key local).

---

## 🤝 Contribuições
Interessado em ajudar? Veja nosso arquivo [CONTRIBUTING.md](CONTRIBUTING.md) para entender como as contribuições são aceitas.

---

&copy; 2024-2026 [Runaris Ghost](https://runaris.com.br/ghost/). Desenvolvido com alma por [vanheber](https://github.com/vanheber).

**Isenção de Garantia**: Este software é fornecido "como está" (AS IS), sem garantias de qualquer tipo, expressas ou implícitas. O desenvolvedor não oferece suporte técnico direto e não se responsabiliza por perda de dados ou mau funcionamento decorrente do uso ou modificação do código.
