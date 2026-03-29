<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test project creation and directory skeleton.
     */
    public function test_can_create_project(): void
    {
        $response = $this->post('/projects', [
            'name' => 'Project Alpha',
            'description' => 'A test project'
        ]);

        $response->assertStatus(201);
        $project = \App\Models\Project::first();

        $this->assertNotNull($project);
        $this->assertEquals('Project Alpha', $project->name);
        
        // Check directories
        $this->assertDirectoryExists($project->getStoragePath());
        $this->assertDirectoryExists($project->getStoragePath('chapters'));
        $this->assertDirectoryExists($project->getStoragePath('cards'));
        $this->assertFileExists($project->getDatabasePath());
    }

    /**
     * Test dynamic context switching via middleware.
     */
    public function test_can_switch_context_to_project(): void
    {
        // 1. Create a project
        $project = app(\App\Services\ProjectManager::class)->createProject([
            'name' => 'Context Project',
        ]);

        // 2. Access the project route
        $response = $this->get("/projects/{$project->uuid}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'uuid' => $project->uuid,
        ]);

        // 3. Verify the database path matches in the response
        $this->assertEquals(
            $project->getDatabasePath(),
            $response->json('database_path')
        );
    }
}
