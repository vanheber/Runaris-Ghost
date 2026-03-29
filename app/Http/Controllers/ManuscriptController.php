<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ManuscriptItem;
use App\Services\ManuscriptService;
use App\Services\ProjectManager;
use Illuminate\Http\Request;

class ManuscriptController extends Controller
{
    protected $manuscriptService;
    protected $projectManager;

    public function __construct(ManuscriptService $manuscriptService, ProjectManager $projectManager)
    {
        $this->manuscriptService = $manuscriptService;
        $this->projectManager = $projectManager;
    }

    /**
     * List all items in a project tree.
     */
    public function index($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $this->projectManager->switchToProject($project);
        $tree = $this->manuscriptService->getTree($project);
        return response()->json($tree);
    }

    /**
     * Store a new manuscript item (Seção, Capítulo, Cena).
     */
    public function store(Request $request, $project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $this->projectManager->switchToProject($project);
        
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
        $this->projectManager->switchToProject($project);
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
        $this->projectManager->switchToProject($project);
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
        $this->projectManager->switchToProject($project);
        $request->validate(['title' => 'required|string']);

        $item = ManuscriptItem::where('uuid', $uuid)->firstOrFail();
        $item->update(['title' => $request->input('title')]);

        return response()->json($item);
    }

    /**
     * Handle tree reordering (Drag & Drop).
     */
    public function destroy($project_uuid, string $uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $this->projectManager->switchToProject($project);
        $this->manuscriptService->deleteItem($project, $uuid);

        return response()->json(['status' => 'success']);
    }

    public function sort(Request $request, $project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $this->projectManager->switchToProject($project);
        $request->validate(['sorting' => 'required|array']);

        $this->manuscriptService->updateOrder($request->input('sorting'));

        return response()->json(['status' => 'success']);
    }
}
