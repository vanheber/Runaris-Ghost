<?php

namespace App\Services;

class EnvWriter
{
    protected string $envPath;

    public function __construct()
    {
        $this->envPath = base_path('.env');
    }

    /**
     * Set one or more key-value pairs in the .env file.
     * Creates the file from .env.example if it doesn't exist.
     */
    public function set(array $values): bool
    {
        $this->ensureEnvExists();

        $content = file_get_contents($this->envPath);

        foreach ($values as $key => $value) {
            $content = $this->replaceOrAppend($content, $key, $value);
        }

        return file_put_contents($this->envPath, $content) !== false;
    }

    /**
     * Replace an existing key or append it at the end.
     */
    protected function replaceOrAppend(string $content, string $key, string $value): string
    {
        // Wrap value in quotes if it contains spaces or special characters
        $escapedValue = $this->escapeValue($value);

        // Try to replace existing key (handles commented-out lines too)
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

        if (preg_match($pattern, $content)) {
            return preg_replace($pattern, "{$key}={$escapedValue}", $content);
        }

        // Also try to uncomment and set a commented-out key
        $commentedPattern = '/^#\s*' . preg_quote($key, '/') . '=.*$/m';
        if (preg_match($commentedPattern, $content)) {
            return preg_replace($commentedPattern, "{$key}={$escapedValue}", $content);
        }

        // Append at the end
        return rtrim($content, "\n") . "\n{$key}={$escapedValue}\n";
    }

    /**
     * Escape a value for safe .env storage.
     */
    protected function escapeValue(string $value): string
    {
        if ($value === '') {
            return '""';
        }

        // If it contains spaces, quotes, or special chars, wrap in double quotes
        if (preg_match('/[\s#"\'\\\\]/', $value) || str_contains($value, '${')) {
            $value = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
            return '"' . $value . '"';
        }

        return $value;
    }

    /**
     * Ensure .env exists, copying from .env.example if needed.
     */
    protected function ensureEnvExists(): void
    {
        if (!file_exists($this->envPath)) {
            $examplePath = base_path('.env.example');

            if (file_exists($examplePath)) {
                copy($examplePath, $this->envPath);
            } else {
                // Create minimal .env
                file_put_contents($this->envPath, "APP_NAME=\"Runaris Ghost\"\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\n");
            }
        }
    }

    /**
     * Remove write permissions from .env file (post-install hardening).
     */
    public function lockEnvFile(): bool
    {
        if (!file_exists($this->envPath)) {
            return false;
        }

        return chmod($this->envPath, 0444);
    }

    /**
     * Restore write permissions to .env file (for re-install or settings).
     */
    public function unlockEnvFile(): bool
    {
        if (!file_exists($this->envPath)) {
            return false;
        }

        return chmod($this->envPath, 0644);
    }
}
