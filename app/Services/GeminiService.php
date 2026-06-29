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

    public static function forProject(string $projectUuid): self
    {
        $project = Project::where('uuid', $projectUuid)->firstOrFail();
        return new self($project);
    }

    /**
     * Send a generation request to the Gemini API.
     * 
     * @param string $prompt The user prompt instructions.
     * @param string $model (e.g. gemini-2.5-flash or gemini-2.5-pro)
     * @param array $systemInstruction Optional system instruction array (roleplay/context).
     * @return string Generative content string.
     */
    public function generate(string $prompt, string $model = 'gemini-2.5-flash', ?array $systemInstruction = null): string
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
        
        if ($this->project->bible_summary) {
            $context .= "Resumo Oficial dos Eventos:\n" . $this->project->bible_summary . "\n";
        }
        
        if ($this->project->bible_content) {
             $context .= "Regras de Lore e Mundo:\n" . $this->project->bible_content . "\n";
        }
        
        return $context;
    }

    /**
     * Suggest potential connections for a specific card.
     */
    public function suggestConnections(\App\Models\Card $card, string $cardContent, \Illuminate\Support\Collection $otherCards): array
    {
        $context = $this->buildMasterContext();
        
        $othersFormatted = $otherCards->map(fn($c) => "- [{$c->uuid}] {$c->title} ({$c->type})")->implode("\n");

        $prompt = "Analise a ficha abaixo e sugira conexões lógicas com os outros elementos listados.\n\n";
        $prompt .= "FICHA ATUAL:\nNome: {$card->title}\nTipo: {$card->type}\nConteúdo:\n{$cardContent}\n\n";
        $prompt .= "OUTRAS FICHAS DISPONÍVEIS:\n{$othersFormatted}\n\n";
        $prompt .= "REQUISITOS:\n";
        $prompt .= "1. Retorne APENAS um JSON puro.\n";
        $prompt .= "2. Formato: [{\"uuid\": \"uuid\", \"title\": \"Nome da Ficha\", \"type\": \"tipo\", \"reason\": \"Pequena explicação\", \"suggested_connection_name\": \"tipo-da-conexão\"}]\n";
        $prompt .= "3. Sugira apenas conexões que tenham evidência no texto ou na lógica da Bíblia.\n";
        $prompt .= "4. Se não encontrar nada, retorne [].";

        $system = [
            "Você é um arquiteto de worldbuilding especializado em análise de tramas e conexões entre personagens, locais e itens.",
            "Sua resposta deve ser estritamente em JSON."
        ];

        $response = $this->generate($prompt, 'gemini-2.5-flash', $system);
        
        // Clean markdown if present
        $json = preg_replace('/```json\n?|\n?```/', '', $response);
        
        return json_decode($json, true) ?? [];
    }
}
