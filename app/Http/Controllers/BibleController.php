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

    /**
     * Sincroniza a Bíblia gerando um resumo com IA a partir dos manuscritos.
     */
    public function syncBibleWithAI($project_uuid)
    {
        try {
            $project = Project::where('uuid', $project_uuid)->firstOrFail();
            $gemini = new \App\Services\GeminiService($project);

            // Coletar texto bruto dos manuscritos para enviar pra IA
            $sections = \App\Models\ManuscriptItem::whereIn('type', ['section', 'chapter', 'scene'])
                            ->orderBy('order')
                            ->get();
            
            $fullText = "";
            foreach ($sections as $section) {
                $path = "projects/{$project->uuid}/manuscript/{$section->uuid}.md";
                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
                    $fullText .= "=== {$section->title} ===\n";
                    $fullText .= \Illuminate\Support\Facades\Storage::disk('local')->get($path) . "\n\n";
                }
            }

            if (empty(trim($fullText))) {
                return response()->json(['error' => 'Manuscrito vazio. Escreva algo primeiro antes de sincronizar.'], 400);
            }

            $prompt = "Abaixo está o conteúdo bruto do manuscrito da obra em andamento.\n\n" .
                      "Sua tarefa: Analisar a narrativa e criar um 'Resumo de Acontecimentos / Cronologia' rico e detalhado. " .
                      "Este resumo deve funcionar como o 'Cerebelo' oficial do projeto para que eu possa consultar posteriormente sem me esquecer dos plot points, mistérios ou eventos que já ocorreram.\n\n" .
                      "Escreva em Markdown limpo (use H2, H3, bullet points). Seja conciso, mas não esconda detalhes cruciais.\n\n" .
                      "TEXTO BRUTO:\n" . $fullText;

            // Usa Flash para resumo rápido de grande volume
            $newSummary = $gemini->generate($prompt, 'gemini-3.0-flash', [
                "Você é um editor literário implacável encarregado de organizar a cronologia de eventos."
            ]);

            $project->update(['bible_summary' => $newSummary]);

            return response()->json([
                'success' => true,
                'message' => 'Sincronização concluída.',
                'summary' => $newSummary
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
