<?php

namespace App\Mcp;

use App\Http\Controllers\AiController;
use App\Http\Controllers\BibleController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ManuscriptController;
use App\Models\Card;
use App\Models\ManuscriptItem;
use App\Models\Project;
use App\Models\SystemSetting;
use App\Services\CardService;
use App\Services\ExporterService;
use App\Services\GeminiService;
use App\Services\ManuscriptService;
use App\Services\ProjectBackupService;
use App\Services\ProjectManager;
use App\Services\UpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Implementação das ferramentas do MCP.
 *
 * Regra da casa: não reimplementar o que o app já faz. Manuscrito e fichas
 * passam pelos mesmos Services que a interface usa (mesmos arquivos .md,
 * mesmo índice, mesmas regras). As ações de IA chamam os Controllers, porque
 * é lá que os prompts literários vivem — duplicar prompt é garantir que um
 * dia os dois divirjam.
 */
class Handlers
{
    // ================================================================ projetos

    public function projectsList(array $args): array
    {
        $projects = Project::orderByDesc('last_opened_at')->get();

        return [
            'count' => $projects->count(),
            'projects' => $projects->map(fn (Project $p) => [
                'uuid' => $p->uuid,
                'name' => $p->name,
                'description' => $p->description,
                'last_opened_at' => optional($p->last_opened_at)->toIso8601String(),
                'has_bible' => filled($p->bible_content) || filled($p->bible_summary),
            ])->all(),
        ];
    }

    public function projectGet(array $args): array
    {
        $project = $this->enter($args);

        $byType = ManuscriptItem::selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $cardsByType = Card::selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return [
            'project' => $this->projectPayload($project),
            'manuscript' => [
                'sections' => (int) ($byType['section'] ?? 0),
                'chapters' => (int) ($byType['chapter'] ?? 0),
                'scenes' => (int) ($byType['scene'] ?? 0),
                'words' => (int) ManuscriptItem::where('is_system', false)->sum('word_count'),
            ],
            'cards' => [
                'total' => (int) $cardsByType->sum(),
                'by_type' => $cardsByType->all(),
            ],
            'snapshots' => count(app(ProjectBackupService::class)->listSnapshots($project)),
        ];
    }

    public function projectCreate(array $args): array
    {
        $project = app(ProjectManager::class)->createProject([
            'name' => $this->arg($args, 'name'),
            'description' => $this->opt($args, 'description'),
            'last_opened_at' => now(),
        ]);

        return ['created' => true, 'project' => $this->projectPayload($project)];
    }

