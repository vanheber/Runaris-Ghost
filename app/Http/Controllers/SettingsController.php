<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display the global system settings or documentation.
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'general');
        $docContent = null;

        if ($tab === 'docs') {
            $docPath = base_path('docs/USER_MANUAL.md');
            if (file_exists($docPath)) {
                $content = file_get_contents($docPath);
                $parsedown = new \Parsedown();
                $docContent = $parsedown->text($content);
            } else {
                $docContent = '<div class="alert alert-warning">Arquivo de documentação não encontrado (docs/USER_MANUAL.md).</div>';
            }
        }

        $geminiApiKey = \App\Models\SystemSetting::getSetting('gemini_api_key');

        return view('settings.index', compact('tab', 'docContent', 'geminiApiKey'));
    }

    /**
     * Update AI settings.
     */
    public function updateAi(Request $request)
    {
        $request->validate([
            'gemini_api_key' => 'nullable|string'
        ]);

        \App\Models\SystemSetting::setSetting('gemini_api_key', $request->input('gemini_api_key'));

        return redirect('/settings?tab=ai')->with('success', 'Configurações de IA salvas com sucesso.');
    }
}
