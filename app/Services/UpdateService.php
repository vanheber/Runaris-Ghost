<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Services\BackupService;

class UpdateService
{
    protected $backupService;
    protected $versionFile;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
        $this->versionFile = base_path('version');
    }

    /**
     * Get current system version.
     */
    public function getCurrentVersion()
    {
        if (file_exists($this->versionFile)) {
            return trim(file_get_contents($this->versionFile));
        }
        return 'v1.0.0';
    }

    /**
     * Check for updates on GitHub.
     */
    public function checkUpdate()
    {
        try {
            $current = $this->getCurrentVersion();
            
            // Simple check against GitHub API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/vanheber/Runaris-Ghost/releases/latest");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, "Runaris-Ghost-Updater");
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            
            if (isset($data['tag_name'])) {
                $latest = $data['tag_name'];
                return [
                    'has_update' => version_compare($latest, $current, '>'),
                    'latest_version' => $latest,
                    'current_version' => $current,
                    'notes' => $data['body'] ?? ''
                ];
            }
        } catch (\Exception $e) {
            Log::error("Update check failed: " . $e->getMessage());
        }

        return ['has_update' => false, 'current_version' => $this->getCurrentVersion()];
    }

    /**
     * Run the update process.
     */
    public function runUpdate()
    {
        Log::info("Starting automatic update...");

        // 1. Create a safety backup
        $backupPath = $this->backupService->createSnapshot();
        if (!$backupPath) {
            throw new \Exception("Falha ao criar backup de segurança antes da atualização.");
        }

        // Store this specific backup as the rollback target
        \App\Models\SystemSetting::setSetting('last_auto_backup', basename($backupPath));

        // 2. Perform update (Priority: Git)
        if ($this->isGitRepository()) {
            $output = [];
            $resultCode = 0;
            exec("git pull origin main 2>&1", $output, $resultCode);
            
            if ($resultCode !== 0) {
                Log::error("Git pull failed: " . implode("\n", $output));
                throw new \Exception("Erro ao sincronizar com o repositório (Git).");
            }
        } else {
            // Fallback for non-git installs would go here (e.g., download ZIP)
            throw new \Exception("Ambiente não configurado para atualizações via Git.");
        }

        // 3. Post-update maintenance
        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            Log::warning("Post-update commands failed, but files were updated: " . $e->getMessage());
        }

        Log::info("Update completed successfully.");
        return true;
    }

    /**
     * Rollback to the backup taken before the last update.
     */
    public function rollback()
    {
        $lastBackup = \App\Models\SystemSetting::getSetting('last_auto_backup');
        if (!$lastBackup) {
            throw new \Exception("Nenhum backup de atualização encontrado para restaurar.");
        }

        $success = $this->backupService->rollback($lastBackup);
        if ($success) {
            \App\Models\SystemSetting::setSetting('last_auto_backup', null);
            return true;
        }

        return false;
    }

    protected function isGitRepository()
    {
        return is_dir(base_path('.git'));
    }
}
