<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Project;
use App\Models\Card;
use App\Services\CardService;

class CardController extends Controller
{
    protected $cardService;

    public function __construct(CardService $cardService)
    {
        $this->cardService = $cardService;
    }

    /**
     * Get cards by type for the project.
     */
    public function index(Request $request, $project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
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
            $request->title ?? __('Nova Ficha'),
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
        
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        $updatedCard = $this->cardService->updateCardContent($project, $card, $request->content);

        return response()->json($updatedCard);
    }

    /**
     * Update card title.
     */
    public function updateTitle(Request $request, $project_uuid, $card_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        $updatedCard = $this->cardService->updateCardTitle($project, $card, $request->title);

        return response()->json($updatedCard);
    }

    /**
     * Update card type.
     */
    public function updateType(Request $request, $project_uuid, $card_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        $updatedCard = $this->cardService->updateCardType($project, $card, $request->type);

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
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        
        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $baseName = \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = $baseName . '-' . time() . '.' . $extension;
        $thumbName = 'thumb-' . $fileName;

        $projectPath = "private/projects/{$project->uuid}/assets";
        \Illuminate\Support\Facades\Storage::disk('local')->makeDirectory($projectPath);

        $imageManager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());

        // Process Main
        $img = $imageManager->decode($file->getRealPath());
        if ($img->width() > 1200 || $img->height() > 1200) {
            $img->scaleDown(1200, 1200);
        }
        $fullPath = storage_path("app/{$projectPath}/{$fileName}");
        $img->save($fullPath, 92);

        // Process Thumb
        $thumb = $imageManager->decode($file->getRealPath());
        $thumb->cover(400, 400); 
        $thumbPath = storage_path("app/{$projectPath}/{$thumbName}");
        $thumb->save($thumbPath, 75);

        // Create Gallery Item
        $galleryItem = \App\Models\GalleryItem::create([
            'name' => __('Capa:') . " " . $card->title,
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
        
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        $card->update(['image_uuid' => $request->image_uuid]);

        return response()->json($card);
    }

    /**
     * Get all cards and their connections for the graph view.
     */
    public function getGraphData($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        $cards = Card::select('uuid', 'title', 'type')->get();
        $links = \DB::connection('sqlite_project')->table('card_connections')->select('card_uuid as source', 'related_card_uuid as target')->get();
        
        return response()->json([
            'nodes' => $cards,
            'links' => $links
        ]);
    }

    /**
     * Sync connections for a specific card.
     */
    public function syncConnections(Request $request, $project_uuid, $card_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        $input_connections = $request->input('connections', []);
        $sync_data = [];
        
        if (empty($input_connections)) {
            $related_uuids = $request->input('related_uuids', []);
            foreach ($related_uuids as $ruuid) {
                $sync_data[$ruuid] = ['metadata' => null];
            }
        } else {
            foreach ($input_connections as $conn) {
                $sync_data[$conn['uuid']] = [
                    'metadata' => isset($conn['metadata']) ? (is_string($conn['metadata']) ? $conn['metadata'] : json_encode($conn['metadata'])) : null
                ];
            }
        }
        
        // Bidirectional sync logic
        $current_uuids = $card->connections()->pluck('related_card_uuid')->toArray();
        $related_uuids = array_keys($sync_data);
        $removed_uuids = array_diff($current_uuids, $related_uuids);
        
        $card->connections()->sync($sync_data);
        
        foreach ($sync_data as $ruuid => $pivot) {
            $other = Card::where('uuid', $ruuid)->first();
            if ($other) {
                $other->connections()->syncWithoutDetaching([$card->uuid => $pivot]);
            }
        }
        
        foreach ($removed_uuids as $ruuid) {
             $other = Card::where('uuid', $ruuid)->first();
             if ($other) $other->connections()->detach($card->uuid);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get connections for a single card.
     */
    public function getCardConnections($project_uuid, $card_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        return response()->json($card->connections->map(function($c) {
            return [
                'uuid' => $c->uuid,
                'title' => $c->title,
                'type' => $c->type,
                'metadata' => $c->pivot->metadata ? json_decode($c->pivot->metadata) : null
            ];
        }));
    }

    /**
     * Search cards for relationship autocomplete.
     */
    public function searchCards(Request $request, $project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        $query = $request->input('q');
        $exclude = $request->input('exclude', []);

        $cards = Card::where('title', 'like', "%{$query}%")
                     ->whereNotIn('uuid', (array)$exclude)
                     ->limit(15)
                     ->get(['uuid', 'title', 'type']);
                     
        return response()->json($cards);
    }

    /**
     * Use AI to suggest connections for the card.
     */
    public function suggest(Request $request, $project_uuid, $card_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        $content = $this->cardService->getMarkdownBody($project, $card);
        
        // Fetch other cards to compare
        $otherCards = Card::where('uuid', '!=', $card_uuid)->get(['uuid', 'title', 'type']);
        
        try {
            $gemini = \App\Services\GeminiService::forProject($project_uuid);
            $suggestions = $gemini->suggestConnections($card, $content, $otherCards);
            
            return response()->json($suggestions);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a card.
     */
    public function destroy($project_uuid, $card_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        
        $card = Card::where('uuid', $card_uuid)->firstOrFail();
        $this->cardService->deleteCard($project, $card);

        return response()->json(['success' => true]);
    }
}
