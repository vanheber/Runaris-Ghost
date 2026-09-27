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

    /**
     * Pós-resposta: cria uma versão automática (commit + push com throttle)
     * após mutações em projetos. Nunca afeta a resposta ao usuário.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     */
    public function terminate($request, $response)
    {
        try {
            if (!$request->route('project_uuid')) {
                return;
            }

            if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                return;
            }

            app(\App\Services\GitVersioningService::class)->autoVersion('auto: ' . $this->actionName($request));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Auto-versionamento ignorado: ' . $e->getMessage());
        }
    }

    /** "Controller@metodo" a partir da rota, para a mensagem de commit. */
    protected function actionName($request): string
    {
        $uses = $request->route() ? $request->route()->getAction('uses') : null;

        if (is_string($uses) && str_contains($uses, '@')) {
            [$class, $method] = explode('@', $uses, 2);
            return class_basename($class) . '@' . $method;
        }

        if (is_array($uses) && isset($uses[0], $uses[1]) && is_string($uses[0])) {
            return class_basename($uses[0]) . '@' . $uses[1];
        }

        return 'request';
    }
}
