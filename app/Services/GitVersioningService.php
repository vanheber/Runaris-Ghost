<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Versionamento Git opcional dos projetos (storage/app/private/projects).
 *
 * - Repositório único no diretório de projetos; reutiliza um repositório
 *   ancestral (ex.: Runaris-Vault) quando existir, exceto o do app.
 * - Commit automático pós-mutação via ProjectContextMiddleware::terminate().
 * - Push somente (sem pull: SQLite é binário, merge seria catastrófico).
 */
class GitVersioningService
{
    /** Intervalo mínimo em segundos entre pushes automáticos. */
    protected const PUSH_INTERVAL = 300;

    protected string $root;
    protected ?bool $gitAvailable = null;

    public function __construct(?string $root = null)
    {
        $this->root = $root ?? storage_path('app/private/projects');
    }

    public function isEnabled(): bool
    {
        return SystemSetting::getSetting('git_versioning') === 'true';
    }

    /** Detecta o binário git (memoizado; sem cwd — não depende da raiz existir). */
    public function detectGit(): bool
    {
        if ($this->gitAvailable === null) {
            $process = new Process(['git', '--version']);
            $process->setTimeout(10);
            try {
                $process->run();
                $this->gitAvailable = $process->getExitCode() === 0;
            } catch (\Throwable $e) {
                $this->gitAvailable = false;
            }
        }

        return $this->gitAvailable;
    }

    /**
     * Garante um repositório utilizável na raiz dos projetos.
     * Reutiliza repositório ancestral (exceto o do app) ou cria um novo.
     */
    public function ensureRepo(): bool
    {
        if (!is_dir($this->root) && !@mkdir($this->root, 0755, true)) {
            return false;
        }

        if (is_dir($this->root . '/.git')) {
            return true;
        }

        $toplevel = $this->ancestorToplevel();
        if ($toplevel !== null && $toplevel !== $this->basePathReal()) {
            return true; // vault-style: repositório ancestral já cobre a raiz
        }

        if ($this->run(['git', 'init'])[0] !== 0) {
            return false;
        }

        $this->run(['git', 'config', 'user.name', 'Runaris Ghost']);
        $this->run(['git', 'config', 'user.email', 'runaris@local']);

        // ponytail: .gitignore escrito só na criação; repo reaproveitado mantém o próprio
        $ignore = $this->root . '/.gitignore';
        if (!file_exists($ignore)) {
            file_put_contents($ignore, "snapshots/\nexports/\n*.zip\n.DS_Store\nThumbs.db\n");
        }

        return true;
    }

    /** Faz commit apenas se houver alterações pendentes. */
    public function commitIfDirty(string $message): bool
    {
        if (!$this->isEnabled() || !$this->ensureRepo()) {
            return false;
        }

        [, $status] = $this->run(['git', 'status', '--porcelain', '--', '.']);
        if (trim($status) === '') {
            return false;
        }

        if ($this->run(['git', 'add', '-A', '--', '.'])[0] !== 0) {
            Log::warning('Git add falhou; commit automático ignorado.');
            return false;
        }

        [$code, $out] = $this->run(['git', 'commit', '-m', $message]);
        if ($code !== 0) {
            Log::warning('Git commit falhou: ' . trim($out));
            return false;
        }

        return true;
    }

    /** Caminho pós-resposta do middleware: commit + push com throttle. */
    public function autoVersion(string $message): void
    {
        if (!$this->isEnabled() || !$this->detectGit()) {
            return;
        }

        try {
            if ($this->commitIfDirty($message)) {
                $this->push();
            }
        } catch (\Throwable $e) {
            Log::warning('Auto-versionamento Git falhou: ' . $e->getMessage());
        }
    }

