<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Card;
use App\Models\GalleryItem;
use App\Models\ManuscriptItem;
use App\Services\ProjectManager;
use App\Services\ManuscriptService;
use App\Services\CardService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ObsidianMigrationCommand extends Command
{
    protected $signature = 'runaris:migrate-obsidian {path : Path to the Obsidian Viagem Estelar folder} {--project=Viagem Estelar : Project name}';
    protected $description = 'Migrate Obsidian content to Runaris Ghost';

    protected $projectManager;
    protected $manuscriptService;
    protected $cardService;
    protected $imageManager;

    public function __construct(
        ProjectManager $projectManager,
        ManuscriptService $manuscriptService,
        CardService $cardService
    ) {
        parent::__construct();
        $this->projectManager = $projectManager;
        $this->manuscriptService = $manuscriptService;
        $this->cardService = $cardService;
        $this->imageManager = new ImageManager(new Driver());
    }

    public function handle()
    {
        $obsidianPath = rtrim($this->argument('path'), '/');
        $projectName = $this->option('project');

        if (!File::isDirectory($obsidianPath)) {
            $this->error("Directory not found: {$obsidianPath}");
            return 1;
        }

        $this->info("Starting migration for project: {$projectName}");

        // 1. Initialize Project
        $project = Project::where('name', 'like', "%{$projectName}%")->first();
        if (!$project) {
            $this->info("Creating new project: {$projectName}");
            $project = Project::create(['name' => $projectName]);
        }
        
        // Ensure structure exists and switch context
        try {
            $this->ensureProjectDirectories($project);
            $this->projectManager->switchToProject($project);
            
            // Clean slate for re-migration
            $this->info("Cleaning existing data for fresh migration...");
            \App\Models\ManuscriptItem::truncate();
            \App\Models\Card::truncate();
            \App\Models\GalleryItem::truncate();
            
        } catch (\Exception $e) {
            $this->error("Failed to switch context: " . $e->getMessage());
            return 1;
        }
        
        // 2. Migrate Bible (Cards)
        $this->migrateBible($project, $obsidianPath);
        
        // 3. Migrate Manuscript
        $this->migrateManuscript($project, $obsidianPath);

        // 4. Migrate Assets
        $this->migrateAssets($project, $obsidianPath);

        $this->info("Migration completed successfully!");
        return 0;
    }

    protected function migrateBible(Project $project, string $obsidianPath)
    {
        $this->info("Migrating Bible content...");
        $biblePath = $obsidianPath . '/bible';
        
        // 1. Process Characters from Personagens.md
        if (File::exists("{$biblePath}/Personagens.md")) {
            $this->parseAndCreateCards($project, "{$biblePath}/Personagens.md", 'character');
        }

        // 2. Process Items from Itens.md
        if (File::exists("{$biblePath}/Itens.md")) {
            $this->parseAndCreateCards($project, "{$biblePath}/Itens.md", 'object');
        }

        // 3. Process Locations from locais.md
        if (File::exists("{$biblePath}/locais.md")) {
            $this->parseAndCreateCards($project, "{$biblePath}/locais.md", 'scenario');
        }
        
        // 4. Store Summary as Project Bible Content
        if (File::exists("{$biblePath}/Sumário.md")) {
            $project->update(['bible_content' => File::get("{$biblePath}/Sumário.md")]);
        }
    }

    protected function parseAndCreateCards(Project $project, string $filePath, string $type)
    {
        $content = File::get($filePath);
        $pattern = ($type === 'character') 
            ? '/### PERSONAGEM: (.*?)\n(.*?)(?=### PERSONAGEM:|## |$)/s'
            : '/### (.*?)\n(.*?)(?=### |## |$)/s';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $title = trim($match[1]);
                $body = trim($match[2]);
                
                $this->info("Processing {$type}: {$title}");
                
                // Idempotent card creation
                $card = Card::where('title', $title)->where('type', $type)->first();
                if (!$card) {
                    $card = $this->cardService->createCard($project, $title, $type);
                }
                
                $this->cardService->updateCardContent($project, $card, $body);
            }
        }
    }

    protected function migrateManuscript(Project $project, string $obsidianPath)
    {
        $this->info("Migrating Manuscript...");
        $indexPath = $obsidianPath . '/bible/Índice.md';
        $manuscriptPath = $obsidianPath . '/Manuscript/Viagem Estelar';

        if (!File::exists($indexPath)) {
            $this->error("Índice.md not found! Order will be random.");
            return;
        }

        $indexContent = File::get($indexPath);
        $lines = explode("\n", $indexContent);
        
        $currentParentUuid = null;
        $order = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Extract title and check formatting
            // Pattern: 1. **Title** or 1. *Title**
            if (preg_match('/^\d+\.\s+(.*)/', $line, $matches)) {
                $rawTitle = $matches[1];
                $title = trim($rawTitle, "* ");
                
                $filename = "{$title}.md";
                $filePath = "{$manuscriptPath}/{$filename}";

                if (File::exists($filePath)) {
                    $this->info("Importing ordered item: {$title}");
                    
                    // Determine type: Sections (Livros) vs Chapters/Scenes
                    $isBook = Str::contains(strtolower($title), 'livro');
                    $isTopMeta = in_array(strtolower($title), ['ficha técnica', 'dedicatória', 'prefácio', 'prólogo', 'epílogo', 'posfácio', 'perguntas e respostas']);
                    
                    $type = 'scene';
                    if ($isBook) {
                        $type = 'section';
                        $currentParentUuid = null; // Books are root
                    } elseif ($isTopMeta) {
                        $type = 'scene';
                        $currentParentUuid = null; // Meta items are root
                    } elseif ($currentParentUuid) {
                        $type = 'chapter'; // Nested under a book
                    }

                    $item = ManuscriptItem::where('title', $title)->first();
                    if (!$item) {
                        // Use service to create with proper level-scoped ordering
                        $item = $this->manuscriptService->createItem($project, [
                            'title' => $title,
                            'type' => $type,
                            'parent_uuid' => $currentParentUuid, 
                        ]);
                    } else {
                        $item->update([
                            'parent_uuid' => $currentParentUuid,
                            'type' => $type
                        ]);
                    }
                    
                    // If it was a section/book, set it as the parent for subsequent items
                    if ($type === 'section') {
                        $currentParentUuid = $item->uuid;
                    }
                    
                    $this->manuscriptService->saveContent($project, $item->uuid, File::get($filePath));
                } else {
                    $this->warn("Scene file not found for title: {$title}");
                }
            }
        }
    }

    protected function migrateAssets(Project $project, string $obsidianPath)
    {
        $this->info("Migrating Assets...");
        $assetsPath = "{$obsidianPath}/assets";
        
        if (!File::isDirectory($assetsPath)) {
            $this->warn("Assets path not found: {$assetsPath}");
            return;
        }

        $projectAssetsDir = $project->getStoragePath('assets');
        $projectPath = "private/projects/{$project->uuid}/assets"; // Relative path for DB (relative to storage/app)

        $files = File::files($assetsPath);
        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (!in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'])) continue;
            
            $thumbName = 'thumb-' . $filename;
            
            $destPath = "{$projectAssetsDir}/{$filename}";
            $destThumbPath = "{$projectAssetsDir}/{$thumbName}";
            
            // 1. Process Main Image
            $img = $this->imageManager->decode($file->getRealPath());
            if ($img->width() > 1600 || $img->height() > 2560) {
                $img->scaleDown(1600, 2560);
            }
            $img->save($destPath, 85);
            
            // 2. Process Thumbnail
            $thumb = $this->imageManager->decode($file->getRealPath());
            $thumb->cover(200, 240);
            $thumb->save($destThumbPath, 85);
            
            // 3. Register in Gallery
            $galleryItem = GalleryItem::updateOrCreate(
                ['name' => $filename],
                [
                    'file_path' => "{$projectPath}/{$filename}",
                    'thumb_path' => "{$projectPath}/{$thumbName}",
                    'type' => 'image',
                    'filesize' => filesize($destPath),
                    'dimensions' => "{$img->width()}x{$img->height()}",
                ]
            );

            // 4. Attempt to link to card
            $cleanName = Str::slug(pathinfo($filename, PATHINFO_FILENAME));
            $cards = Card::all();
            foreach ($cards as $card) {
                if (Str::slug($card->title) === $cleanName) {
                    $card->update(['image_uuid' => $galleryItem->uuid]);
                    $this->info("Linked asset {$filename} to card {$card->title}");
                }
            }
        }
    }

    protected function ensureProjectDirectories(Project $project)
    {
        $directories = [
            $project->getStoragePath(),
            $project->getStoragePath('manuscript'),
            $project->getStoragePath('cards'),
            $project->getStoragePath('assets'),
        ];

        foreach ($directories as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
        }

        if (!File::exists($project->getDatabasePath())) {
            touch($project->getDatabasePath());
        }
    }
}
