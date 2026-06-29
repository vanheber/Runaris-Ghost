<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\BackupService;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    protected $backupService;
    protected $updateService;

    public function __construct(BackupService $backupService, \App\Services\UpdateService $updateService)
    {
        $this->backupService = $backupService;
        $this->updateService = $updateService;
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
        $currentVersion = $this->updateService->getCurrentVersion();
        $hasRollback = \App\Models\SystemSetting::getSetting('last_auto_backup') !== null;

        return view('settings.index', compact('tab', 'docContent', 'geminiApiKey', 'currentVersion', 'hasRollback'));
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
     * Show the full reset progress page.
     */
    public function showResetProgress(Request $request)
    {
        // Require password confirmation if user has one
        if (\App\Models\SystemSetting::getSetting('use_local_password', 'true') === 'true') {
            $request->validate(['password' => 'required|current_password']);
        }

        return view('settings.reset_progress');
    }

    /**
     * Step 1: Clean Projects and Backup Files
     */
    public function stepCleanFiles()
    {
        try {
            auth()->logout();
            \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory('projects');
            \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory('backups');
            return response()->json(['status' => true, 'next' => 'database']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Erro ao limpar arquivos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Step 2: Reconstruct Database (Aggressive File Cleanup)
     */
    public function stepCleanDatabase()
    {
        try {
            \Illuminate\Support\Facades\DB::disconnect();

            $dbConnection = config('database.default');
            $dbPath = config("database.connections.{$dbConnection}.database");
            $dbDir = database_path();

            // targets all possible locations for the sqlite files
            $targets = array_unique([$dbPath, $dbDir . '/database.sqlite']);

            foreach ($targets as $target) {
                if (empty($target)) continue;
                
                $files = [$target, $target . '-wal', $target . '-shm'];
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        $relative = str_replace(storage_path('app/'), '', $file);
                        Storage::disk('local')->delete($relative);
                    }
                }
            }

            return response()->json(['status' => true, 'next' => 'finalize']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Factory Reset - DB Cleanup Error: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Erro ao limpar banco: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Step 3: Finalize Environment
     */
    public function stepFinalize()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');

            // Remove install lock so the wizard can run again
            $lockFile = storage_path('install.lock');
            if (file_exists($lockFile)) {
                Storage::disk('local')->delete('install.lock');
            }

            // Unlock .env for re-configuration
            $envWriter = new \App\Services\EnvWriter();
            $envWriter->unlockEnvFile();

            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Erro na finalização: ' . $e->getMessage()], 500);
        }
    }
}
