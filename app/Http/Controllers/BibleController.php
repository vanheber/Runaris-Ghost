<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class BibleController extends Controller
{
    /**
     * Get the global bible content for a project.
     */
    public function show($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        return response()->json([
            'content' => $project->bible_content ?? "# Bíblia do Projeto\n\nDefina aqui os pilares do seu mundo, tom de voz e sinopse geral.",
            'summary' => $project->bible_summary ?? "# Resumo Narrativo (Geração IA)\n\nClique em 'Sincronizar Bíblia' para que a IA atualize o resumo baseada no seu manuscrito."
        ]);
    }

    /**
     * Update the global bible content for a project.
     */
    public function update(Request $request, $project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        $updateData = [];
        if ($request->has('content')) $updateData['bible_content'] = $request->input('content');
        if ($request->has('summary')) $updateData['bible_summary'] = $request->input('summary');
        
        $project->update($updateData);

        return response()->json(['message' => 'Bíblia atualizada com sucesso.']);
    }
}
