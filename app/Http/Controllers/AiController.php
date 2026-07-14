<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ManuscriptItem;
use App\Services\GeminiService;
use App\Services\LiteraryCraft;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function suggestCard(Request $request, $project_uuid)
    {
        try {
            $gemini = GeminiService::forProject($project_uuid);

            $context = $gemini->buildMasterContext();
            
            $prompt = "Você é um assistente de Worldbuilding criativo. Avalie o contexto do mundo atual:\n\n{$context}\n\n" .
                      "SUA TAREFA: Sugira UMA nova ficha para este universo. Pode ser um personagem intrigante não detalhado antes, um local inexplorado ou um objeto essencial para o plot.\n" .
                      "Responda EXATAMENTE neste formato JSON puro (sem markup markdown markdown tags ```json), para que possa ser inserido no banco:\n" .
                      '{"title": "Nome sugerido", "type": "character", "content": "Descrição rica em detalhes, motivações e importância para o cenário."}' . "\n\n" .
                      "Nota: 'type' deve ser obrigatoriamente um destes: character, scenario, object.";

            $response = $gemini->generate($prompt, 'gemini-2.5-flash');
            
            // Clean markdown code blocks if the model insists
            $jsonStr = trim(preg_replace('/^```json\s*|\s*```$/i', '', $response));
            
            $data = json_decode($jsonStr, true);
            if (!$data) {
                // Tenta extrair qualquer formato embutido
                throw new \Exception(__("A resposta não veio em formato JSON suportado."));
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
            $gemini = GeminiService::forProject($project_uuid);

            $context = $gemini->buildMasterContext();
            $sceneTitle = $request->input('title', __('Cena Sem Título'));

            $prompt = "Você é um mestre da narração e estruturação dramática. Baseie-se na Bíblia deste projeto:\n\n{$context}\n\n" .
                      "O autor quer planejar uma nova cena/capítulo com o título '{$sceneTitle}'.\n" .
                      "SUA TAREFA: Escreva 5 a 7 bullet points (em Markdown) traçando os acontecimentos sugeridos para esta cena. " .
                      "Foque em conflito, avanços de plot e revelações. Vá direto ao ponto, não explique suas escolhas.";

            $planningMarkdown = $gemini->generate($prompt, 'gemini-2.5-flash', LiteraryCraft::instructions());

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
            $gemini = GeminiService::forProject($project_uuid);

            $context = $gemini->buildMasterContext();
            $sceneTitle = $request->input('title', __('Cena Sem Título'));
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
            $responseText = $gemini->generate($prompt, 'gemini-2.5-pro', [
                "Você é o fantasma literário, escreva apenas a arte.",
                "Não inclua notas, sumários ou avisos no final.",
                ...LiteraryCraft::instructions()
            ]);

            return response()->json([
                'success' => true,
                'content' => $responseText
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function reviewScene(Request $request, $project_uuid)
    {
        try {
            $gemini = GeminiService::forProject($project_uuid);

            $content = $request->input('content', '');
            $fixGrammar = $request->boolean('fix_grammar', true);
            $removeSlop = $request->boolean('remove_slop', true);

            if (!$fixGrammar && !$removeSlop) {
                return response()->json([
                    'success' => true,
                    'content' => $content
                ]);
            }

            $systemInstructions = [];
            $tasks = [];

            if ($fixGrammar) {
                $systemInstructions[] = "Você é um revisor de texto especializado em normas culta do português do Brasil.";
                $tasks[] = "Corrija erros de ortografia, concordância, regência, crase, pontuação e gramática do português brasileiro.";
            }

            if ($removeSlop) {
                $systemInstructions = array_merge($systemInstructions, LiteraryCraft::instructions());
                $tasks[] = "Remova padrões de AI Slop conforme as regras do sistema.";
            } else {
                $tasks[] = "Preserve integralmente as escolhas estilísticas do autor. NÃO altere metáforas, estruturas ou dicção — apenas corrija erros gramaticais objetivos.";
            }

            $prompt = "Revise o texto literário abaixo.\n\n"
                    . "TAREFAS:\n"
                    . implode("\n", array_map(fn($t) => "- {$t}", $tasks)) . "\n\n"
                    . "REGRAS:\n"
                    . "- Preserve o estilo e a voz do autor.\n"
                    . "- Não adicione nem remova conteúdo narrativo.\n"
                    . "- Não inclua explicações, notas ou comentários.\n"
                    . "- Devolva APENAS o texto revisado, sem formatação adicional.\n\n"
                    . "--- TEXTO PARA REVISÃO ---\n{$content}\n--- FIM DO TEXTO ---";

            $reviewed = $gemini->generate($prompt, 'gemini-2.5-flash', $systemInstructions);

            return response()->json([
                'success' => true,
                'content' => $reviewed
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
