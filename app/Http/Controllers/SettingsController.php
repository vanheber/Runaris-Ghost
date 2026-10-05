<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\BackupService;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /** Sentinel exibido no campo de token quando já existe um salvo. */
    protected const TOKEN_SENTINEL = '••••••••';

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
                $docContent = '<div class="alert alert-warning">' . __('Arquivo de documentação não encontrado (docs/USER_MANUAL.md).') . '</div>';
            }
        }

        $geminiApiKey = \App\Models\SystemSetting::getSetting('gemini_api_key');
        $geminiModelFlash = \App\Services\GeminiService::effectiveModel(\App\Services\GeminiService::FLASH);
        $geminiModelPro = \App\Services\GeminiService::effectiveModel(\App\Services\GeminiService::PRO);
        $flashModels = \App\Services\GeminiService::availableModels(\App\Services\GeminiService::FLASH);
        $proModels = \App\Services\GeminiService::availableModels(\App\Services\GeminiService::PRO);
        $currentVersion = $this->updateService->getCurrentVersion();
        $hasRollback = \App\Models\SystemSetting::getSetting('last_auto_backup') !== null;

        // Status do versionamento Git (apenas na aba Sistema)
        $gitStatus = $tab === 'system' ? app(\App\Services\GitVersioningService::class)->status() : null;
        $gitRemoteUrl = \App\Models\SystemSetting::getSetting('git_remote_url', '');
        $gitTokenValue = \App\Models\SystemSetting::getSetting('git_remote_token')
            ? self::TOKEN_SENTINEL : '';

        return view('settings.index', compact('tab', 'docContent', 'geminiApiKey', 'geminiModelFlash', 'geminiModelPro', 'flashModels', 'proModels', 'currentVersion', 'hasRollback', 'gitStatus', 'gitRemoteUrl', 'gitTokenValue'));
    }

    /**
     * Update AI settings.
     */
    public function updateAi(Request $request)
    {
        $flashIn = implode(',', array_keys(\App\Services\GeminiService::availableModels(\App\Services\GeminiService::FLASH)));
        $proIn = implode(',', array_keys(\App\Services\GeminiService::availableModels(\App\Services\GeminiService::PRO)));

        $request->validate([
            'gemini_api_key' => 'nullable|string',
            'gemini_model_flash' => 'required|string|in:' . $flashIn,
            'gemini_model_pro' => 'required|string|in:' . $proIn,
        ]);

        \App\Models\SystemSetting::setSetting('gemini_api_key', $request->input('gemini_api_key'));
        \App\Models\SystemSetting::setSetting('gemini_model_flash', $request->input('gemini_model_flash'));
        \App\Models\SystemSetting::setSetting('gemini_model_pro', $request->input('gemini_model_pro'));

        return redirect('/settings?tab=ai')->with('success', __('Configurações de IA salvas com sucesso.'));
    }

    /**
     * Salvar configuração do versionamento Git (chave + remoto).
     */
    public function storeGit(Request $request)
    {
        $request->validate([
            'git_remote_url' => 'nullable|string|max:500',
            'git_remote_token' => 'nullable|string|max:500',
        ]);

        $git = app(\App\Services\GitVersioningService::class);
        $url = trim((string) $request->input('git_remote_url'));

        if ($url !== '' && !preg_match('#^(https://\S+|git@\S+:\S+)$#', $url)) {
            return redirect('/settings?tab=system')->with('error', __('URL inválida. Use https://... ou git@host:caminho.'));
        }

        if ($request->boolean('git_versioning')) {
            if (!$git->detectGit()) {
                return redirect('/settings?tab=system')->with('error', __('Git não está instalado neste servidor.'));
            }
            if (!$git->ensureRepo()) {
                return redirect('/settings?tab=system')->with('error', __('Falha ao preparar o repositório local.'));
            }
        }

        \App\Models\SystemSetting::setSetting('git_versioning', $request->boolean('git_versioning') ? 'true' : 'false');
        \App\Models\SystemSetting::setSetting('git_remote_url', $url);

        // Sentinel intocado = token já salvo; vazio = limpa; outro valor = novo token.
        $token = $request->input('git_remote_token');
        if ($token !== self::TOKEN_SENTINEL) {
            \App\Models\SystemSetting::setSetting('git_remote_token', trim((string) $token));
        }

        return redirect('/settings?tab=system')->with('success', __('Configurações de versionamento salvas.'));
    }

    /**
     * Sincronização manual: commit pendente + push imediato.
     */
    public function pushGit()
    {
        $git = app(\App\Services\GitVersioningService::class);
        $git->commitIfDirty('manual: sincronização');
        $result = $git->push(true);

        return redirect('/settings?tab=system')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
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

        return redirect()->back()->with('error', __('Falha ao gerar o backup do sistema.'));
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
            return response()->json(['status' => false, 'message' => __('Erro ao limpar arquivos:') . ' ' . $e->getMessage()], 500);
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
                        unlink($file);
                    }
                }
            }

            return response()->json(['status' => true, 'next' => 'finalize']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Factory Reset - DB Cleanup Error: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('Erro ao limpar banco:') . ' ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the interface locale.
     */
    public function setLocale(Request $request)
    {
        $request->validate(['locale' => 'required|string|in:pt_BR,en,es']);
        \App\Models\SystemSetting::setSetting('system_locale', $request->locale);
        app()->setLocale($request->locale);
        return redirect('/settings?tab=general')->with('success', __('Idioma alterado.'));
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
                unlink($lockFile);
            }

            // Unlock .env for re-configuration
            $envWriter = new \App\Services\EnvWriter();
            $envWriter->unlockEnvFile();

            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return response()->json(['status' => true]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => __('Erro na finalização:') . ' ' . $e->getMessage()], 500);
        }
    }
}
