<?php

namespace App\Services;

use App\Models\Project;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ProjectBackupService
{
    protected $basePath = 'private/projects';

    /**
     * Create a full snapshot of a specific project.
     * Includes database and all files.
     */
    public function createSnapshot(Project $project)
    {
        try {
            $projectPath = storage_path("app/{$this->basePath}/{$project->uuid}");
            $snapshotsPath = "{$projectPath}/snapshots";

            if (!File::exists($snapshotsPath)) {
                File::makeDirectory($snapshotsPath, 0755, true);
            }

            $snapshotName = 'Snapshot_' . date('Y-m-d_His') . '.zip';
            $snapshotFile = "{$snapshotsPath}/{$snapshotName}";

            $zip = new ZipArchive();
            if ($zip->open($snapshotFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($projectPath),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    // Skip the snapshots directory itself to avoid recursion
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        
                        // Ignore current snapshots folder and export folder
                        if (strpos($filePath, DIRECTORY_SEPARATOR . 'snapshots' . DIRECTORY_SEPARATOR) !== false ||
                            strpos($filePath, DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR) !== false) {
                            continue;
                        }

                        $relativePath = substr($filePath, strlen($projectPath) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }

                $zip->close();
                return $snapshotName;
            }
        } catch (\Exception $e) {
            Log::error("Failed to create project snapshot: " . $e->getMessage());
            return false;
        }

        return false;
    }

    /**
     * Restore a project from a snapshot file.
     */
    public function restoreSnapshot(Project $project, string $snapshotName)
    {
        $projectPath = storage_path("app/{$this->basePath}/{$project->uuid}");
        $snapshotPath = "{$projectPath}/snapshots/{$snapshotName}";

        if (!File::exists($snapshotPath)) {
            return false;
        }

        return $this->performRestore($project, $snapshotPath);
    }

    /**
     * Restore project from an uploaded ZIP file.
     */
    public function restoreFromUpload(Project $project, $uploadedFile)
    {
        $tempPath = storage_path("app/temp_restore_" . $project->uuid . ".zip");
        move_uploaded_file($uploadedFile, $tempPath);
        
        $result = $this->performRestore($project, $tempPath);
        
        if (File::exists($tempPath)) {
            File::delete($tempPath);
        }

        return $result;
    }

    /**
     * Core logic to extract ZIP and replace project files.
     */
    protected function performRestore(Project $project, string $zipPath)
    {
        try {
            $projectPath = storage_path("app/{$this->basePath}/{$project->uuid}");
            $tempExtractPath = storage_path("app/temp_extract_" . $project->uuid);

            if (File::exists($tempExtractPath)) {
                File::deleteDirectory($tempExtractPath);
            }
            File::makeDirectory($tempExtractPath, 0755, true);

            $zip = new ZipArchive();
            if ($zip->open($zipPath) === TRUE) {
                $zip->extractTo($tempExtractPath);
                $zip->close();

                // Validate if it's a valid project backup (should have database.sqlite)
                if (!File::exists("{$tempExtractPath}/database.sqlite")) {
                    throw new \Exception("Backup inválido: database.sqlite não encontrado.");
                }

                // Backup current snapshots folder to move it back after restore
                $snapshotsPath = "{$projectPath}/snapshots";
                $tempSnapshots = storage_path("app/temp_snapshots_" . $project->uuid);
                if (File::exists($snapshotsPath)) {
                    File::moveDirectory($snapshotsPath, $tempSnapshots);
                }

                // Wipe current project directory (except the ZIP we might be using if it was inside)
                // Actually, the ZIP is in /snapshots or /temp, so we are safe.
                File::cleanDirectory($projectPath);

                // Copy extracted files
                File::copyDirectory($tempExtractPath, $projectPath);

                // Restore snapshots folder
                if (File::exists($tempSnapshots)) {
                    if (File::exists($snapshotsPath)) File::deleteDirectory($snapshotsPath);
                    File::moveDirectory($tempSnapshots, $snapshotsPath);
                }

                File::deleteDirectory($tempExtractPath);
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Failed to restore project snapshot: " . $e->getMessage());
            return false;
        }

        return false;
    }

    /**
     * List available snapshots for a project.
     */
    public function listSnapshots(Project $project)
    {
        $snapshotsPath = storage_path("app/{$this->basePath}/{$project->uuid}/snapshots");
        
        if (!File::exists($snapshotsPath)) {
            return [];
        }

        $files = File::files($snapshotsPath);
        $snapshots = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'zip') {
                $snapshots[] = [
                    'name' => $file->getFilename(),
                    'size' => round($file->getSize() / 1024 / 1024, 2) . ' MB',
                    'date' => date('d/m/Y H:i:s', $file->getMTime()),
                    'timestamp' => $file->getMTime()
                ];
            }
        }

        // Sort by date desc
        usort($snapshots, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return $snapshots;
    }
}
