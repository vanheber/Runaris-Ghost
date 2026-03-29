<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProjectController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('projects')->group(function () {
    Route::get('/', [ProjectController::class, 'index']);
    Route::post('/', [ProjectController::class, 'store']);
    
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

        // Worldbuilding Cards
        Route::get('/{project_uuid}/cards', [\App\Http\Controllers\CardController::class, 'index']);
        Route::post('/{project_uuid}/cards', [\App\Http\Controllers\CardController::class, 'store']);
        Route::get('/{project_uuid}/cards/{card_uuid}', [\App\Http\Controllers\CardController::class, 'show']);
        Route::put('/{project_uuid}/cards/{card_uuid}', [\App\Http\Controllers\CardController::class, 'update']);
        Route::post('/{project_uuid}/cards/{card_uuid}/image', [\App\Http\Controllers\CardController::class, 'uploadImage']);
        Route::patch('/{project_uuid}/cards/{card_uuid}/image', [\App\Http\Controllers\CardController::class, 'linkImage']);
        Route::delete('/{project_uuid}/cards/{card_uuid}', [\App\Http\Controllers\CardController::class, 'destroy']);

        // Gallery
        Route::get('/{project_uuid}/gallery', [\App\Http\Controllers\GalleryController::class, 'index']);
        Route::post('/{project_uuid}/gallery', [\App\Http\Controllers\GalleryController::class, 'store']);
        Route::put('/{project_uuid}/gallery/{uuid}', [\App\Http\Controllers\GalleryController::class, 'update']);
        Route::delete('/{project_uuid}/gallery/{uuid}', [\App\Http\Controllers\GalleryController::class, 'destroy']);
        Route::get('/{project_uuid}/gallery/{uuid}/image/{type?}', [\App\Http\Controllers\GalleryController::class, 'showImage']);
    });
});
