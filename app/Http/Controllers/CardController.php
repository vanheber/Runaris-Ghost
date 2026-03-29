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
}
