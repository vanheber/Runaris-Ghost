# Guia do Runaris Ghost - v1.2.0

O Runaris Ghost é um ambiente de escrita imersivo projetado para autores que buscam organizar o caos da criação literária em uma jornada estruturada.

## 0. Primeiros Passos e Configuração
Ao iniciar o aplicativo pela primeira vez, você passará pelo fluxo de Onboarding profissional:
*   **Idioma**: Escolha entre Português (BR/PT), Inglês ou Espanhol para a interface.
*   **Perfil e Segurança**: Defina seu nome de autor e escolha se deseja proteger seus manuscritos com uma senha local.
*   **IA (Opcional)**: Configure sua chave da API do Google Gemini para habilitar as funções de co-autoria.

## 1. Gestão de Projetos
Cada projeto representa uma obra única (livro ou série). Na tela principal, você pode gerenciar suas histórias e configurar a **Capa do Livro**.
*   **Capas**: Recomendamos o uso de imagens na proporção 1600x2560 (padrão Amazon KDP/Apple Books). O sistema otimiza automaticamente o peso e a qualidade (JPG).

## 2. Manuscrito
O coração do seu livro. Organizado em uma estrutura de árvore:
*   **Seções**: Servem para agrupar capítulos ou partes do livro. Você pode criar capítulos diretamente de uma seção usando o ícone `+` na árvore.
*   **Capítulos**: Blocos de narrativa contínua. Você pode criar cenas diretamente de um capítulo usando o ícone `+`.
*   **Cenas de Raiz**: Use o botão de "Nova Cena" no topo da árvore para criar arquivos independentes (Prólogos, Prefácios) fora de seções.
*   **Planejamento**: Cada capítulo possui um modo de "Planejamento" alternável, onde você pode rascunhar cenas antes de escrever a versão final.
*   **Editor Limpo**: Ao criar um novo item, o editor inicia totalmente em branco. O título é gerenciado na árvore lateral para evitar duplicação de conteúdo no manuscrito.
2.1. Modo Literário e Ghost Formatting
O editor possui uma camada visual inteligente chamada **Ghost Formatting**:
*   **Imagens Imersivas**: Tags de imagem `![alt](url)` aparecem como miniaturas elegantes no texto.
*   **Troca Rápida**: Clique em qualquer imagem no editor para abrir a galeria e escolher uma substituta instantaneamente.
*   **Modo Raio-X**: Precisa ver o código Markdown puro? Use o botão **Markdown (Raio-X)** na barra de ferramentas. Todas as ferramentas de formatação continuam ativas neste modo.

## 3. Worldbuilding (Construção de Mundo)
Organize os pilares da sua narrativa através de fichas interativas:
*   **Personagens**: Registre aparência, motivações e segredos.
*   **Geografia**: Descreva cenários, cidades e regras do ambiente.
*   **Objetos**: Catalogue itens importantes, relíquias ou ferramentas.
*   **Conexões**: O sistema gera um **Grafo de Relacionamentos**, permitindo visualizar como personagens se cruzam e interagem com cenários e objetos.

## 4. Bíblia do Mundo
O repositório central da "Verdade" do seu universo.
*   **Lore**: Descrições profundas sobre as regras do seu mundo.
*   **IA Recap**: Um resumo narrativo que a IA mantém atualizado para ajudar você a não perder o fio da meada em tramas longas.

## 5. Exportação e Backup Soberano
O Runaris Ghost prioriza a soberania dos seus dados. Você pode exportar seu trabalho a qualquer momento em formatos profissionais ou brutos:
*   **Formatos de Leitura**: Gere **ePub** (Kindle), **PDF** (Impressão) ou um **Pacote Web** (HTML). A exportação é **recursiva**, preservando toda a hierarquia de Seções, Capítulos e Cenas definida na sua árvore lateral.
*   **Backup Human-Readable**: Gera um arquivo ZIP estruturado com todo o conteúdo em **Markdown puro**. Diferente de backups técnicos, aqui os arquivos usam os **nomes reais** das cenas e categorias, facilitando a portabilidade para qualquer outro editor (como Obsidian ou Notion).
*   **Snapshots & Rollback**: Você pode criar "Snapshots" — pontos de restauração que capturam o estado exato do banco de dados e arquivos do projeto. Se algo der errado ou você se arrepender de uma mudança, pode fazer o **Rollback** instantâneo para um estado anterior.
*   **Restauração Externa**: Permite importar um arquivo ZIP de backup para restaurar o projeto ou movê-lo entre diferentes instalações do Runaris Ghost.

## 6. Inteligência Artificial (Botões Mágicos) 🌟
O Runaris Ghost utiliza a tecnologia **Google Gemini** para atuar como seu co-autor:
*   **Escritor Fantasma**: No modo *Escrita*, a IA redige parágrafos literários baseando-se no que você planejou e no lore do seu mundo.
*   **Sugerir Ideias**: No modo *Planejamento*, a IA sugere pontos de conflito, objetivos e reviravoltas para estruturar sua cena.
*   **Sincronização da Bíblia**: A IA lê seu manuscrito e gera resumos narrativos automáticos.

---
*Nota: Este guia serve tanto como manual para usuários quanto como base de contexto para sistemas de inteligência artificial que auxiliem na escrita.*
