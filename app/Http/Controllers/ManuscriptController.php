<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ManuscriptItem;
use App\Services\ManuscriptService;
use Illuminate\Http\Request;

class ManuscriptController extends Controller
{
    protected $manuscriptService;

    public function __construct(ManuscriptService $manuscriptService)
    {
        $this->manuscriptService = $manuscriptService;
    }

    /**
     * List all items in a project tree.
     */
    public function index($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $tree = $this->manuscriptService->getTree($project);
        return response()->json($tree);
    }

    /**
     * Store a new manuscript item (Seção, Capítulo, Cena).
     */
    public function store(Request $request, $project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        $request->validate([
            'title' => 'required|string',
            'type' => 'required|in:section,chapter,scene',
            'parent_uuid' => 'nullable|exists:sqlite_project.manuscript_items,uuid'
        ]);

        $item = $this->manuscriptService->createItem($project, $request->all());

        return response()->json($item);
    }

    /**
     * Show a single item with its content.
     */
    public function show($project_uuid, string $uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $item = ManuscriptItem::where('uuid', $uuid)->firstOrFail();
        $content = $this->manuscriptService->getContent($project, $uuid);

        return response()->json([
            'item' => $item,
            'content' => $content
        ]);
    }

    /**
     * Update item content (Auto-save).
     */
    public function update(Request $request, $project_uuid, string $uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $request->validate(['content' => 'required|string']);

        $this->manuscriptService->saveContent($project, $uuid, $request->input('content'));
        
        $item = ManuscriptItem::where('uuid', $uuid)->first();
        return response()->json($item);
    }

    /**
     * Update item title.
     */
    public function updateTitle(Request $request, $project_uuid, string $uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $request->validate(['title' => 'required|string']);

        $item = $this->manuscriptService->updateTitle($project, $uuid, $request->input('title'));

        return response()->json($item);
    }

    /**
     * Handle tree reordering (Drag & Drop).
     */
    public function destroy($project_uuid, string $uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $this->manuscriptService->deleteItem($project, $uuid);

        return response()->json(['status' => 'success']);
    }

    public function sort(Request $request, $project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $request->validate(['sorting' => 'required|array']);

        $this->manuscriptService->updateOrder($project, $request->input('sorting'));

        return response()->json(['status' => 'success']);
    }

    /**
     * Get planning/beats content for a manuscript item.
     */
    public function getPlanning($project_uuid, string $uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $content = $this->manuscriptService->getPlanning($project, $uuid);

        return response()->json(['content' => $content]);
    }

    /**
     * Save planning/beats content for a manuscript item.
     */
    public function savePlanning(Request $request, $project_uuid, string $uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $request->validate(['content' => 'required|string']);

        $this->manuscriptService->savePlanning($project, $uuid, $request->input('content'));

        return response()->json(['status' => 'success']);
    }

    /**
     * Generate an AI summary for a manuscript item.
     */
    public function generateSummary($project_uuid, string $uuid)
    {
        try {
            $project = Project::where('uuid', $project_uuid)->firstOrFail();
                
            $item = ManuscriptItem::where('uuid', $uuid)->firstOrFail();
            $content = $this->manuscriptService->getContent($project, $uuid);

            if (empty($content) || strlen($content) < 50) {
                return response()->json(['error' => __('Conteúdo muito curto para gerar resumo.')], 400);
            }

            $gemini = \App\Services\GeminiService::forProject($project_uuid);
            $prompt = "Você é um editor literário especialista em análise estrutural. " .
                      "Abaixo está o texto de um capítulo/cena intitulado '{$item->title}':\n\n" .
                      "--- INÍCIO DO TEXTO ---\n{$content}\n--- FIM DO TEXTO ---\n\n" .
                      "SUA TAREFA: Escreva um resumo conciso (máximo 150 palavras) dos acontecimentos desta seção. " .
                      "Foque em fatos, mudanças de estado emocional dos personagens e revelações de plot. " .
                      "Não use introduções, vá direto ao resumo.";

            $summary = $gemini->generate($prompt, 'gemini-2.5-flash');
            
            $item->update(['summary' => $summary]);

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
