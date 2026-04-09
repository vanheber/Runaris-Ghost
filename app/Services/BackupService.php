<?php

namespace App\Services;

use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupService
{
    /**
     * Create a secure snapshot of the manuscripts and database.
     */
    public function createSnapshot()
    {
        try {
            $backupName = 'Runaris_Backup_' . date('Y_m_d_His') . '.zip';
            $backupPath = storage_path('app/backups/' . $backupName);
            
            if (!Storage::exists('backups')) {
                Storage::makeDirectory('backups');
            }

            $zip = new \ZipArchive();
            if ($zip->open($backupPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                // Add DB
                $dbPath = database_path('database.sqlite');
                if (file_exists($dbPath)) {
                    $zip->addFile($dbPath, 'database.sqlite');
                }

                // Add Projects Files
                $projectsPath = storage_path('app/projects');
                if (is_dir($projectsPath)) {
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($projectsPath),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );

                    foreach ($files as $name => $file) {
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = 'projects/' . substr($filePath, strlen($projectsPath) + 1);
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                }

                $zip->close();
                Log::info("Snapshot created successfully at $backupPath");
                return $backupPath;
            }
        } catch (\Exception $e) {
            Log::error("Failed to create snapshot: " . $e->getMessage());
            return false;
        }

        return false;
    }

    /**
     * Restore from a specific snapshot.
     */
    public function rollback($backupFile)
    {
        $backupPath = storage_path('app/backups/' . $backupFile);
        if (!file_exists($backupPath)) return false;

        try {
            $zip = new ZipArchive;
            if ($zip->open($backupPath) === TRUE) {
                $zip->extractTo(storage_path('app/temp_restore/'));
                $zip->close();

                // Restore DB
                if (file_exists(storage_path('app/temp_restore/database.sqlite'))) {
                    copy(storage_path('app/temp_restore/database.sqlite'), database_path('database.sqlite'));
                }

                // Restore Projects
                if (is_dir(storage_path('app/temp_restore/projects'))) {
                    \Illuminate\Support\Facades\File::copyDirectory(storage_path('app/temp_restore/projects'), storage_path('app/projects'));
                }

                \Illuminate\Support\Facades\File::deleteDirectory(storage_path('app/temp_restore'));
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Failed to restore snapshot: " . $e->getMessage());
            return false;
        }
        
        return false;
    }
}
