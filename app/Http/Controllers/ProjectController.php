<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

use App\Services\ProjectManager;

class ProjectController extends Controller
{
    protected $projectManager;

    public function __construct(ProjectManager $projectManager)
    {
        $this->projectManager = $projectManager;
    }

    /**
     * Display a listing of projects.
     */
    public function index()
    {
        $projects = Project::orderBy('last_opened_at', 'desc')->get();
        return view('projects.index', compact('projects'));
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $project = $this->projectManager->createProject([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'last_opened_at' => now()
        ]);

        return redirect('/projects/' . $project->uuid);
    }

    /**
     * Display the writing dashboard for a specific project.
     */
    public function show($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        // Update last opened timestamp
        $project->update(['last_opened_at' => now()]);

        return view('projects.dashboard', compact('project'));
    }

    /**
     * Display project settings.
     */
    public function settings($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        return view('projects.settings', compact('project'));
    }

    /**
     * Update project basic settings.
     */
    public function update(Request $request, $project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $project->update([
            'name' => $request->input('name'),
            'description' => $request->input('description')
        ]);

        return redirect()->back()->with('success', 'Configurações atualizadas com sucesso.');
    }

    /**
     * Update export metadata via JSON.
     */
    public function updateMetadata(Request $request, $project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'language' => 'nullable|string|max:10',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|string|max:255',
            'copyright_info' => 'nullable|string',
        ]);

        $project->update($request->only([
            'name', 'author', 'isbn', 'language', 'publisher', 'publication_date', 'copyright_info'
        ]));

        return response()->json(['success' => true]);
    }

    /**
     * Update the project cover image.
     */
    public function updateCover(Request $request, $project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        $request->validate([
            'image_uuid' => 'required|uuid|exists:gallery_items,uuid'
        ]);

        $project->update([
            'cover_image_uuid' => $request->input('image_uuid')
        ]);

        return response()->json([
            'message' => 'Capa atualizada com sucesso.',
            'cover_image_uuid' => $project->cover_image_uuid
        ]);
    }

    /**
     * Export project to ePub.
     */
    public function exportEpub($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $exporter = new \App\Services\ExporterService($project);
        $filePath = $exporter->generateEpub();

        return response()->download($filePath);
    }

    /**
     * Export project to PDF.
     */
    public function exportPdf($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $exporter = new \App\Services\ExporterService($project);
        $filePath = $exporter->generatePdf();

        return response()->download($filePath);
    }

    /**
     * Export project to HTML.
     */
    public function exportHtml($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $exporter = new \App\Services\ExporterService($project);
        $filePath = $exporter->generateHtml();

        return response()->download($filePath);
    }

    /**
     * Preview project HTML in browser.
     */
    public function previewHtml($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $exporter = new \App\Services\ExporterService($project);
        $filePath = $exporter->generateHtml();

        return response()->file($filePath);
    }
    /**
     * Export project to HTML ZIP Package.
     */
    public function exportZip($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $exporter = new \App\Services\ExporterService($project);
        $filePath = $exporter->generateHtmlZip();

        return response()->download($filePath);
    }
    /**
     * Get the current export state (existing files).
     */
    public function getExportState($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $exporter = new \App\Services\ExporterService($project);
        return response()->json($exporter->getExistingExports());
    }

    /**
     * Process multiple exports.
     */
    public function processBatchExport(\Illuminate\Http\Request $request, $project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $exporter = new \App\Services\ExporterService($project);
        $formats = $request->input('formats', []);
        
        // Trigger generation
        if (in_array('epub', $formats)) $exporter->generateEpub();
        if (in_array('pdf', $formats)) $exporter->generatePdf();
        if (in_array('html', $formats)) { 
            $exporter->generateHtml(); 
            $exporter->generateHtmlZip(); 
        }

        return response()->json($exporter->getExistingExports());
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        // Delete project assets directory
        $projectPath = "projects/{$project->uuid}";
        \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory($projectPath);
        
        $project->delete();

        return response()->json(['success' => true, 'message' => 'Projeto destruído permanentemente.']);
    }
}
