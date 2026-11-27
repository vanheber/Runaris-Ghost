<?php

namespace App\Console\Commands;

use App\Services\UpdateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Aplica o pacote que o Guardião baixou (storage/updates/pending.zip).
 *
 * É o equivalente PHP do trecho de update do start.bat, para o macOS/Linux
 * rodarem o mesmo fluxo. Roda no boot, com o servidor parado, e reverte
 * sozinho se a extração ou a migração falhar — storage/, .env e os bancos
 * SQLite nunca são tocados, nem pelo pacote nem pelo snapshot.
 *
 * ponytail: chame pelo start.sh / start.command, não no meio de uma sessão
 * aberta — trocar arquivos com o servidor no ar é o caminho do bug.
 */
class ApplyUpdateCommand extends Command
{
    protected $signature = 'app:apply-update';

    protected $description = 'Aplica a atualização pendente (storage/updates/pending.zip)';

    public function handle(UpdateService $updates): int
    {
        if (!$updates->hasPendingPackage()) {
            $this->line('Nenhuma atualização pendente.');

            return self::SUCCESS;
        }

        // Clone git atualiza por git pull; extrair o zip aqui sobrescreveria o repo.
        if (is_dir(base_path('.git'))) {
            $this->error('Instalação git: atualize com "git pull". O pacote não é aplicado aqui.');

            return self::FAILURE;
        }

        try {
            $updates->snapshotForUpdate();
        } catch (\Throwable $e) {
            Log::error('Update snapshot failed: ' . $e->getMessage());
            $this->error('Falha ao preparar o backup da versão atual: ' . $this->firstLine($e));

            return self::FAILURE;
        }

        // O migrate pode devolver código de erro OU estourar exceção (SQL inválido):
        // nos dois casos a instalação volta para o snapshot.
        try {
            $updates->applyPendingPackage();
            $migrated = $this->call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            Log::error('Update failed: ' . $e->getMessage());
            $this->restoreAndFail($updates, $this->firstLine($e));

            return self::FAILURE;
        }

        if ($migrated !== self::SUCCESS) {
            $this->restoreAndFail($updates, 'a migração falhou');

            return self::FAILURE;
        }

        $updates->discardPendingPackage();
        $this->callSilently('config:clear');
        $this->callSilently('view:clear');
        $this->callSilently('cache:clear');

        $this->info('Atualização aplicada.');

        return self::SUCCESS;
    }

    /**
     * Volta os arquivos para o snapshot e descarta o pacote. O .env, os bancos
     * e storage/ nunca saíram do lugar, então nada do autor é perdido.
     */
    protected function restoreAndFail(UpdateService $updates, string $reason): void
    {
        $this->error('Falha ao aplicar a atualização (' . $reason . '). Restaurando a versão anterior...');

        try {
            $updates->restorePreviousPackage();
        } catch (\Throwable $e) {
            Log::error('Update rollback failed: ' . $e->getMessage());
            $this->error('A restauração também falhou: ' . $this->firstLine($e));
            $this->line('  O snapshot está em storage/updates/previous — rode: php artisan app:rollback');

            return;
        }

        $updates->discardPendingPackage();
        $this->info('Versão anterior restaurada.');
    }

    /**
     * A mensagem de uma exceção de banco vem em várias linhas; a janela preta
     * do launcher só precisa da primeira.
     */
    protected function firstLine(\Throwable $e): string
    {
        return mb_substr(trim((string) strtok($e->getMessage(), "\n")), 0, 160);
    }
}
