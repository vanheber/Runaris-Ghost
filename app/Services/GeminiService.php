<?php

namespace App\Services;

use App\Models\Project;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Exception;

class GeminiService
{
    protected $apiKey;
    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->apiKey = SystemSetting::getSetting('gemini_api_key');
        
        if (empty($this->apiKey)) {
            throw new Exception("Google Gemini API Key não configurada. Vá para Configurações (rodape) > Inteligência Artificial.");
        }
    }

    /**
     * Send a generation request to the Gemini API.
     * 
     * @param string $prompt The user prompt instructions.
     * @param string $model (e.g. gemini-3.0-flash or gemini-3.1-pro)
     * @param array $systemInstruction Optional system instruction array (roleplay/context).
     * @return string Generative content string.
     */
    public function generate(string $prompt, string $model = 'gemini-3.0-flash', array $systemInstruction = null): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";
        
        $payload = [
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        // Se usar system_instruction
        if ($systemInstruction) {
            $payload["system_instruction"] = [
                "parts" => [
                    ["text" => implode("\n", $systemInstruction)]
                ]
            ];
        }

        $response = Http::post($url, $payload);

        if (!$response->successful()) {
            $error = $response->json();
            throw new Exception("Falha na API Gemini: " . ($error['error']['message'] ?? 'Erro desconhecido.'));
        }

        $result = $response->json();
        
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        }

        throw new Exception("Resposta inesperada da API Gemini.");
    }

    /**
     * Helper to build the Master Context from the Project.
     */
    public function buildMasterContext(): string
    {
        $context = "BÍBLIA E CONTEXTO DO PROJETO:\n";
        $context .= "Título: " . $this->project->name . "\n";
        $context .= "Descrição Original: " . $this->project->description . "\n";
        
        if ($this->project->bible_summary) {
            $context .= "Resumo Oficial dos Eventos:\n" . $this->project->bible_summary . "\n";
        }
        
        if ($this->project->bible_content) {
             $context .= "Regras de Lore e Mundo:\n" . $this->project->bible_content . "\n";
        }

        // Você também pode anexar lista de Fichas (Worldbuilding) 
        // ou deixar para injetar sob demanda nos Botões Mágicos específicos.
        
        return $context;
    }
}
