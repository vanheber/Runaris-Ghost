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
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);

            if (isset($data['tag_name'])) {
                $latestRaw = $data['tag_name'];
                // ponytail: a tag do release costuma vir "v1.5.0"; version_compare não entende o "v".
                $latest    = preg_replace('/^v/i', '', (string) $latestRaw) ?: $latestRaw;
                $skipped   = ltrim((string) \App\Models\SystemSetting::getSetting('skipped_update_version'), 'vV');
                $isNewer   = version_compare($latest, $current, '>');

                // URL do pacote Windows (asset do release).
                $downloadUrl = null;
                foreach (($data['assets'] ?? []) as $asset) {
                    if (($asset['name'] ?? '') === 'runaris-ghost-win.zip') {
                        $downloadUrl = $asset['browser_download_url'] ?? null;
                        break;
                    }
                }

                return [
                    // "Manter versão atual" grava skipped_update_version e para de avisar aquela versão.
                    'has_update'      => $isNewer && $latest !== $skipped,
                    'latest_version'  => $latestRaw,
                    'current_version' => $current,
                    'notes'           => $data['body'] ?? '',
                    'download_url'    => $downloadUrl,
                    'skipped'         => $latest === $skipped,
                    'is_packaged'     => $this->isPackagedInstall(),
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

        if ($this->isGitRepository()) {
            return $this->runGitUpdate();
        }

        if ($this->isPackagedInstall()) {
            return $this->stageZipUpdate();
        }

        throw new \Exception("Ambiente não configurado para atualizações.");
    }

    /**
     * O usuário optou por manter a versão atual: não avisar mais desta versão.
     */
    public function keepCurrent(): bool
    {
        $check = $this->checkUpdate();
        $latestRaw = $check['latest_version'] ?? null;
        if (!$latestRaw) {
            return false;
        }
        $latest  = preg_replace('/^v/i', '', $latestRaw);
        $current = $this->getCurrentVersion();

        if (version_compare($latest, $current, '>')) {
            \App\Models\SystemSetting::setSetting('skipped_update_version', $latestRaw);
            return true;
        }

        return false;
    }

    /**
     * Instalação empacotada (Windows + runtime/): update = baixar runaris-ghost-win.zip
     * e deixar para o start.bat aplicar no próximo boot (Windows trava arquivos em uso).
     */
    protected function stageZipUpdate(): bool
    {
        $check = $this->checkUpdate();
        if (empty($check['download_url'])) {
            throw new \Exception("Nenhum pacote de atualização disponível para download.");
        }

        $dir = storage_path('updates');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $zipPath = $dir . '/pending.zip';
        $fp = fopen($zipPath, 'w');
        $ch = curl_init($check['download_url']);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Runaris-Ghost-Updater");
        curl_setopt($ch, CURLOPT_TIMEOUT, 900);
        $ok = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (!$ok || $code !== 200 || filesize($zipPath) < 1024) {
            @unlink($zipPath);
            throw new \Exception("Falha ao baixar o pacote de atualização.");
        }

        file_put_contents($dir . '/pending.json', json_encode([
            'version'   => $check['latest_version'],
            'staged_at' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT));

        Log::info("Update staged: " . $check['latest_version']);
        return true;
    }

    protected function runGitUpdate(): bool
    {
        Log::info("Starting git update...");

        // 1. Create a safety backup
        $backupPath = $this->backupService->createSnapshot();
        if (!$backupPath) {
            throw new \Exception("Falha ao criar backup de segurança antes da atualização.");
        }

        // Store this specific backup as the rollback target
        \App\Models\SystemSetting::setSetting('last_auto_backup', basename($backupPath));

        // 2. Perform update (git)
        $output = [];
        $resultCode = 0;
        exec("git pull origin main 2>&1", $output, $resultCode);

        if ($resultCode !== 0) {
            Log::error("Git pull failed: " . implode("\n", $output));
            throw new \Exception("Erro ao sincronizar com o repositório (Git).");
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

    /**
     * Instalação empacotada (Windows): existe a pasta runtime/ com o FrankenPHP.
     */
    public function isPackagedInstall(): bool
    {
        return is_dir(base_path('runtime'));
    }
}
