<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ManuscriptItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManuscriptService
{
    /**
     * Get the full tree of manuscript items for a project.
     */
    public function getTree(Project $project)
    {
        $items = ManuscriptItem::orderBy('order')->get();
        return $this->buildTree($items);
    }

    private function buildTree($items, $parentUuid = null)
    {
        $branch = [];
        foreach ($items as $item) {
            if ($item->parent_uuid == $parentUuid) {
                $children = $this->buildTree($items, $item->uuid);
                if ($children) {
                    $item->setAttribute('children', $children);
                } else {
                    $item->setAttribute('children', []);
                }
                $branch[] = $item;
            }
        }
        return $branch;
    }

    /**
     * Create a new item (Section, Chapter, Scene).
     */
    public function createItem(Project $project, array $data): ManuscriptItem
    {
        $uuid = (string) Str::uuid();
        
        // Find last order in the same level
        $lastOrder = ManuscriptItem::where('parent_uuid', $data['parent_uuid'] ?? null)
            ->max('order') ?? 0;

        $item = ManuscriptItem::create([
            'uuid' => $uuid,
            'parent_uuid' => $data['parent_uuid'] ?? null,
            'type' => $data['type'], // section, chapter, scene
            'title' => $data['title'],
            'order' => $lastOrder + 1,
        ]);

        // Initialize empty content file
        $this->saveContent($project, $uuid, "# " . $data['title'] . "\n\nComece sua escrita aqui...");

        return $item;
    }

    /**
     * Save item content to the physical .md file.
     */
    public function saveContent(Project $project, string $uuid, string $content)
    {
        $path = $this->getFilePath($project, $uuid);
        Storage::put($path, $content);
        
        // Update word count and timestamp in SQLite
        $item = ManuscriptItem::where('uuid', $uuid)->first();
        if ($item) {
            $wordCount = str_word_count(strip_tags($content));
            $item->update([
                'word_count' => $wordCount,
                'content_updated_at' => now(),
            ]);
        }
    }

    /**
     * Get content from the physical .md file.
     */
    public function getContent(Project $project, string $uuid): string
    {
        $path = $this->getFilePath($project, $uuid);
        if (Storage::exists($path)) {
            return Storage::get($path);
        }
        return "";
    }

    /**
     * Update item hierarchy/order (Drag & Drop).
     */
    public function updateOrder(array $sortingData)
    {
        foreach ($sortingData as $data) {
            ManuscriptItem::where('uuid', $data['uuid'])->update([
                'parent_uuid' => $data['parent_uuid'] ?? null,
                'order' => $data['order']
            ]);
        }
    }

    private function getFilePath(Project $project, string $uuid): string
    {
        return "projects/{$project->uuid}/manuscript/{$uuid}.md";
    }

    public function deleteItem(Project $project, string $uuid)
    {
        $item = ManuscriptItem::where('uuid', $uuid)->first();
        if ($item) {
            // Recursive delete of children
            foreach ($item->children as $child) {
                $this->deleteItem($project, $child->uuid);
            }
            
            // Delete physical file
            Storage::delete($this->getFilePath($project, $uuid));
            
            // Delete record
            $item->delete();
        }
    }
}
