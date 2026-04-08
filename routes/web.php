<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProjectController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('projects')->group(function () {
    Route::get('/', [ProjectController::class, 'index']);
    Route::post('/', [ProjectController::class, 'store']);
    Route::delete('/{project_uuid}', [ProjectController::class, 'destroy']);
    
    // Group routes that require the project context
    Route::middleware(['project.context'])->group(function () {
        Route::get('/{project_uuid}', [ProjectController::class, 'show']);
        
        // Manuscript Tree (Section > Chapter > Scene)
        Route::get('/{project_uuid}/manuscript', [\App\Http\Controllers\ManuscriptController::class, 'index']);
        Route::post('/{project_uuid}/manuscript', [\App\Http\Controllers\ManuscriptController::class, 'store']);
        Route::get('/{project_uuid}/manuscript/{uuid}', [\App\Http\Controllers\ManuscriptController::class, 'show']);
        Route::put('/{project_uuid}/manuscript/{uuid}', [\App\Http\Controllers\ManuscriptController::class, 'update']);
        Route::patch('/{project_uuid}/manuscript/{uuid}/title', [\App\Http\Controllers\ManuscriptController::class, 'updateTitle']);
        Route::post('/{project_uuid}/manuscript/sort', [\App\Http\Controllers\ManuscriptController::class, 'sort']);
        Route::delete('/{project_uuid}/manuscript/{uuid}', [\App\Http\Controllers\ManuscriptController::class, 'destroy']);
        Route::get('/{project_uuid}/manuscript/{uuid}/planning', [\App\Http\Controllers\ManuscriptController::class, 'getPlanning']);
        Route::put('/{project_uuid}/manuscript/{uuid}/planning', [\App\Http\Controllers\ManuscriptController::class, 'savePlanning']);

        // Worldbuilding Cards
        Route::get('/{project_uuid}/cards/search', [\App\Http\Controllers\CardController::class, 'searchCards']);
        Route::get('/{project_uuid}/graph', [\App\Http\Controllers\CardController::class, 'getGraphData']);
        
        Route::get('/{project_uuid}/cards', [\App\Http\Controllers\CardController::class, 'index']);
        Route::post('/{project_uuid}/cards', [\App\Http\Controllers\CardController::class, 'store']);
        Route::get('/{project_uuid}/cards/{card_uuid}', [\App\Http\Controllers\CardController::class, 'show']);
        Route::put('/{project_uuid}/cards/{card_uuid}', [\App\Http\Controllers\CardController::class, 'update']);
        Route::patch('/{project_uuid}/cards/{card_uuid}/title', [\App\Http\Controllers\CardController::class, 'updateTitle']);
        Route::post('/{project_uuid}/cards/{card_uuid}/image', [\App\Http\Controllers\CardController::class, 'uploadImage']);
        Route::patch('/{project_uuid}/cards/{card_uuid}/image', [\App\Http\Controllers\CardController::class, 'linkImage']);
        Route::delete('/{project_uuid}/cards/{card_uuid}', [\App\Http\Controllers\CardController::class, 'destroy']);
        
        Route::get('/{project_uuid}/cards/{card_uuid}/connections', [\App\Http\Controllers\CardController::class, 'getCardConnections']);
        Route::post('/{project_uuid}/cards/{card_uuid}/connections', [\App\Http\Controllers\CardController::class, 'syncConnections']);

        // Gallery
        Route::get('/{project_uuid}/gallery', [\App\Http\Controllers\GalleryController::class, 'index']);
        Route::post('/{project_uuid}/gallery', [\App\Http\Controllers\GalleryController::class, 'store']);
        Route::put('/{project_uuid}/gallery/{uuid}', [\App\Http\Controllers\GalleryController::class, 'update']);
        Route::delete('/{project_uuid}/gallery/{uuid}', [\App\Http\Controllers\GalleryController::class, 'destroy']);
        Route::get('/{project_uuid}/gallery/{uuid}/image/{type?}', [\App\Http\Controllers\GalleryController::class, 'showImage']);

        // Project Settings & Export
        Route::get('/{project_uuid}/settings', [ProjectController::class, 'settings']);
        Route::put('/{project_uuid}', [ProjectController::class, 'update']);
        Route::put('/{project_uuid}/metadata', [ProjectController::class, 'updateMetadata']);
        Route::patch('/{project_uuid}/cover', [\App\Http\Controllers\ProjectController::class, 'updateCover']);
        Route::get('/{project_uuid}/export/epub', [\App\Http\Controllers\ProjectController::class, 'exportEpub']);
        Route::get('/{project_uuid}/export/pdf', [\App\Http\Controllers\ProjectController::class, 'exportPdf']);
        Route::get('/{project_uuid}/export/html', [\App\Http\Controllers\ProjectController::class, 'exportHtml']);
        Route::get('/{project_uuid}/export-state', [\App\Http\Controllers\ProjectController::class, 'getExportState']);
        Route::post('/{project_uuid}/export-process', [\App\Http\Controllers\ProjectController::class, 'processBatchExport']);

        // Project Bible
        Route::get('/{project_uuid}/bible', [\App\Http\Controllers\BibleController::class, 'show']);
        Route::put('/{project_uuid}/bible', [\App\Http\Controllers\BibleController::class, 'update']);
    });
});

Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index']);
