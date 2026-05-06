<?php

namespace App\Services;

class RequirementsChecker
{
    /**
     * Minimum PHP version required.
     */
    const MIN_PHP_VERSION = '8.3.0';

    /**
     * Required PHP extensions.
     */
    const REQUIRED_EXTENSIONS = [
        'pdo_sqlite',
        'mbstring',
        'openssl',
        'tokenizer',
        'xml',
        'ctype',
        'json',
        'fileinfo',
        'bcmath',
    ];

    /**
     * Optional extensions that unlock extra features.
     */
    const OPTIONAL_EXTENSIONS = [
        'pdo_mysql'  => 'Permite usar MySQL como banco de dados.',
        'pdo_pgsql'  => 'Permite usar PostgreSQL como banco de dados.',
        'gd'         => 'Permite manipulação de imagens (capas, galeria).',
        'zip'        => 'Permite exportação e backup em formato ZIP.',
    ];

    /**
     * Directories that must be writable by the web server.
     */
    const WRITABLE_DIRS = [
        'storage',
        'storage/app',
        'storage/framework',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache',
    ];

    /**
     * Run all checks and return a structured result.
     */
    public function check(): array
    {
        return [
            'php_version'  => $this->checkPhpVersion(),
            'extensions'   => $this->checkExtensions(),
            'optional'     => $this->checkOptionalExtensions(),
            'permissions'  => $this->checkPermissions(),
            'env_writable' => $this->checkEnvWritable(),
            'can_proceed'  => $this->canProceed(),
        ];
    }

    /**
     * Check if PHP version meets minimum requirement.
     */
    public function checkPhpVersion(): array
    {
        $current = PHP_VERSION;
        $ok = version_compare($current, self::MIN_PHP_VERSION, '>=');

        return [
            'label'    => 'PHP >= ' . self::MIN_PHP_VERSION,
            'current'  => $current,
            'ok'       => $ok,
        ];
    }

    /**
     * Check required extensions.
     */
    public function checkExtensions(): array
    {
        $results = [];

        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            $results[] = [
                'name' => $ext,
                'ok'   => extension_loaded($ext),
            ];
        }

        return $results;
    }

    /**
     * Check optional extensions.
     */
    public function checkOptionalExtensions(): array
    {
        $results = [];

        foreach (self::OPTIONAL_EXTENSIONS as $ext => $description) {
            $results[] = [
                'name'        => $ext,
                'description' => $description,
                'ok'          => extension_loaded($ext),
            ];
        }

        return $results;
    }

    /**
     * Check directory permissions.
     */
    public function checkPermissions(): array
    {
        $results = [];

        foreach (self::WRITABLE_DIRS as $dir) {
            $fullPath = base_path($dir);
            $exists = is_dir($fullPath);
            $writable = $exists && is_writable($fullPath);

            $results[] = [
                'path'     => $dir,
                'exists'   => $exists,
                'writable' => $writable,
                'ok'       => $writable,
            ];
        }

        return $results;
    }

    /**
     * Check if .env file is writable (needed for installer).
     */
    public function checkEnvWritable(): array
    {
        $envPath = base_path('.env');
        $exists = file_exists($envPath);
        $writable = $exists && is_writable($envPath);

        // If .env doesn't exist, check if the directory is writable (so we can create it)
        if (!$exists) {
            $writable = is_writable(base_path());
        }

        return [
            'label'    => '.env file',
            'exists'   => $exists,
            'writable' => $writable,
            'ok'       => $writable || !$exists, // OK if writable or if we can create it
        ];
    }

    /**
     * Determine if all critical requirements are met.
     */
    public function canProceed(): bool
    {
        $phpOk = $this->checkPhpVersion()['ok'];

        $extOk = collect($this->checkExtensions())->every(fn($e) => $e['ok']);

        $permOk = collect($this->checkPermissions())->every(fn($p) => $p['ok']);

        return $phpOk && $extOk && $permOk;
    }
}
