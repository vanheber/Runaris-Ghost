<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
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

        $project = Project::create([
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

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
