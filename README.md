# 🕯️ Runaris Ghost - v1.2.0

**Runaris Ghost** é um Santuário Digital para Autores. Uma plataforma de escrita criativa focada em imersão, organização e cooperação com Inteligência Artificial.

> [!TIP]
> **Convenience is the Product**: Este software é Open Source, mas a conveniência é paga. Se você quer usar o Runaris Ghost sem se preocupar com dependências, compilação de código, assinatura de binários ou suporte técnico, adquira a versão oficial no Gumroad.

[**🛒 Baixar Versão Oficial (Mac ARM64/Intel)**](https://gumroad.com)

---

## ⚖️ Filosofia e Modelo de Negócio

Este projeto opera sob o modelo **"Free Code, Paid Convenience"**:

1.  **Código Aberto (Open Source)**: O código fonte está disponível sob a licença [MIT](LICENSE). Você é livre para auditar, modificar e compilar sua própria build.
2.  **Binários Oficiais**: As builds oficiais são assinadas, notarizadas pela Apple e prontas para uso. É nelas que investimos nosso tempo de curadoria e suporte.
3.  **Suporte Pago**: Build manual? Você por sua conta. Suporte técnico e garantias de estabilidade são exclusivos para quem adquire a versão oficial.

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
php artisan native:serve
```

*Nota: Não oferecemos suporte para problemas de ambiente ou compilação manual. Se encontrar um **bug técnico real** no código, abra uma Issue.*

---

## 🚀 Funcionalidades Atuais

### 1. Workspace & Editorial
*   **Split Sidebars**: Barras laterais redimensionáveis via `Split.js`.
*   **Editor Markdown**: Integrado com `EasyMDE`, com auto-salvamento resiliente.
*   **Árvore de Trama**: Organização hierárquica (Seção > Capítulo > Cena).

### 2. Inteligência Artificial (Magic Buttons)
*   **Escritor Fantasma**: Redação literária de cenas baseada em sua Bíblia de Trama.
*   **Sugerir Ideias**: Geração de conflitos e reviravoltas baseadas no contexto.

### 3. Santuário & Segurança
*   **Onboarding Local**: Setup privado, sem contas em nuvem obrigatórias.
*   **Backup Total**: Exportação de todo o seu progresso em um único ZIP.
*   **Reset de Fábrica**: Limpeza profunda e segura de todos os dados locais.

---

## 🛠️ Stack Tecnológica
*   **Core**: Laravel 11 + NativePHP (Electron).
*   **Frontend**: Vanilla JS + Bootstrap 5.3 (Strict Design).
*   **Database**: SQLite.
*   **IA**: Google Gemini Cloud (via API Key local).

---

## 🤝 Contribuições
Interessado em ajudar? Veja nosso arquivo [CONTRIBUTING.md](CONTRIBUTING.md) para entender como as contribuições são aceitas.

---

&copy; {{ date('Y') }} Runaris. Desenvolvido com alma por [vanheber](https://github.com/vanheber).