    public function projectUpdate(array $args): array
    {
        $project = $this->enter($args);

        $fields = ['name', 'description', 'author', 'isbn', 'language', 'publisher', 'publication_date', 'copyright_info'];
        $changes = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $args)) {
                $changes[$field] = $args[$field];
            }
        }

        if (array_key_exists('name', $changes) && trim((string) $changes['name']) === '') {
            throw new McpException('O nome do projeto não pode ficar vazio.');
        }

        if (!$changes) {
            throw new McpException('Nada para atualizar: informe ao menos um campo.');
        }

        $project->update($changes);

        return ['updated' => array_keys($changes), 'project' => $this->projectPayload($project->fresh())];
    }

    public function projectDelete(array $args): array
    {
        $project = $this->enter($args);

        if ($this->arg($args, 'confirm') !== $project->uuid && $this->arg($args, 'confirm') !== $project->name) {
            throw new McpException('Confirmação inválida: envie o uuid ou o nome exato do projeto.');
        }

        // File (caminho absoluto), não Storage: em DEV projects é symlink para o
        // Runaris-Vault e o Flysystem recusa sair da raiz do disco (throw: false,
        // falha silenciosa) — sobrava a pasta com banco e manuscrito.
        File::deleteDirectory($project->getStoragePath());
        // exports ficam no disco público (storage/app/public), fora da pasta do
        // projeto: sem isto o livro exportado sobrevive ao projeto apagado.
        File::deleteDirectory(Storage::disk('public')->path("projects/{$project->uuid}"));
        $project->delete();

        return ['deleted' => true];
    }

    // ============================================================== manuscrito

    public function manuscriptTree(array $args): array
    {
        $project = $this->enter($args);
        $tree = app(ManuscriptService::class)->getTree($project);

        return ['tree' => json_decode(json_encode($tree), true)];
    }

    public function manuscriptRead(array $args): array
    {
        $project = $this->enter($args);
        $item = $this->item($args);
        $service = app(ManuscriptService::class);

        return [
            'item' => $item->toArray(),
            'content' => $service->getContent($project, $item->uuid),
            'planning' => $service->getPlanning($project, $item->uuid),
        ];
    }

    public function manuscriptCreate(array $args): array
    {
        $project = $this->enter($args);

        $type = $this->arg($args, 'type');
        if (!in_array($type, ['section', 'chapter', 'scene'], true)) {
            throw new McpException('type deve ser section, chapter ou scene.');
        }

        $parent = $this->opt($args, 'parent');
        if ($parent !== null) {
            $this->item($args, 'parent');
        }

        $item = app(ManuscriptService::class)->createItem($project, [
            'title' => $this->arg($args, 'title'),
            'type' => $type,
            'parent_uuid' => $parent,
        ]);

        return ['created' => true, 'item' => $item->fresh()->toArray()];
    }

    public function manuscriptWrite(array $args): array
    {
        $project = $this->enter($args);
        $item = $this->item($args);
        $content = $this->arg($args, 'content', allowEmpty: true);
        $mode = $this->opt($args, 'mode', 'replace');
        $service = app(ManuscriptService::class);

        if ($mode === 'append') {
            $previous = $service->getContent($project, $item->uuid);
            $content = rtrim($previous) . ($previous === '' ? '' : "\n\n") . $content;
        } elseif ($mode !== 'replace') {
            throw new McpException('mode deve ser replace ou append.');
        }

        $service->saveContent($project, $item->uuid, $content);
        $item = $item->fresh();

        return [
            'saved' => true,
            'mode' => $mode,
            'chars' => mb_strlen($content),
            'words' => (int) $item->word_count,
            'item' => $item->toArray(),
        ];
    }

    public function manuscriptWritePlanning(array $args): array
    {
        $project = $this->enter($args);
        $item = $this->item($args);

        app(ManuscriptService::class)->savePlanning($project, $item->uuid, $this->arg($args, 'content', allowEmpty: true));

        return ['saved' => true, 'item' => $item->fresh()->toArray()];
    }

    public function manuscriptRename(array $args): array
    {
        $project = $this->enter($args);
        $item = $this->item($args);

        $updated = app(ManuscriptService::class)->updateTitle($project, $item->uuid, $this->arg($args, 'title'));

        return ['renamed' => true, 'item' => $updated->fresh()->toArray()];
    }

    public function manuscriptSort(array $args): array
    {
        $project = $this->enter($args);
        $items = $args['items'] ?? null;

        if (!is_array($items) || $items === []) {
            throw new McpException('items deve ser uma lista não vazia de {uuid, order, parent_uuid?}.');
        }

        $sorting = [];
        foreach ($items as $index => $entry) {
            if (!is_array($entry) || empty($entry['uuid']) || !isset($entry['order'])) {
                throw new McpException("items[{$index}] precisa de uuid e order.");
            }

            $sorting[] = [
                'uuid' => (string) $entry['uuid'],
                'order' => (int) $entry['order'],
                'parent_uuid' => $entry['parent_uuid'] ?? null,
            ];
        }

        app(ManuscriptService::class)->updateOrder($project, $sorting);

        return ['sorted' => count($sorting)];
    }

    public function manuscriptDelete(array $args): array
    {
        $project = $this->enter($args);
        $item = $this->item($args);

        if ($this->arg($args, 'confirm') !== $item->uuid) {
            throw new McpException('Confirmação inválida: envie o uuid do item em confirm.');
        }

        app(ManuscriptService::class)->deleteItem($project, $item->uuid);

        return ['deleted' => true, 'item' => $item->uuid, 'title' => $item->title];
    }

    public function manuscriptSummary(array $args): array
    {
        $project = $this->enter($args);
        $item = $this->item($args);

        return $this->json(app(ManuscriptController::class)->generateSummary($project->uuid, $item->uuid));
    }

    // ============================================================ worldbuilding

    public function cardsList(array $args): array
    {
        $this->enter($args);
        $type = $this->opt($args, 'type');

        $cards = Card::query()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderBy('title')
            ->get(['uuid', 'type', 'title', 'summary', 'image_uuid']);

        return ['count' => $cards->count(), 'cards' => $cards->toArray()];
    }

    public function cardRead(array $args): array
    {
        $project = $this->enter($args);
        $card = $this->card($args);

        return [
            'card' => $card->toArray(),
            'content' => app(CardService::class)->getMarkdownBody($project, $card),
        ];
    }

    public function cardCreate(array $args): array
    {
        $project = $this->enter($args);
        $type = $this->opt($args, 'type', 'character');

        if (!in_array($type, ['character', 'scenario', 'object', 'lore'], true)) {
            throw new McpException('type deve ser character, scenario, object ou lore.');
        }

        $service = app(CardService::class);
        $card = $service->createCard($project, $this->arg($args, 'title'), $type);

        if (array_key_exists('content', $args) && trim((string) $args['content']) !== '') {
            $service->updateCardContent($project, $card, (string) $args['content']);
        }

        return ['created' => true, 'card' => $card->fresh()->toArray()];
    }

    public function cardWrite(array $args): array
    {
        $project = $this->enter($args);
        $card = $this->card($args);

        app(CardService::class)->updateCardContent($project, $card, $this->arg($args, 'content', allowEmpty: true));

        return ['saved' => true, 'card' => $card->fresh()->toArray()];
    }

    public function cardUpdate(array $args): array
    {
        $project = $this->enter($args);
        $card = $this->card($args);
        $service = app(CardService::class);
        $changed = [];

        if (!empty($args['title'])) {
            $service->updateCardTitle($project, $card, (string) $args['title']);
            $changed[] = 'title';
        }

        if (!empty($args['type'])) {
            if (!in_array($args['type'], ['character', 'scenario', 'object', 'lore'], true)) {
                throw new McpException('type deve ser character, scenario, object ou lore.');
            }
            $service->updateCardType($project, $card, (string) $args['type']);
            $changed[] = 'type';
        }

        if (!$changed) {
            throw new McpException('Nada para atualizar: informe title e/ou type.');
        }

        return ['updated' => $changed, 'card' => $card->fresh()->toArray()];
    }

    public function cardDelete(array $args): array
    {
        $project = $this->enter($args);
        $card = $this->card($args);

        if ($this->arg($args, 'confirm') !== $card->uuid) {
            throw new McpException('Confirmação inválida: envie o uuid da ficha em confirm.');
        }

        app(CardService::class)->deleteCard($project, $card);

        return ['deleted' => true, 'card' => $card->uuid, 'title' => $card->title];
    }

    public function cardsSearch(array $args): array
    {
        $this->enter($args);
        $query = $this->arg($args, 'query');

        $cards = Card::where('title', 'like', "%{$query}%")
            ->limit(20)
            ->get(['uuid', 'title', 'type']);

        return ['count' => $cards->count(), 'cards' => $cards->toArray()];
    }

    public function graphGet(array $args): array
    {
        $this->enter($args);

        $nodes = Card::select('uuid', 'title', 'type')->get();
        $links = DB::connection('sqlite_project')
            ->table('card_connections')
            ->select('card_uuid as source', 'related_card_uuid as target')
            ->get();

        return ['nodes' => $nodes->toArray(), 'links' => $links->toArray()];
    }

    public function cardConnectionsGet(array $args): array
    {
        $this->enter($args);
        $card = $this->card($args);

        return ['connections' => $card->connections->map(fn (Card $c) => [
            'uuid' => $c->uuid,
            'title' => $c->title,
            'type' => $c->type,
            'metadata' => $c->pivot->metadata ? json_decode($c->pivot->metadata, true) : null,
        ])->values()->all()];
    }

    public function cardConnectionsSet(array $args): array
    {
        $project = $this->enter($args);
        $card = $this->card($args);
        $related = $args['related'] ?? null;

        if (!is_array($related)) {
            throw new McpException('related deve ser uma lista de uuids.');
        }

        return $this->json(app(CardController::class)->syncConnections(
            $this->request(['related_uuids' => array_values($related)]),
            $project->uuid,
            $card->uuid,
        ));
    }

    public function cardSuggest(array $args): array
    {
        $project = $this->enter($args);
        $card = $this->card($args);

        return ['suggestions' => $this->json(app(CardController::class)->suggest(
            $this->request([]),
            $project->uuid,
            $card->uuid,
        ))];
    }

    // ================================================================= bíblia

    public function bibleRead(array $args): array
    {
        $project = $this->enter($args);

        return $this->json(app(BibleController::class)->show($project->uuid));
    }

    public function bibleWrite(array $args): array
    {
        $project = $this->enter($args);
        $payload = [];

        if (array_key_exists('content', $args)) {
            $payload['content'] = (string) $args['content'];
        }

        if (array_key_exists('summary', $args)) {
            $payload['summary'] = (string) $args['summary'];
        }

        if (!$payload) {
            throw new McpException('Nada para gravar: informe content (Bíblia) e/ou summary (Cerebelo).');
        }

        return $this->json(app(BibleController::class)->update($this->request($payload), $project->uuid));
    }

    public function bibleSync(array $args): array
    {
        $project = $this->enter($args);
        $mode = $this->opt($args, 'mode', 'cerebellum');
        $controller = app(BibleController::class);

        return $mode === 'manuscript'
            ? $this->json($controller->syncBibleWithAI($project->uuid))
            : $this->json($controller->syncCerebellum($project->uuid));
    }

    // ===================================================================== IA

    public function aiWriteScene(array $args): array
    {
        $project = $this->enter($args);

        $result = $this->json(app(AiController::class)->writeScene(
            $this->request([
                'title' => $this->arg($args, 'title'),
                'planning' => $this->opt($args, 'planning', ''),
            ]),
            $project->uuid,
        ));

        $itemUuid = $this->opt($args, 'item');
        $save = filter_var($args['save'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$itemUuid || !$save) {
            return $result + ['saved' => false];
        }

        $item = $this->item(['item' => $itemUuid], 'item');
        $content = (string) ($result['content'] ?? '');
        $append = filter_var($args['append'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $service = app(ManuscriptService::class);

        if ($append) {
            $previous = $service->getContent($project, $item->uuid);
            $content = rtrim($previous) . ($previous === '' ? '' : "\n\n") . $content;
        }

        $service->saveContent($project, $item->uuid, $content);

        return $result + ['saved' => true, 'item' => $item->fresh()->toArray()];
    }

    public function aiReview(array $args): array
    {
        $project = $this->enter($args);

        return $this->json(app(AiController::class)->reviewScene(
            $this->request([
                'content' => $this->arg($args, 'content', allowEmpty: true),
                'fix_grammar' => filter_var($args['fix_grammar'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'remove_slop' => filter_var($args['remove_slop'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ]),
            $project->uuid,
        ));
    }

    public function aiSuggestCard(array $args): array
    {
        $project = $this->enter($args);

        return $this->json(app(AiController::class)->suggestCard($this->request([]), $project->uuid));
    }

    public function aiPlanning(array $args): array
    {
        $project = $this->enter($args);

        return $this->json(app(AiController::class)->generatePlanning(
            $this->request(['title' => $this->arg($args, 'title')]),
            $project->uuid,
        ));
    }

    // ================================================== exportação e snapshots

    public function exportGenerate(array $args): array
    {
        $project = $this->enter($args);
        $formats = $args['formats'] ?? null;

        if (!is_array($formats) || $formats === []) {
            throw new McpException('formats deve ser uma lista: epub, pdf, html, html_zip, markdown, backup.');
        }

        $exporter = new ExporterService($project);
        $generated = [];

        foreach ($formats as $format) {
            $generated[$format] = match ($format) {
                'epub' => $exporter->generateEpub(),
                'pdf' => $exporter->generatePdf(),
                'html' => $exporter->generateHtml(),
                'html_zip' => $exporter->generateHtmlZip(),
                'markdown' => $exporter->generateMarkdownZip(),
                'backup' => $exporter->generateFullBackupZip(),
                default => throw new McpException("Formato desconhecido: {$format}"),
            };
        }

        return ['generated' => $generated, 'files' => $this->exportFiles($project)];
    }

    public function exportsList(array $args): array
    {
        $project = $this->enter($args);

        return ['files' => $this->exportFiles($project)];
    }

    public function snapshotsList(array $args): array
    {
        $project = $this->enter($args);
        $dir = storage_path("app/private/projects/{$project->uuid}/snapshots");

        $snapshots = array_map(function (array $snapshot) use ($dir) {
            $snapshot['path'] = $dir . '/' . $snapshot['name'];

            return $snapshot;
        }, app(ProjectBackupService::class)->listSnapshots($project));

        return ['snapshots' => $snapshots];
    }

    public function snapshotCreate(array $args): array
    {
        $project = $this->enter($args);
        $name = app(ProjectBackupService::class)->createSnapshot($project);

        if (!$name) {
            throw new McpException('Falha ao criar o snapshot.');
        }

        return ['created' => true, 'snapshot' => $name];
    }

    public function snapshotRestore(array $args): array
    {
        $project = $this->enter($args);
        $snapshot = $this->arg($args, 'snapshot');

        if ($this->arg($args, 'confirm') !== $snapshot) {
            throw new McpException('Confirmação inválida: envie o nome do snapshot em confirm.');
        }

        if (!app(ProjectBackupService::class)->restoreSnapshot($project, $snapshot)) {
            throw new McpException("Snapshot não encontrado ou inválido: {$snapshot}");
        }

        return ['restored' => true, 'snapshot' => $snapshot];
    }

    // ==================================================================== ops

    public function systemStatus(array $args): array
    {
        $logPath = storage_path('logs/laravel.log');
        $dbPath = database_path('database.sqlite');

        return [
            'app' => [
                'name' => config('app.name'),
                'version' => config('app.version'),
                'env' => config('app.env'),
                'debug' => (bool) config('app.debug'),
                'url' => config('app.url'),
                'locale' => config('app.locale'),
            ],
            'install' => [
                'packaged' => is_dir(base_path('runtime')),
                'git' => is_dir(base_path('.git')),
                'path' => base_path(),
            ],
            'runtime' => [
                'php' => PHP_VERSION,
                'os' => PHP_OS_FAMILY,
            ],
            'database' => [
                'connection' => config('database.default'),
                'size_bytes' => file_exists($dbPath) ? filesize($dbPath) : 0,
            ],
            'projects' => Project::count(),
            'ai' => [
                'configured' => filled(SystemSetting::getSetting('gemini_api_key')),
                'flash_model' => GeminiService::effectiveModel(GeminiService::FLASH),
                'pro_model' => GeminiService::effectiveModel(GeminiService::PRO),
            ],
            'mcp' => [
                'version' => Tools::VERSION,
                'port' => config('mcp.port'),
                'token_set' => filled(config('mcp.token')),
            ],
            'update' => [
                'pending_zip' => file_exists(storage_path('updates/pending.zip')),
            ],
            'logs' => [
                'path' => $logPath,
                'size_bytes' => file_exists($logPath) ? filesize($logPath) : 0,
            ],
        ];
    }

    public function updateCheck(array $args): array
    {
        return app(UpdateService::class)->checkUpdate();
    }

    public function updateApply(array $args): array
    {
        $service = app(UpdateService::class);

        try {
            $service->runUpdate();
        } catch (\Throwable $e) {
            throw new McpException('Falha na atualização: ' . $e->getMessage());
        }

        $packaged = $service->isPackagedInstall();

        return [
            'staged' => $packaged,
            'restart_required' => $packaged,
            'message' => $packaged
                ? 'Pacote baixado. Feche o start.bat e abra novamente para aplicar a atualização.'
                : 'Sistema atualizado.',
        ];
    }

    public function logsTail(array $args): array
    {
        $path = storage_path('logs/laravel.log');
        $lines = max(1, min(1000, (int) $this->opt($args, 'lines', 80)));

        if (!file_exists($path)) {
            return ['lines' => [], 'note' => 'Nenhum log encontrado.'];
        }

        $handle = fopen($path, 'rb');
        $size = filesize($path);
        $read = (int) min($size, 256 * 1024);
        fseek($handle, -$read, SEEK_END);
        $buffer = fread($handle, $read) ?: '';
        fclose($handle);

        $all = preg_split('/\r?\n/', trim($buffer)) ?: [];
        $tail = array_slice($all, -$lines);

        return ['path' => $path, 'size_bytes' => $size, 'lines' => array_values($tail)];
    }

    // ================================================================ helpers

    /** Resolve o projeto pelo uuid e ativa o banco SQLite dele. */
    protected function enter(array $args): Project
    {
        $uuid = $this->arg($args, 'project');
        $project = Project::where('uuid', $uuid)->first();

        if (!$project) {
            throw new McpException("Projeto não encontrado: {$uuid}. Use projects_list.");
        }

        try {
            app(ProjectManager::class)->switchToProject($project);
        } catch (\Throwable $e) {
            throw new McpException("Não foi possível abrir o projeto {$project->name}: " . $e->getMessage());
        }

        return $project;
    }

    protected function item(array $args, string $key = 'item'): ManuscriptItem
    {
        $uuid = $this->arg($args, $key);
        $item = ManuscriptItem::where('uuid', $uuid)->first();

        if (!$item) {
            throw new McpException("Item do manuscrito não encontrado: {$uuid}. Use manuscript_tree.");
        }

        return $item;
    }

    protected function card(array $args): Card
    {
        $uuid = $this->arg($args, 'card');
        $card = Card::where('uuid', $uuid)->first();

        if (!$card) {
            throw new McpException("Ficha não encontrada: {$uuid}. Use cards_list.");
        }

        return $card;
    }

    protected function arg(array $args, string $key, bool $allowEmpty = false): string
    {
        if (!array_key_exists($key, $args)) {
            throw new McpException("Parâmetro obrigatório ausente: {$key}.");
        }

        $value = $args[$key];

        if (!is_scalar($value)) {
            throw new McpException("Parâmetro {$key} deve ser texto.");
        }

        $value = (string) $value;

        if (!$allowEmpty && trim($value) === '') {
            throw new McpException("Parâmetro {$key} não pode ficar vazio.");
        }

        return $value;
    }

    protected function opt(array $args, string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $args) && $args[$key] !== '' ? $args[$key] : $default;
    }

    /** Request sintética: os controllers só leem input, não sessão nem CSRF. */
    protected function request(array $params): Request
    {
        return Request::create('/mcp', 'POST', $params);
    }

    /**
     * Extrai o JSON de um controller, transformando `{error: ...}` em erro de
     * ferramenta (o modelo lê a mensagem e corrige).
     */
    protected function json($response): array
    {
        $data = json_decode((string) $response->getContent(), true);

        if (($response->getStatusCode() ?? 200) >= 400) {
            $message = is_array($data) ? ($data['error'] ?? $data['message'] ?? 'Falha na operação.') : 'Falha na operação.';
            throw new McpException(is_string($message) ? $message : json_encode($message));
        }

        if (!is_array($data)) {
            throw new McpException('Resposta inesperada da aplicação.');
        }

        return $data;
    }

    /** Arquivos de export no disco (nome, caminho absoluto, tamanho, data). */
    protected function exportFiles(Project $project): array
    {
        $dir = Storage::disk('public')->path("projects/{$project->uuid}/exports");

        if (!is_dir($dir)) {
            return [];
        }

        $files = [];

        foreach (File::files($dir) as $file) {
            $files[] = [
                'name' => $file->getFilename(),
                'path' => $file->getPathname(),
                'size_bytes' => $file->getSize(),
                'modified_at' => date(DATE_ATOM, $file->getMTime()),
            ];
        }

        usort($files, fn ($a, $b) => strcmp($b['modified_at'], $a['modified_at']));

        return $files;
    }

    protected function projectPayload(Project $project): array
    {
        return [
            'uuid' => $project->uuid,
            'name' => $project->name,
            'description' => $project->description,
            'author' => $project->author,
            'isbn' => $project->isbn,
            'language' => $project->language,
            'publisher' => $project->publisher,
            'publication_date' => $project->publication_date,
            'copyright_info' => $project->copyright_info,
            'has_bible' => filled($project->bible_content),
            'has_cerebellum' => filled($project->bible_summary),
            'last_opened_at' => optional($project->last_opened_at)->toIso8601String(),
        ];
    }
}
