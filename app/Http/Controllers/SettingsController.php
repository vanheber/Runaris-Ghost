<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\BackupService;

class SettingsController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }
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

    /**
     * Create a full ZIP backup of all projects and the system database.
     */
    public function fullBackup()
    {
        $backupPath = $this->backupService->createSnapshot();
        
        if ($backupPath && file_exists($backupPath)) {
            return response()->download($backupPath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Falha ao gerar o backup do sistema.');
    }

    /**
     * Display the EULA.
     */
    public function showEula()
    {
        $docPath = base_path('docs/EULA.md');
        $content = "Licença não encontrada.";
        
        if (file_exists($docPath)) {
            $parsedown = new \Parsedown();
            $content = $parsedown->text(file_get_contents($docPath));
        }

        return view('settings.eula', compact('content'));
    }

    /**
     * Factory reset the application (Wipe out all data).
     */
    public function factoryReset(Request $request)
    {
        // Require password confirmation if user has one
        if (\App\Models\SystemSetting::getSetting('use_local_password', 'true') === 'true') {
            $request->validate(['password' => 'required|current_password']);
        }

        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
        
        // Clear all projects in storage
        \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory('projects');
        \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory('backups');

        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/setup')->with('success', 'Santuário resetado com sucesso.');
    }
}
