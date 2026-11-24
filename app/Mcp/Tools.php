<?php

namespace App\Mcp;

/**
 * Catálogo de ferramentas do MCP do Runaris Ghost.
 *
 * Cada entrada é só contrato (nome, descrição, JSON Schema) + o método do
 * Handlers que executa. A implementação fica em App\Mcp\Handlers.
 *
 * Convenção: o parâmetro `project` é sempre o uuid do projeto e `item`/`card`
 * são uuids dentro do banco daquele projeto.
 */
class Tools
{
    public const VERSION = '1.0.0';

    /** @var array<string, array{description: string, inputSchema: array, handler: string}> */
    protected array $registry;

    public function __construct(protected Handlers $handlers)
    {
        $this->registry = $this->build();
    }

    /** Lista no formato MCP (name/description/inputSchema). */
    public function definitions(): array
    {
        $definitions = [];

        foreach ($this->registry as $name => $tool) {
            $definitions[] = [
                'name' => $name,
                'description' => $tool['description'],
                'inputSchema' => $tool['inputSchema'],
            ];
        }

        usort($definitions, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $definitions;
    }

    public function call(string $name, array $arguments): mixed
    {
        if (!isset($this->registry[$name])) {
            throw new McpException("Ferramenta desconhecida: {$name}. Use tools/list para ver as disponíveis.");
        }

        $handler = $this->registry[$name]['handler'];

        return $this->handlers->{$handler}($arguments);
    }

    /** @return array<string, array{description: string, inputSchema: array, handler: string}> */
    protected function build(): array
    {
        $project = ['type' => 'string', 'description' => 'uuid do projeto'];

        return [
            // ---------------------------------------------------------- projetos
            'projects_list' => [
                'description' => 'Lista os projetos (santuários) do autor, do último aberto para o mais antigo.',
                'handler' => 'projectsList',
                'inputSchema' => $this->schema([]),
            ],
            'project_get' => [
                'description' => 'Detalhes de um projeto: metadados, contagem de seções/capítulos/cenas, fichas, palavras e snapshots.',
                'handler' => 'projectGet',
                'inputSchema' => $this->schema(['project' => $project], ['project']),
            ],
            'project_create' => [
                'description' => 'Cria um projeto vazio (estrutura de pastas + banco SQLite próprio).',
                'handler' => 'projectCreate',
                'inputSchema' => $this->schema([
                    'name' => $this->str('nome da obra'),
                    'description' => $this->str('sinopse curta (opcional)'),
                ], ['name']),
            ],
            'project_update' => [
                'description' => 'Atualiza metadados do projeto (nome, autor, ISBN, idioma, editora, data, copyright).',
                'handler' => 'projectUpdate',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'name' => $this->str('novo título'),
                    'description' => $this->str('sinopse'),
                    'author' => $this->str('autor'),
                    'isbn' => $this->str('ISBN'),
                    'language' => $this->str('idioma (ex.: pt-BR)'),
                    'publisher' => $this->str('editora'),
                    'publication_date' => $this->str('data de publicação'),
                    'copyright_info' => $this->str('texto de copyright'),
                ], ['project']),
            ],
            'project_delete' => [
                'description' => 'APAGA o projeto e todos os seus arquivos (irreversível). Exige confirm igual ao nome ou ao uuid do projeto.',
                'handler' => 'projectDelete',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'confirm' => $this->str('nome ou uuid do projeto, como confirmação'),
                ], ['project', 'confirm']),
            ],

            // -------------------------------------------------------- manuscrito
            'manuscript_tree' => [
                'description' => 'Árvore do manuscrito (Seção > Capítulo > Cena) com uuid, tipo, título e palavras.',
                'handler' => 'manuscriptTree',
                'inputSchema' => $this->schema(['project' => $project], ['project']),
            ],
            'manuscript_read' => [
                'description' => 'Lê uma cena/capítulo/seção: metadados, texto (.md) e planejamento (.beats.md).',
                'handler' => 'manuscriptRead',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'item' => $this->str('uuid do item'),
                ], ['project', 'item']),
            ],
            'manuscript_create' => [
                'description' => 'Cria um item no manuscrito (section, chapter ou scene), opcionalmente dentro de um pai.',
                'handler' => 'manuscriptCreate',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'title' => $this->str('título'),
                    'type' => $this->enum(['section', 'chapter', 'scene']),
                    'parent' => $this->str('uuid do item pai (opcional)'),
                ], ['project', 'title', 'type']),
            ],
            'manuscript_write' => [
                'description' => 'Grava o texto (.md) de um item. mode=replace sobrescreve; mode=append continua do fim. '
                    . 'Só use quando o autor pedir — o markdown é sagrado.',
                'handler' => 'manuscriptWrite',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'item' => $this->str('uuid do item'),
                    'content' => $this->str('texto em Markdown'),
                    'mode' => $this->enum(['replace', 'append'], 'replace'),
                ], ['project', 'item', 'content']),
            ],
            'manuscript_write_planning' => [
                'description' => 'Grava o planejamento/beats (.beats.md) de um item.',
                'handler' => 'manuscriptWritePlanning',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'item' => $this->str('uuid do item'),
                    'content' => $this->str('planejamento em Markdown'),
                ], ['project', 'item', 'content']),
            ],
            'manuscript_rename' => [
                'description' => 'Renomeia um item do manuscrito (atualiza o Índice e o nome do arquivo).',
                'handler' => 'manuscriptRename',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'item' => $this->str('uuid do item'),
                    'title' => $this->str('novo título'),
                ], ['project', 'item', 'title']),
            ],
            'manuscript_sort' => [
                'description' => 'Reordena/reparenta itens do manuscrito (drag & drop).',
                'handler' => 'manuscriptSort',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'items' => [
                        'type' => 'array',
                        'description' => 'lista de {uuid, order, parent_uuid?}',
                        'items' => $this->schema([
                            'uuid' => $this->str('uuid do item'),
                            'order' => ['type' => 'integer', 'description' => 'posição (1, 2, 3...)'],
                            'parent_uuid' => $this->str('uuid do pai (null para raiz)'),
                        ], ['uuid', 'order']),
                    ],
                ], ['project', 'items']),
            ],
            'manuscript_delete' => [
                'description' => 'APAGA um item do manuscrito e seus filhos (irreversível). Exige confirm igual ao uuid do item.',
                'handler' => 'manuscriptDelete',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'item' => $this->str('uuid do item'),
                    'confirm' => $this->str('uuid do item, como confirmação'),
                ], ['project', 'item', 'confirm']),
            ],
            'manuscript_summary' => [
                'description' => 'Gera (IA) e salva o resumo de um capítulo/cena — alimenta o Cerebelo.',
                'handler' => 'manuscriptSummary',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'item' => $this->str('uuid do item'),
                ], ['project', 'item']),
            ],

            // ------------------------------------------------------ worldbuilding
            'cards_list' => [
                'description' => 'Lista fichas de worldbuilding (personagens, cenários, objetos, lore).',
                'handler' => 'cardsList',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'type' => $this->enum(['character', 'scenario', 'object', 'lore']),
                ], ['project']),
            ],
            'card_read' => [
                'description' => 'Lê uma ficha completa (metadados + markdown).',
                'handler' => 'cardRead',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'card' => $this->str('uuid da ficha'),
                ], ['project', 'card']),
            ],
            'card_create' => [
                'description' => 'Cria uma ficha. Se content for enviado, já grava o corpo.',
                'handler' => 'cardCreate',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'title' => $this->str('nome da ficha'),
                    'type' => $this->enum(['character', 'scenario', 'object', 'lore'], 'character'),
                    'content' => $this->str('corpo em Markdown (opcional)'),
                ], ['project', 'title']),
            ],
            'card_write' => [
                'description' => 'Grava o corpo (markdown) de uma ficha.',
                'handler' => 'cardWrite',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'card' => $this->str('uuid da ficha'),
                    'content' => $this->str('corpo em Markdown'),
                ], ['project', 'card', 'content']),
            ],
            'card_update' => [
                'description' => 'Renomeia ou muda o tipo de uma ficha.',
                'handler' => 'cardUpdate',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'card' => $this->str('uuid da ficha'),
                    'title' => $this->str('novo nome'),
                    'type' => $this->enum(['character', 'scenario', 'object', 'lore']),
                ], ['project', 'card']),
            ],
            'card_delete' => [
                'description' => 'APAGA uma ficha e seu .md. Exige confirm igual ao uuid da ficha.',
                'handler' => 'cardDelete',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'card' => $this->str('uuid da ficha'),
                    'confirm' => $this->str('uuid da ficha, como confirmação'),
                ], ['project', 'card', 'confirm']),
            ],
            'cards_search' => [
                'description' => 'Busca fichas por título.',
                'handler' => 'cardsSearch',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'query' => $this->str('termo buscado'),
                ], ['project', 'query']),
            ],
            'graph_get' => [
                'description' => 'Grafo de worldbuilding: nós (fichas) e arestas (conexões).',
                'handler' => 'graphGet',
                'inputSchema' => $this->schema(['project' => $project], ['project']),
            ],
            'card_connections_get' => [
                'description' => 'Conexões de uma ficha.',
                'handler' => 'cardConnectionsGet',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'card' => $this->str('uuid da ficha'),
                ], ['project', 'card']),
            ],
            'card_connections_set' => [
                'description' => 'Define a lista de conexões de uma ficha (sync bidirecional).',
                'handler' => 'cardConnectionsSet',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'card' => $this->str('uuid da ficha'),
                    'related' => [
                        'type' => 'array',
                        'description' => 'uuids das fichas conectadas',
                        'items' => ['type' => 'string'],
                    ],
                ], ['project', 'card', 'related']),
            ],
            'card_suggest' => [
                'description' => 'IA sugere conexões para uma ficha (não grava nada — devolve sugestões).',
                'handler' => 'cardSuggest',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'card' => $this->str('uuid da ficha'),
                ], ['project', 'card']),
            ],

            // -------------------------------------------------------------- bíblia
            'bible_read' => [
                'description' => 'Lê a Bíblia do Mundo (lore) e o Cerebelo (resumo narrativo).',
                'handler' => 'bibleRead',
                'inputSchema' => $this->schema(['project' => $project], ['project']),
            ],
            'bible_write' => [
                'description' => 'Grava a Bíblia (content) e/ou o Cerebelo (summary).',
                'handler' => 'bibleWrite',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'content' => $this->str('Bíblia: regras de lore e mundo'),
                    'summary' => $this->str('Cerebelo: resumo narrativo'),
                ], ['project']),
            ],
            'bible_sync' => [
                'description' => 'IA consolida o Cerebelo. mode=cerebellum usa os resumos dos capítulos; '
                    . 'mode=manuscript lê o manuscrito bruto.',
                'handler' => 'bibleSync',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'mode' => $this->enum(['cerebellum', 'manuscript'], 'cerebellum'),
                ], ['project']),
            ],

            // ------------------------------------------------------------------ IA
            'ai_write_scene' => [
                'description' => 'Escritor Fantasma: escreve a prosa literária de uma cena a partir do planejamento + Bíblia. '
                    . 'Se item for informado e save=true, grava no .md.',
                'handler' => 'aiWriteScene',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'title' => $this->str('título da cena/capítulo'),
                    'planning' => $this->str('planejamento/beats (opcional)'),
                    'item' => $this->str('uuid do item para salvar (opcional)'),
                    'save' => $this->bool('gravar no item', false),
                    'append' => $this->bool('continuar do fim em vez de sobrescrever', false),
                ], ['project', 'title']),
            ],
            'ai_review' => [
                'description' => 'Revisor: corrige português e remove AI slop, preservando a voz do autor.',
                'handler' => 'aiReview',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'content' => $this->str('texto a revisar'),
                    'fix_grammar' => $this->bool('corrigir gramática', true),
                    'remove_slop' => $this->bool('remover clichês/slop', true),
                ], ['project', 'content']),
            ],
            'ai_suggest_card' => [
                'description' => 'IA sugere UMA nova ficha (personagem, local ou objeto) para o universo. Não grava.',
                'handler' => 'aiSuggestCard',
                'inputSchema' => $this->schema(['project' => $project], ['project']),
            ],
            'ai_planning' => [
                'description' => 'IA escreve 5-7 bullets de planejamento para uma cena. Não grava.',
                'handler' => 'aiPlanning',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'title' => $this->str('título da cena'),
                ], ['project', 'title']),
            ],

            // ------------------------------------------------- export e snapshots
            'export_generate' => [
                'description' => 'Gera exports do projeto. formats: epub, pdf, html, html_zip, markdown, backup.',
                'handler' => 'exportGenerate',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'formats' => [
                        'type' => 'array',
                        'description' => 'formatos desejados',
                        'items' => $this->enum(['epub', 'pdf', 'html', 'html_zip', 'markdown', 'backup']),
                    ],
                ], ['project', 'formats']),
            ],
            'exports_list' => [
                'description' => 'Lista os arquivos de export já gerados (nome, tamanho, data).',
                'handler' => 'exportsList',
                'inputSchema' => $this->schema(['project' => $project], ['project']),
            ],
            'snapshots_list' => [
                'description' => 'Lista os snapshots (backups locais) do projeto.',
                'handler' => 'snapshotsList',
                'inputSchema' => $this->schema(['project' => $project], ['project']),
            ],
            'snapshot_create' => [
                'description' => 'Cria um snapshot do projeto (banco + arquivos).',
                'handler' => 'snapshotCreate',
                'inputSchema' => $this->schema(['project' => $project], ['project']),
            ],
            'snapshot_restore' => [
                'description' => 'RESTAURA o projeto a um snapshot (sobrescreve estado atual). Exige confirm igual ao nome do snapshot.',
                'handler' => 'snapshotRestore',
                'inputSchema' => $this->schema([
                    'project' => $project,
                    'snapshot' => $this->str('nome do snapshot, ex.: Snapshot_2026-10-26_101500.zip'),
                    'confirm' => $this->str('nome do snapshot, como confirmação'),
                ], ['project', 'snapshot', 'confirm']),
            ],

            // ----------------------------------------------------------------- ops
            'system_status' => [
                'description' => 'Saúde da instalação: versão, modo (pacote Windows ou git), PHP, banco, IA configurada, token do MCP, update pendente.',
                'handler' => 'systemStatus',
                'inputSchema' => $this->schema([]),
            ],
            'update_check' => [
                'description' => 'Consulta o GitHub pela versão mais recente do Runaris Ghost.',
                'handler' => 'updateCheck',
                'inputSchema' => $this->schema([]),
            ],
            'update_apply' => [
                'description' => 'Baixa a atualização. Em instalação empacotada (Windows) ela é aplicada ao reabrir o start.bat.',
                'handler' => 'updateApply',
                'inputSchema' => $this->schema([]),
            ],
            'logs_tail' => [
                'description' => 'Últimas linhas do log da aplicação (storage/logs/laravel.log).',
                'handler' => 'logsTail',
                'inputSchema' => $this->schema([
                    'lines' => $this->int('quantidade de linhas', 80),
                ]),
            ],
        ];
    }

    // ------------------------------------------------------------- helpers de schema

    protected function schema(array $properties, array $required = []): array
    {
        $schema = ['type' => 'object', 'properties' => $properties];

        if ($required) {
            $schema['required'] = $required;
        }

        $schema['additionalProperties'] = false;

        return $schema;
    }

    protected function str(string $description): array
    {
        return ['type' => 'string', 'description' => $description];
    }

    protected function int(string $description, int $default): array
    {
        return ['type' => 'integer', 'description' => $description, 'default' => $default];
    }

    protected function bool(string $description, bool $default): array
    {
        return ['type' => 'boolean', 'description' => $description, 'default' => $default];
    }

    protected function enum(array $values, ?string $default = null): array
    {
        $schema = ['type' => 'string', 'enum' => $values];

        if ($default !== null) {
            $schema['default'] = $default;
        }

        return $schema;
    }
}
