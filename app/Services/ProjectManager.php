<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ProjectManager
{
    /**
     * Create physical project structure and initialize database.
     */
    public function createProject(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = Project::create($data);
            
            $this->createDirectoryStructure($project);
            $this->initializeDatabase($project);
            
            return $project;
        });
    }

    /**
     * Create the folder skeleton for a project.
     */
    protected function createDirectoryStructure(Project $project): void
    {
        $basePath = $project->getStoragePath();
        
        $directories = [
            $basePath,
            $project->getStoragePath('manuscript'),
            $project->getStoragePath('cards'),
            $project->getStoragePath('assets'),
        ];

        foreach ($directories as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
        }
    }

    /**
     * Create the .sqlite file and run project-specific migrations.
     */
    protected function initializeDatabase(Project $project): void
    {
        $dbPath = $project->getDatabasePath();
        
        if (!File::exists($dbPath)) {
            touch($dbPath);
        }

        // Configure the project connection for this execution
        config(['database.connections.sqlite_project.database' => $dbPath]);
        DB::purge('sqlite_project');

        // Run migrations specifically for the project database
        Artisan::call('migrate', [
            '--database' => 'sqlite_project',
            '--path' => 'database/migrations/project',
            '--force' => true,
        ]);
    }

    /**
     * Switch the database context to a specific project.
     */
    public function switchToProject(Project $project): void
    {
        $dbPath = $project->getDatabasePath();
        
        if (!File::exists($dbPath)) {
            throw new \Exception("Database file not found for project: {$project->name}");
        }

        config(['database.connections.sqlite_project.database' => $dbPath]);
        
        DB::purge('sqlite_project');
        DB::reconnect('sqlite_project');
        
        // Ensure the project database is up to date with the latest project-specific migrations
        Artisan::call('migrate', [
            '--database' => 'sqlite_project',
            '--path' => 'database/migrations/project',
            '--force' => true,
        ]);
        
        // Optionally set as default if the route requires it
        // DB::setDefaultConnection('sqlite_project');
    }
}
