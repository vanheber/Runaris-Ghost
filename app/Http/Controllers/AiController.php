<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ManuscriptItem;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function suggestCard(Request $request, $project_uuid)
    {
        try {
            $project = Project::where('uuid', $project_uuid)->firstOrFail();
            $gemini = new GeminiService($project);

            $context = $gemini->buildMasterContext();
            
            $prompt = "Você é um assistente de Worldbuilding criativo. Avalie o contexto do mundo atual:\n\n{$context}\n\n" .
                      "SUA TAREFA: Sugira UMA nova ficha para este universo. Pode ser um personagem intrigante não detalhado antes, um local inexplorado ou um objeto essencial para o plot.\n" .
                      "Responda EXATAMENTE neste formato JSON puro (sem markup markdown markdown tags ```json), para que possa ser inserido no banco:\n" .
                      '{"title": "Nome sugerido", "type": "character", "content": "Descrição rica em detalhes, motivações e importância para o cenário."}' . "\n\n" .
                      "Nota: 'type' deve ser obrigatoriamente um destes: character, scenario, object.";

            $response = $gemini->generate($prompt, 'gemini-3.0-flash');
            
            // Clean markdown code blocks if the model insists
            $jsonStr = trim(preg_replace('/^```json\s*|\s*```$/i', '', $response));
            
            $data = json_decode($jsonStr, true);
            if (!$data) {
                // Tenta extrair qualquer formato embutido
                throw new \Exception("A resposta não veio em formato JSON suportado.");
            }

            return response()->json([
                'success' => true,
                'suggestion' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generatePlanning(Request $request, $project_uuid)
    {
        try {
            $project = Project::where('uuid', $project_uuid)->firstOrFail();
            $gemini = new GeminiService($project);

            $context = $gemini->buildMasterContext();
            $sceneTitle = $request->input('title', 'Cena Sem Título');

            $prompt = "Você é um mestre da narração e estruturação dramática. Baseie-se na Bíblia deste projeto:\n\n{$context}\n\n" .
                      "O autor quer planejar uma nova cena/capítulo com o título '{$sceneTitle}'.\n" .
                      "SUA TAREFA: Escreva 5 a 7 bullet points (em Markdown) traçando os acontecimentos sugeridos para esta cena. " .
                      "Foque em conflito, avanços de plot e revelações. Vá direto ao ponto, não explique suas escolhas.";

            $planningMarkdown = $gemini->generate($prompt, 'gemini-3.0-flash');

            return response()->json([
                'success' => true,
                'planning' => $planningMarkdown
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function writeScene(Request $request, $project_uuid)
    {
        try {
            $project = Project::where('uuid', $project_uuid)->firstOrFail();
            $gemini = new GeminiService($project);

            $context = $gemini->buildMasterContext();
            $sceneTitle = $request->input('title', 'Cena Sem Título');
            $planning = $request->input('planning', '');

            $prompt = "Você é um romancista premiado e excepcionalmente criativo, atuando como ghostwriter. " .
                      "Abaixo está o universo onde a história se passa:\n\n{$context}\n\n";

            if (!empty($planning)) {
                $prompt .= "O autor fez o seguinte planejamento de eventos para este capítulo ('{$sceneTitle}'):\n{$planning}\n\n";
            } else {
                $prompt .= "Escreva os parágrafos de inicialização de um capítulo intitulado '{$sceneTitle}'.\n\n";
            }

            $prompt .= "SUA TAREFA: Escreva o corpo literário da cena em estilo de prosa elegante. Evite clichês. " .
                       "Desenvolva diálogos e monólogos internos com maestria. Formate em Markdown livre (negritos adequados, itálicos e travessões para falas).\n" .
                       "Importante: NÃO crie introduções como 'Aqui está sua cena'. Devolva somente o texto literário puro para ser inserido diretamente no editor.";

            // Usa o Pro para qualidade literária densa
            $responseText = $gemini->generate($prompt, 'gemini-3.1-pro', [
                "Você é o fantasma literário, escreva apenas a arte.",
                "Não inclua notas, sumários ou avisos no final."
            ]);

            return response()->json([
                'success' => true,
                'content' => $responseText
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
