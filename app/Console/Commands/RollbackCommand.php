<?php

namespace App\Console\Commands;

use App\Services\UpdateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Restaura a versão anterior guardada pela última atualização.
 *
 * Mesmo fluxo do rollback.bat: o snapshot fica em storage/updates/previous e
 * nunca contém .env, bancos SQLite nem storage/ — os dados do autor ficam.
 * Rode com o servidor fechado (o start.sh --rollback faz isso na ordem certa).
 */
class RollbackCommand extends Command
{
    protected $signature = 'app:rollback';

    protected $description = 'Restaura a versão anterior guardada pela última atualização';

    public function handle(UpdateService $updates): int
    {
        if (!$updates->hasPreviousPackage()) {
            $this->error('Nenhuma versão anterior encontrada. Nada a reverter.');

            return self::FAILURE;
        }

        try {
            $updates->restorePreviousPackage();
            $migrated = $this->call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            Log::error('Rollback failed: ' . $e->getMessage());
            $this->error('Falha ao restaurar a versão anterior: ' . mb_substr(trim((string) strtok($e->getMessage(), "\n")), 0, 160));

            return self::FAILURE;
        }

        if ($migrated !== self::SUCCESS) {
            $this->error('Arquivos restaurados, mas a migração falhou. Confira o banco.');

            return self::FAILURE;
        }

        $this->callSilently('config:clear');
        $this->callSilently('view:clear');
        $this->callSilently('cache:clear');

        $this->info('Versão anterior restaurada.');

        return self::SUCCESS;
    }
}