    /**
     * Push para o remoto configurado (push-only, sem pull).
     * $force=true pula o throttle (botão manual).
     */
    public function push(bool $force = false): array
    {
        $url = SystemSetting::getSetting('git_remote_url');
        if (!$url) {
            return ['ok' => false, 'message' => __('Nenhum remoto configurado.')];
        }

        if (!$this->detectGit() || !$this->ensureRepo()) {
            return ['ok' => false, 'message' => __('Git indisponível ou repositório inválido.')];
        }

        $lastPush = (int) SystemSetting::getSetting('git_last_push_at', 0);
        if (!$force && $lastPush > 0 && (time() - $lastPush) < self::PUSH_INTERVAL) {
            return ['ok' => true, 'message' => __('Push adiado pelo throttle.')];
        }

        if ($this->run(['git', 'rev-parse', '--verify', 'HEAD'])[0] !== 0) {
            return ['ok' => false, 'message' => __('Nenhum commit para sincronizar.')];
        }

        [$code, $branch] = $this->run(['git', 'symbolic-ref', '--short', 'HEAD']);
        if ($code !== 0 || trim($branch) === '') {
            return ['ok' => false, 'message' => __('Não foi possível determinar a branch atual.')];
        }

        // Token nunca gravado no config do git: injetado só neste comando.
        $remote = $url;
        $token = SystemSetting::getSetting('git_remote_token');
        if ($token && str_starts_with($url, 'https://')) {
            $remote = 'https://' . $token . '@' . substr($url, 8);
        }

        [$code, $out] = $this->run(['git', 'push', $remote, 'HEAD:' . trim($branch)]);

        if ($code === 0) {
            $message = __('Sincronizado com o remoto (branch :branch).', ['branch' => trim($branch)]);
            SystemSetting::setSetting('git_last_push_at', (string) time());
        } else {
            $detail = trim($out) !== '' ? trim($out) : __('timeout ou falha ao executar o git');
            $message = __('Falha no push: :out', ['out' => $this->scrub($detail)]);
            Log::warning('Git push falhou: ' . $message);
        }

        SystemSetting::setSetting('git_last_push_status', $message);

        return ['ok' => $code === 0, 'message' => $message];
    }

    /** Estado para a UI (somente leitura: nada é criado). */
    public function status(): array
    {
        $gitAvailable = $this->detectGit();
        $repo = false;
        $dirty = null;
        $lastCommit = null;

        if ($gitAvailable && is_dir($this->root) && $this->hasRepo()) {
            $repo = true;
            [, $s] = $this->run(['git', 'status', '--porcelain', '--', '.']);
            $dirty = count(array_filter(array_map('trim', explode("\n", trim($s)))));
            [$c, $log] = $this->run(['git', 'log', '-1', '--format=%s|%cr']);
            if ($c === 0 && trim($log) !== '') {
                [$subject, $when] = array_pad(explode('|', trim($log), 2), 2, '');
                $lastCommit = ['subject' => $subject, 'when' => $when];
            }
        }

        return [
            'git_available' => $gitAvailable,
            'enabled' => $this->isEnabled(),
            'repo' => $repo,
            'remote' => (bool) SystemSetting::getSetting('git_remote_url'),
            'dirty' => $dirty,
            'last_commit' => $lastCommit,
            'last_push_status' => SystemSetting::getSetting('git_last_push_status'),
        ];
    }

    /** Toplevel do repositório que cobre a raiz, se for diferente do app. */
    protected function ancestorToplevel(): ?string
    {
        if (!is_dir($this->root)) {
            return null;
        }

        [$code, $out] = $this->run(['git', 'rev-parse', '--show-toplevel']);
        if ($code !== 0 || trim($out) === '') {
            return null;
        }

        return rtrim(trim($out), '/');
    }

    /** Existe um repositório utilizável (próprio ou ancestral, nunca o do app). */
    protected function hasRepo(): bool
    {
        if (is_dir($this->root . '/.git')) {
            return true;
        }

        $toplevel = $this->ancestorToplevel();

        return $toplevel !== null && $toplevel !== $this->basePathReal();
    }

    protected function basePathReal(): ?string
    {
        return realpath(base_path());
    }

    /** Remove o token de qualquer texto gravado/logado. */
    protected function scrub(string $text): string
    {
        $token = SystemSetting::getSetting('git_remote_token');
        if ($token) {
            $text = str_replace($token, '***', $text);
        }

        return $text;
    }

    /**
     * Roda um comando git com cwd na raiz dos projetos.
     * Retorna [exitCode, output]. Falhas viram [1, ''] — nunca exceção.
     */
    protected function run(array $cmd): array
    {
        if (!is_dir($this->root)) {
            return [1, ''];
        }

        $process = new Process($cmd, $this->root);
        $process->setTimeout(120);

        try {
            $process->run();
        } catch (\Throwable $e) {
            Log::warning('Falha ao executar git: ' . $e->getMessage());
            return [1, ''];
        }

        return [$process->getExitCode() ?? 1, $process->getOutput() . $process->getErrorOutput()];
    }
}
