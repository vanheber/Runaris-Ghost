# Runaris Ghost — Santuário Digital para Autores

Regras combinadas: Ponytail (YAGNI ladder) + Runaris Ghost PROTOCOL.md.

---

## LADDER (Ponytail): Antes de escrever código, pare no primeiro degrau que segurar

1. **Isso precisa existir?** (YAGNI) → não: pule.
2. **Já existe neste codebase?** → reuse. Não reescreva helpers, utils ou padrões que já estão aqui.
3. **Stdlib / linguagem já faz?** → use.
4. **Plataforma nativa cobre?** → use.
5. **Dependência já instalada resolve?** → use.
6. **Cabe em uma linha?** → uma linha.
7. **Só então:** o mínimo que funciona.

A escada roda **depois** de entender o problema: leia a tarefa e o código que ela toca, trace o fluxo real, então suba a escada.

Bug fix = causa raiz, não sintoma. Grep todo chamador da função tocada e corrija UMA vez na função compartilhada. Remendar só o caminho que o ticket nomeia deixa os outros chamadores quebrados.

---

## REGRAS DE CÓDIGO (Ponytail + PROTOCOL.md)

- **KISS**: menor quantidade de código necessária. Complexidade é bug potencial.
- **Alterações cirúrgicas**: modificar APENAS o código estritamente necessário. Não refatorar o que não foi pedido ou já funciona.
- **Manutenibilidade** sobre "genialidade".
- Zero abstrações não solicitadas. Nenhuma dependência nova se puder evitar. Nenhum boilerplate.
- **Deleção sobre adição**. Tedioso sobre esperto. Menos arquivos possível.
- O **menor diff que funciona** vence — mas só depois de entender o problema. O menor diff no lugar errado não é preguiça, é um segundo bug.
- Questione pedidos complexos: "Você realmente precisa de X, ou Y já cobre?"
- Marque simplificações intencionais com `ponytail:` no comentário. Se o atalho tem teto conhecido, o comentário nomeia o teto e o caminho de upgrade.

---

## STACK OBRIGATÓRIA (PROTOCOL.md)

| Componente | Especificação |
|---|---|
| Backend | Laravel 12 com padrões Service Layer |
| Frontend | Bootstrap 5.3 (classes nativas). **Tailwind CSS proibido.** |
| Lógica cliente | Vanilla JavaScript. Sem frameworks JS complexos. |
| Banco | SQLite (hardcoded, zero-config). |
| Ícones | Bootstrap Icons (exclusivo). |

---

## REGRAS DE OURO DO FRONTEND (PROTOCOL.md)

- **`style` inline proibido.** Usar classes CSS.
- **`!important` proibido.** Usar especificidade.
- **Bootstrap First**: esgotar classes utilitárias Bootstrap antes de criar CSS customizado.
- **app.css**: classes customizadas só para o que Bootstrap 5.3 não resolve.
- Qualquer nova UI deve seguir o design system existente (cores, tipografia, espaçamento).

---

## SEGURANÇA E PRIVACIDADE (PROTOCOL.md)

- **Zero telemetria**: proibido incluir rastreamento, logs de usuário ou chamadas externas desnecessárias.
- **Integridade de conteúdo**: NUNCA editar arquivos `.md` do manuscrito/lore exceto por ordem expressa do usuário. Ghost Formatting = CSS/JS visual, sem tocar no markdown fonte.
- **Dados confidenciais**: armazenamento e processamento local (SQLite).

---

## NÃO É PREGUIÇA (Ponytail)

Nunca cortar cantos em: **entender o problema** (leia por completo antes de codificar), validação de input em trust boundaries, tratamento de erros com risco de perda de dados, segurança, acessibilidade, qualquer coisa explicitamente solicitada.

Código não-trivial deixa UM check rodável: o menor teste que falha se a lógica quebrar (assert, demo inline, ou um arquivo de teste; sem frameworks, sem fixtures). One-liners triviais não precisam de teste.

---

## COMMITS

- Cada commit = conclusão de um objetivo específico.
- Planejar em bullet points (função, arquivo, classes envolvidas) antes de codificar.
- Commits em português do Brasil, no formato: `verbo no presente: descrição curta`.
