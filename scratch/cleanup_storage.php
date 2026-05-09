<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Project;
use App\Models\ManuscriptItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

echo "Iniciando limpeza de arquivos órfãos...\n";

$projects = Project::all();

foreach ($projects as $project) {
    echo "Processando projeto: {$project->name} ({$project->uuid})\n";
    
    // Switch to project database
    $dbPath = storage_path("app/private/projects/{$project->uuid}/database.sqlite");
    if (!file_exists($dbPath)) {
        echo "  [ERRO] Banco de dados do projeto não encontrado.\n";
        continue;
    }
    
    config(['database.connections.sqlite_project.database' => $dbPath]);
    \Illuminate\Support\Facades\DB::purge('sqlite_project');
    \Illuminate\Support\Facades\DB::reconnect('sqlite_project');

    // Get all valid UUIDs
    $validUuids = ManuscriptItem::pluck('uuid')->toArray();
    
    $directory = "projects/{$project->uuid}/manuscript";
    $backupDir = "projects/{$project->uuid}/orphaned_files";
    
    if (!Storage::exists($directory)) {
        echo "  [AVISO] Diretório de manuscritos não existe.\n";
        continue;
    }

    if (!Storage::exists($backupDir)) {
        Storage::makeDirectory($backupDir);
    }

    $allFiles = Storage::files($directory);
    $movedCount = 0;

    foreach ($allFiles as $file) {
        $filename = basename($file);
        
        // Extract UUID from filename
        // Padrões: "slug_UUID.md", "UUID.md", "slug_UUID.beats.md", "UUID.beats.md"
        $uuid = null;
        if (preg_match('/([a-f0-9-]{36})/', $filename, $matches)) {
            $uuid = $matches[1];
        }

        if (!$uuid || !in_array($uuid, $validUuids)) {
            // É um arquivo órfão
            Storage::move($file, "{$backupDir}/{$filename}");
            $movedCount++;
        }
    }

    echo "  Limpeza concluída para este projeto: {$movedCount} arquivos movidos para 'orphaned_files'.\n";
}

echo "\nOperação finalizada!\n";
