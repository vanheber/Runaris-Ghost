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

                // URL do pacote certo para este SO/arquitetura (asset do release).
                $downloadUrl = null;
                $assetName = $this->packageAssetName();
                foreach (($data['assets'] ?? []) as $asset) {
                    if ($assetName !== null && ($asset['name'] ?? '') === $assetName) {
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
     * Instalação empacotada (runtime/ do SO atual): update = baixar o zip do
     * release e deixar para o launcher aplicar no próximo boot — no Windows o
     * start.bat (arquivos em uso), no macOS/Linux o próprio start.sh via
     * `php artisan app:apply-update`.
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

    /**
     * Há pacote baixado esperando aplicação? (storage/updates/pending.zip)
     */
    public function hasPendingPackage(): bool
    {
        return is_file(storage_path('updates/pending.zip'));
    }

    /**
     * Há snapshot da versão anterior? (storage/updates/previous)
     */
    public function hasPreviousPackage(): bool
    {
        return is_dir(storage_path('updates/previous'));
    }

    /**
     * Guarda a instalação atual em storage/updates/previous antes de sobrescrever.
     * Mesmo layout do start.bat: storage/, .env e os bancos SQLite ficam de fora,
     * então rollback.bat e app:rollback restauram o snapshot um do outro.
     *
     * ponytail: runtime/ também fica fora — é um binário fixo, e copiar ~170 MB
     * a cada update só para poder voltar à mesma versão não paga. Se um dia o
     * runtime mudar, o pacote novo o instala normalmente.
     */
    public function snapshotForUpdate(): void
    {
        $previous = storage_path('updates/previous');
        $this->deleteTree($previous);
        $this->copyTree(base_path(), $previous, ['runtime']);
    }

    /**
     * Instala o pacote pendente: extrai para um staging e move por rename().
     *
     * Extrair direto sobre a instalação falha no Linux: o binário do runtime em
     * execução não pode ser aberto para escrita ("Text file busy", ETXTBSY).
     * rename() troca a entrada do diretório sem tocar no inode em uso — é o que
     * deixou o update e o rollback funcionarem com o runtime embutido.
     *
     * ponytail: no Windows renomear arquivo em uso pode ser recusado; lá o
     * start.bat continua sendo o caminho (aplica com o servidor parado).
     */
    public function applyPendingPackage(): void
    {
        $zipPath = storage_path('updates/pending.zip');

        if (!is_file($zipPath)) {
            throw new \RuntimeException('Nenhum pacote pendente em storage/updates/pending.zip.');
        }

        $staging = storage_path('updates/staging');
        $this->deleteTree($staging);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Pacote de atualização ilegível (zip corrompido?).');
        }

        if (!$zip->extractTo($staging)) {
            $zip->close();

            throw new \RuntimeException('Falha ao extrair o pacote (disco cheio?).');
        }

        $zip->close();

        $this->moveIntoPlace($staging);
        $this->deleteTree($staging);
        $this->ensureExecutables();
    }

    /**
     * Restaura os arquivos do snapshot sobre a instalação. Não apaga arquivos
     * que só existem na versão nova (mesmo comportamento do rollback.bat),
     * exceto migrações: um update que falhou no meio deixa o arquivo .php para
     * trás e o migrate seguinte tentaria rodá-lo de novo, falhando de novo.
     */
    public function restorePreviousPackage(): void
    {
        $snapshot = storage_path('updates/previous');

        if (!is_dir($snapshot)) {
            throw new \RuntimeException('Nenhuma versão anterior em storage/updates/previous.');
        }

        $staging = storage_path('updates/staging');
        $this->deleteTree($staging);
        $this->copyTree($snapshot, $staging);
        $this->moveIntoPlace($staging);
        $this->deleteTree($staging);
        $this->ensureExecutables();

        $this->purgeMigrationsMissingFrom($snapshot);
    }

    /**
     * Apaga migrações que não existiam na versão anterior. Só age quando o
     * snapshot tem a pasta database/migrations — sem ela, apagar seria destruir
     * o histórico do próprio app.
     */
    protected function purgeMigrationsMissingFrom(string $snapshot): void
    {
        $snapshotDir = $snapshot . '/database/migrations';
        if (!is_dir($snapshotDir)) {
            return;
        }

        $dir = database_path('migrations');
        if (!is_dir($dir)) {
            return;
        }

        foreach (glob($dir . '/*.php') ?: [] as $file) {
            if (!is_file($snapshotDir . '/' . basename($file))) {
                @unlink($file);
            }
        }
    }

    /**
     * Descarta o pacote aplicado (ou o que falhou) e o marcador da versão.
     */
    public function discardPendingPackage(): void
    {
        @unlink(storage_path('updates/pending.zip'));
        @unlink(storage_path('updates/pending.json'));
    }

    /**
     * Garante o bit de execução nos launchers e no runtime.
     *
     * ZipArchive não restaura modo unix (extrai 0644) e copy() também não:
     * sem isso o update deixaria `runtime/frankenphp` não executável e o app
     * não voltaria a subir — e o `start.sh` perderia o +x para a próxima vez.
     * No Windows chmod não muda nada importante (usa start.bat).
     */
    protected function ensureExecutables(): void
    {
        foreach (['start.sh', 'start.command', 'start-mcp.sh', 'runtime/frankenphp'] as $relative) {
            $path = base_path($relative);

            if (is_file($path)) {
                @chmod($path, 0755);
            }
        }
    }

    /**
     * Move a árvore de staging para a instalação, arquivo por arquivo.
     *
     * rename() primeiro: substitui o arquivo sem abrir o inode em uso, que é o
     * que permite trocar o binário do runtime durante o update. copy() fica só
     * como fallback (rename pode falhar entre sistemas de arquivos diferentes).
     */
    protected function moveIntoPlace(string $staging): void
    {
        $staging = rtrim($staging, '/\\');

        if (!is_dir($staging)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($staging, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            $relative = substr($item->getPathname(), strlen($staging) + 1);

            if ($this->mustSkip($relative)) {
                continue;
            }

            $target = base_path($relative);

            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0775, true);
                }
                continue;
            }

            $parent = dirname($target);
            if (!is_dir($parent)) {
                mkdir($parent, 0775, true);
            }

            if (@rename($item->getPathname(), $target)) {
                continue;
            }

            if (!copy($item->getPathname(), $target)) {
                throw new \RuntimeException('Falha ao instalar ' . $relative);
            }

            @unlink($item->getPathname());
        }
    }

    /**
     * Cópia recursiva preservando o layout, sem descer em storage/, .git e
     * node_modules e sem copiar .env nem bancos SQLite. Só o que o pacote pode
     * sobrescrever entra no snapshot — os dados do autor nunca são tocados.
     */
    protected function copyTree(string $from, string $to, array $extraSkip = []): void
    {
        $from = rtrim($from, '/\\');

        if (!is_dir($from)) {
            return;
        }

        if (!is_dir($to) && !mkdir($to, 0775, true) && !is_dir($to)) {
            throw new \RuntimeException('Não foi possível criar ' . $to);
        }

        $filter = new \RecursiveCallbackFilterIterator(
            new \RecursiveDirectoryIterator($from, \FilesystemIterator::SKIP_DOTS),
            function (\SplFileInfo $file) use ($from, $extraSkip): bool {
                $relative = substr($file->getPathname(), strlen($from) + 1);

                return !$this->mustSkip($relative, $extraSkip);
            }
        );

        $items = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($items as $item) {
            $relative = substr($item->getPathname(), strlen($from) + 1);
            $target = $to . DIRECTORY_SEPARATOR . $relative;

            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0775, true);
                }
                continue;
            }

            $parent = dirname($target);
            if (!is_dir($parent)) {
                mkdir($parent, 0775, true);
            }

            if (!copy($item->getPathname(), $target)) {
                throw new \RuntimeException('Falha ao copiar ' . $relative);
            }
        }
    }

    /**
     * O que nunca entra (nem volta) por um pacote de atualização.
     */
    protected function mustSkip(string $relative, array $extraTop = []): bool
    {
        $relative = str_replace('\\', '/', $relative);
        $top = explode('/', $relative)[0];

        if (in_array($top, array_merge(['storage', '.git', 'node_modules'], $extraTop), true)) {
            return true;
        }

        $name = basename($relative);

        return $name === '.env'
            || $name === '.DS_Store'
            || (bool) preg_match('/\.sqlite(-wal|-shm|-journal)?$/', $name);
    }

    /**
     * Remove uma árvore inteira (usado para refazer o snapshot a cada update).
     */
    protected function deleteTree(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($dir);
    }

    protected function isGitRepository()
    {
        return is_dir(base_path('.git'));
    }

    /**
     * Instalação empacotada: o runtime embutido deste SO está presente.
     */
    public function isPackagedInstall(): bool
    {
        return $this->runtimeBinary() !== null;
    }

    /**
     * Marcador do runtime empacotado para o SO atual (null = instalação por
     * código-fonte). Evita que um zip de outro SO, extraído na pasta errada,
     * seja tratado como "pacote" e atualizado com o binário errado.
     */
    protected function runtimeBinary(): ?string
    {
        $relative = PHP_OS_FAMILY === 'Windows' ? 'runtime/php.exe' : 'runtime/frankenphp';

        return is_file(base_path($relative)) ? base_path($relative) : null;
    }

    /**
     * Nome do asset do release para o SO/arquitetura atual.
     *
     * Mac Intel fica fora: o FrankenPHP parou de publicar darwin-x86_64 na
     * v1.14 e o macOS 27 já não roda em Intel — instalação por código-fonte
     * continua funcionando em qualquer arquitetura.
     */
    protected function packageAssetName(): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 'runaris-ghost-win.zip';
        }

        $arch = strtolower((string) php_uname('m'));
        $arm = in_array($arch, ['arm64', 'aarch64'], true);

        if (PHP_OS_FAMILY === 'Darwin') {
            return $arm ? 'runaris-ghost-mac-arm64.zip' : null;
        }

        return $arm ? 'runaris-ghost-linux-arm64.zip' : 'runaris-ghost-linux-x64.zip';
    }
}
