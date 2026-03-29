<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Project;
use App\Models\Card;
use App\Services\CardService;
use App\Services\ProjectManager;

class CardController extends Controller
{
    protected $cardService;
    protected $projectManager;

    public function __construct(CardService $cardService, ProjectManager $projectManager)
    {
        $this->cardService = $cardService;
        $this->projectManager = $projectManager;
    }

    /**
     * Get cards by type for the project.
     */
    public function index(Request $request, $project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $this->projectManager->switchToProject($project);
        
        $cards = Card::where('type', $request->type)->orderBy('title')->get();
        return response()->json($cards);
    }

    /**
     * Create a new card.
     */
    public function store(Request $request, $project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        $card = $this->cardService->createCard(
            $project, 
            $request->title ?? 'Nova Ficha',
            $request->type ?? 'character'
        );

        return response()->json($card, 201);
    }

    /**
     * Get card content.
     */
    public function show($project_uuid, $card_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $this->projectManager->switchToProject($project);
        
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        $content = $this->cardService->getMarkdownBody($project, $card);

        return response()->json([
            'card' => $card,
            'content' => $content
        ]);
    }

    /**
     * Update card content.
     */
    public function update(Request $request, $project_uuid, $card_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $this->projectManager->switchToProject($project);
        
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        $updatedCard = $this->cardService->updateCardContent($project, $card, $request->content);

        return response()->json($updatedCard);
    }

    /**
     * Upload and link an image to a card.
     */
    public function uploadImage(Request $request, $project_uuid, $card_uuid)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
        ]);

        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $this->projectManager->switchToProject($project);
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        
        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $baseName = \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = $baseName . '-' . time() . '.' . $extension;
        $thumbName = 'thumb-' . $fileName;

        $projectPath = "projects/{$project->uuid}/assets";
        \Illuminate\Support\Facades\Storage::disk('local')->makeDirectory($projectPath);

        $imageManager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());

        // Process Main
        $img = $imageManager->decode($file->getRealPath());
        if ($img->width() > 1200 || $img->height() > 1200) {
            $img->scaleDown(1200, 1200);
        }
        $fullPath = storage_path("app/{$projectPath}/{$fileName}");
        $img->save($fullPath);

        // Process Thumb
        $thumb = $imageManager->decode($file->getRealPath());
        $thumb->cover(400, 400); 
        $thumbPath = storage_path("app/{$projectPath}/{$thumbName}");
        $thumb->save($thumbPath);

        // Create Gallery Item
        $galleryItem = \App\Models\GalleryItem::create([
            'name' => "Capa: " . $card->title,
            'file_path' => "{$projectPath}/{$fileName}",
            'thumb_path' => "{$projectPath}/{$thumbName}",
            'category' => 'card_cover',
            'dimensions' => "{$img->width()}x{$img->height()}",
            'filesize' => filesize($fullPath),
        ]);

        // Link to Card
        $card->update(['image_uuid' => $galleryItem->uuid]);

        return response()->json($card);
    }

    /**
     * Link an existing gallery image to a card.
     */
    public function linkImage(Request $request, $project_uuid, $card_uuid)
    {
        $request->validate([
            'image_uuid' => 'required|string',
        ]);

        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $this->projectManager->switchToProject($project);
        
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        $card->update(['image_uuid' => $request->image_uuid]);

        return response()->json($card);
    }

    /**
     * Delete a card.
     */
    public function destroy($project_uuid, $card_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $this->projectManager->switchToProject($project);
        
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        $this->cardService->deleteCard($project, $card);

        return response()->json(['success' => true]);
    }
}
