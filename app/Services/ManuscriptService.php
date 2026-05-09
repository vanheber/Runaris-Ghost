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
        $item = ManuscriptItem::where('uuid', $uuid)->first();
        $slug = ($item && $item->title) ? Str::slug($item->title) : 'document';
        if (empty($slug)) $slug = 'document';
        
        $idealPath = "projects/{$project->uuid}/manuscript/{$slug}_{$uuid}.md";
        $existingPath = $this->findExistingPath($project, $uuid, 'content');

        if ($existingPath && $existingPath !== $idealPath) {
            Storage::move($existingPath, $idealPath);
        }

        Storage::put($idealPath, $content);
        
        if ($item) {
            $wordCount = str_word_count(strip_tags($content));
            $item->update([
                'word_count' => $wordCount,
                'content_updated_at' => now(),
            ]);
            
            if ($syncTOC && !$item->is_system) {
                $this->mirrorToDocuments($project, $item, $content);
            }
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
        $item = ManuscriptItem::where('uuid', $uuid)->first();
        $slug = ($item && $item->title) ? Str::slug($item->title) : 'document';
        if (empty($slug)) $slug = 'document';

        $idealPath = "projects/{$project->uuid}/manuscript/{$slug}_{$uuid}.beats.md";
        $existingPath = $this->findExistingPath($project, $uuid, 'planning');

        if ($existingPath && $existingPath !== $idealPath) {
            Storage::move($existingPath, $idealPath);
        }

        Storage::put($idealPath, $content);
        
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
        $directory = "projects/{$project->uuid}/manuscript";

        // 1. Try to find an existing file that ends with _{$uuid}.{$extension}
        $existing = $this->findExistingPath($project, $uuid, $type);
        if ($existing) {
            return $existing;
        }

        // 2. If not found, generate a new path based on title
        $item = ManuscriptItem::where('uuid', $uuid)->first();
        $slug = $item ? Str::slug($item->title) : 'document';
        if (empty($slug)) $slug = 'document';

        return "{$directory}/{$slug}_{$uuid}.{$extension}";
    }

    private function findExistingPath(Project $project, string $uuid, string $type = 'content'): ?string
    {
        $extension = ($type === 'planning') ? 'beats.md' : 'md';
        $directory = "projects/{$project->uuid}/manuscript";
        
        if (!Storage::exists($directory)) {
            return null;
        }

        $files = Storage::files($directory);
        foreach ($files as $file) {
            $basename = basename($file);
            // Match slug_UUID.md or legacy UUID.md
            if (str_ends_with($basename, "_{$uuid}.{$extension}") || $basename === "{$uuid}.{$extension}") {
                return $file;
            }
        }

        return null;
    }

    private function mirrorToDocuments(Project $project, ManuscriptItem $item, string $content)
    {
        try {
            $docsPath = getenv('HOME') . '/Documents/RunarisGhost/' . Str::slug($project->name);
            if (!is_dir($docsPath)) {
                @mkdir($docsPath, 0777, true);
            }
            
            $safeTitle = preg_replace('/[^A-Za-z0-9\- \_]+/', '', $item->title);
            $orderPrefix = str_pad($item->order, 2, '0', STR_PAD_LEFT);
            $filename = "{$orderPrefix} - {$safeTitle}.md";
            
            file_put_contents($docsPath . '/' . $filename, $content);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to mirror document: " . $e->getMessage());
        }
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
