<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $projectUuid = $request->route('project_uuid');

        if ($projectUuid) {
            $project = \App\Models\Project::where('uuid', $projectUuid)->firstOrFail();
            
            app(\App\Services\ProjectManager::class)->switchToProject($project);
            
            // Share the project with all views
            view()->share('activeProject', $project);

            // Optional: Update last opened
            $project->update(['last_opened_at' => now()]);
        }

        return $next($request);
    }
}
