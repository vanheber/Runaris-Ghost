<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Project;
use App\Services\ProjectManager;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    protected $projectManager;

    public function __construct(ProjectManager $projectManager)
    {
        $this->projectManager = $projectManager;
    }

    /**
     * List all projects (from Main DB).
     */
    public function index()
    {
        $projects = Project::orderBy('last_opened_at', 'desc')->get();
        return view('projects.index', compact('projects'));
    }

    /**
     * Create a new project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = $this->projectManager->createProject($validated);

        return redirect('/projects')->with('success', 'Projeto criado com sucesso!');
    }

    /**
     * Show a project context (Dynamic Switching test).
     */
    public function show($project_uuid)
    {
        // Project context is already handled by Middleware
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        return view('projects.dashboard', compact('project'));
    }
}
