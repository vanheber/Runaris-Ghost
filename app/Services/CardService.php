<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Card;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class CardService
{
    /**
     * Create a new card (character, scenario, object).
     */
    public function createCard(Project $project, string $title, string $type): Card
    {
        $uuid = (string) Str::uuid();
        $fileName = "card_{$uuid}.md";
        $filePath = $project->getStoragePath("cards/{$fileName}");

        $content = "---\n";
        $content .= "uuid: {$uuid}\n";
        $content .= "type: \"{$type}\"\n";
        $content .= "title: \"{$title}\"\n";
        $content .= "---\n\n";
        $content .= "# {$title}\n\nDescreva este elemento de worldbuilding...";

        File::put($filePath, $content);

        // Index in SQLite context
        app(ProjectManager::class)->switchToProject($project);
        
        return Card::create([
            'uuid' => $uuid,
            'type' => $type,
            'title' => $title,
            'file_path' => "cards/{$fileName}",
            'metadata' => []
        ]);
    }

    /**
     * Update card content and sync metadata.
     */
    public function updateCardContent(Project $project, Card $card, string $markdown): Card
    {
        $filePath = $project->getStoragePath($card->file_path);
        
        // Parse current frontmatter
        $object = YamlFrontMatter::parse(File::get($filePath));
        $data = $object->matter();
        $data['updated_at'] = now()->toDateTimeString();

        // Rebuild file
        $newContent = "---\n";
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $newContent .= "{$key}: " . json_encode($value) . "\n";
            } else {
                $newContent .= "{$key}: {$value}\n";
            }
        }
        $newContent .= "---\n\n";
        $newContent .= $markdown;

        File::put($filePath, $newContent);

        // Sync with SQLite (metadata can be expanded here)
        app(ProjectManager::class)->switchToProject($project);
        $card->touch();

        return $card;
    }

    /**
     * Get content from MD file.
     */
    public function getMarkdownBody(Project $project, Card $card): string
    {
        $filePath = $project->getStoragePath($card->file_path);
        if (!File::exists($filePath)) return "";
        
        $object = YamlFrontMatter::parse(File::get($filePath));
        return $object->body();
    }

    /**
     * Delete card and its file.
     */
    public function deleteCard(Project $project, Card $card): void
    {
        $filePath = $project->getStoragePath($card->file_path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
        
        $card->delete();
    }
}
