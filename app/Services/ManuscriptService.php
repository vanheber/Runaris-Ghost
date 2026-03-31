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
        $this->ensureTOCExists($project);
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

        $this->syncTOC($project);

        return $item;
    }

    /**
     * Save item content to the physical .md file.
     */
    public function saveContent(Project $project, string $uuid, string $content, bool $syncTOC = true)
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

        if ($syncTOC && $item && !$item->is_system) {
            // No need to sync TOC on content save unless we want to update headings inside content
            // But usually TOC is based on items title.
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
     * Save planning/beats content to the physical .beats.md file.
     */
    public function savePlanning(Project $project, string $uuid, string $content)
    {
        $path = $this->getFilePath($project, $uuid, 'planning');
        Storage::put($path, $content);
        
        $item = ManuscriptItem::where('uuid', $uuid)->first();
        if ($item) {
            $item->update([
                'has_planning' => true,
                'planning_updated_at' => now(),
            ]);
        }
    }

    /**
     * Get planning/beats from the physical .beats.md file.
     */
    public function getPlanning(Project $project, string $uuid): string
    {
        $path = $this->getFilePath($project, $uuid, 'planning');
        if (Storage::exists($path)) {
            return Storage::get($path);
        }
        return "";
    }

    /**
     * Update item hierarchy/order (Drag & Drop).
     */
    public function updateOrder(Project $project, array $sortingData)
    {
        foreach ($sortingData as $data) {
            ManuscriptItem::where('uuid', $data['uuid'])->update([
                'parent_uuid' => $data['parent_uuid'] ?? null,
                'order' => $data['order']
            ]);
        }
        $this->syncTOC($project);
    }

    public function updateTitle(Project $project, string $uuid, string $title)
    {
        $item = ManuscriptItem::where('uuid', $uuid)->firstOrFail();
        $item->update(['title' => $title]);
        
        $this->syncTOC($project);
        
        return $item;
    }

    private function getFilePath(Project $project, string $uuid, string $type = 'content'): string
    {
        $extension = ($type === 'planning') ? 'beats.md' : 'md';
        return "projects/{$project->uuid}/manuscript/{$uuid}.{$extension}";
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
            $this->syncTOC($project);
        }
    }

    private function ensureTOCExists(Project $project)
    {
        // First, check if we have multiple or unsynced TOCs
        $tocs = ManuscriptItem::where(function($q) {
            $q->where('type', 'toc')
              ->orWhere('title', 'Índice');
        })->get();

        if ($tocs->count() > 1 || ($tocs->count() == 1 && !$tocs->first()->is_system)) {
            // Delete all and start fresh to avoid ghosts
            foreach ($tocs as $t) {
                Storage::delete($this->getFilePath($project, $t->uuid));
                $t->delete();
            }
        }

        $toc = ManuscriptItem::where('is_system', true)->where('type', 'toc')->first();
        if (!$toc) {
            $uuid = (string) Str::uuid();
            $toc = ManuscriptItem::create([
                'uuid' => $uuid,
                'type' => 'toc',
                'title' => 'Índice',
                'order' => -100, // Always first
                'is_system' => true
            ]);
            $this->syncTOC($project);
        }
    }

    public function syncTOC(Project $project)
    {
        $tocItem = ManuscriptItem::where('is_system', true)->where('type', 'toc')->first();
        if (!$tocItem) return;

        $items = ManuscriptItem::where('is_system', false)->orderBy('order')->get();
        $markdown = "# Índice\n\n";
        $markdown .= $this->generateTOCMarkdown($this->buildTree($items));

        $this->saveContent($project, $tocItem->uuid, $markdown, false);
    }

    private function generateTOCMarkdown($nodes, $level = 0)
    {
        $md = "";
        foreach ($nodes as $node) {
            $indent = str_repeat("  ", $level);
            // Anchor-friendly title for Kindle/HTML
            $anchor = Str::slug($node->title);
            $prefix = $node->type === 'section' ? "**" : "";
            $suffix = $node->type === 'section' ? "**" : "";
            
            $md .= "{$indent}* [{$prefix}{$node->title}{$suffix}](#{$anchor})\n";
            
            if (!empty($node->children)) {
                $md .= $this->generateTOCMarkdown($node->children, $level + 1);
            }
        }
        return $md;
    }
}
