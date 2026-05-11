<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProjectController;

Route::get('/', function () {
    return redirect('/projects');
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
        Route::post('/{project_uuid}/manuscript/{uuid}/summary', [\App\Http\Controllers\ManuscriptController::class, 'generateSummary']);

        // Worldbuilding Cards
        Route::get('/{project_uuid}/cards/search', [\App\Http\Controllers\CardController::class, 'searchCards']);
        Route::get('/{project_uuid}/graph', [\App\Http\Controllers\CardController::class, 'getGraphData']);
        
        Route::get('/{project_uuid}/cards', [\App\Http\Controllers\CardController::class, 'index']);
        Route::post('/{project_uuid}/cards', [\App\Http\Controllers\CardController::class, 'store']);
        Route::get('/{project_uuid}/cards/{card_uuid}', [\App\Http\Controllers\CardController::class, 'show']);
        Route::get('/{project_uuid}/cards/{card_uuid}/suggest', [\App\Http\Controllers\CardController::class, 'suggest']);
        Route::put('/{project_uuid}/cards/{card_uuid}', [\App\Http\Controllers\CardController::class, 'update']);
        Route::patch('/{project_uuid}/cards/{card_uuid}/title', [\App\Http\Controllers\CardController::class, 'updateTitle']);
        Route::patch('/{project_uuid}/cards/{card_uuid}/type', [\App\Http\Controllers\CardController::class, 'updateType']);
        Route::post('/{project_uuid}/cards/{card_uuid}/image', [\App\Http\Controllers\CardController::class, 'uploadImage']);
        Route::patch('/{project_uuid}/cards/{card_uuid}/image', [\App\Http\Controllers\CardController::class, 'linkImage']);
        Route::delete('/{project_uuid}/cards/{card_uuid}', [\App\Http\Controllers\CardController::class, 'destroy']);
        
        Route::get('/{project_uuid}/cards/{card_uuid}/connections', [\App\Http\Controllers\CardController::class, 'getCardConnections']);
        Route::post('/{project_uuid}/cards/{card_uuid}/connections', [\App\Http\Controllers\CardController::class, 'syncConnections']);

        // Gallery
        Route::get('/{project_uuid}/gallery', [\App\Http\Controllers\GalleryController::class, 'index']);
        Route::post('/{project_uuid}/gallery', [\App\Http\Controllers\GalleryController::class, 'store']);
        Route::put('/{project_uuid}/gallery/{item_uuid}', [\App\Http\Controllers\GalleryController::class, 'update']);
        Route::delete('/{project_uuid}/gallery/{item_uuid}', [\App\Http\Controllers\GalleryController::class, 'destroy']);
        Route::get('/{project_uuid}/gallery/{item_uuid}/image/{type?}', [\App\Http\Controllers\GalleryController::class, 'showImage']);

        // Project Settings & Export
        Route::get('/{project_uuid}/settings', [ProjectController::class, 'settings']);
        Route::put('/{project_uuid}', [ProjectController::class, 'update']);
        Route::put('/{project_uuid}/metadata', [ProjectController::class, 'updateMetadata']);
        Route::patch('/{project_uuid}/cover', [\App\Http\Controllers\ProjectController::class, 'updateCover']);
        Route::get('/{project_uuid}/export/epub', [\App\Http\Controllers\ProjectController::class, 'exportEpub']);
        Route::get('/{project_uuid}/export/pdf', [\App\Http\Controllers\ProjectController::class, 'exportPdf']);
        Route::get('/{project_uuid}/export/html', [\App\Http\Controllers\ProjectController::class, 'exportHtml']);
        Route::get('/{project_uuid}/export/zip', [\App\Http\Controllers\ProjectController::class, 'exportZip']);
        Route::get('/{project_uuid}/export/markdown', [\App\Http\Controllers\ProjectController::class, 'exportMarkdown']);
        Route::get('/{project_uuid}/export/backup', [\App\Http\Controllers\ProjectController::class, 'exportBackup']);
        Route::get('/{project_uuid}/export/preview-html', [\App\Http\Controllers\ProjectController::class, 'previewHtml']);
        Route::get('/{project_uuid}/export-state', [\App\Http\Controllers\ProjectController::class, 'getExportState']);
        Route::post('/{project_uuid}/export-process', [\App\Http\Controllers\ProjectController::class, 'processBatchExport']);

        // Project Bible
        Route::get('/{project_uuid}/bible', [\App\Http\Controllers\BibleController::class, 'show']);
        Route::put('/{project_uuid}/bible', [\App\Http\Controllers\BibleController::class, 'update']);
        Route::post('/{project_uuid}/bible/sync', [\App\Http\Controllers\BibleController::class, 'syncBibleWithAI']);
        Route::post('/{project_uuid}/bible/cerebellum', [\App\Http\Controllers\BibleController::class, 'syncCerebellum']);

        // AI Magic Buttons
        Route::post('/{project_uuid}/ai/card', [\App\Http\Controllers\AiController::class, 'suggestCard']);
        Route::post('/{project_uuid}/ai/planning', [\App\Http\Controllers\AiController::class, 'generatePlanning']);
        Route::post('/{project_uuid}/ai/scene', [\App\Http\Controllers\AiController::class, 'writeScene']);
    });
});

// Authentication
Route::get('/login', [\App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Web Installer (First-time server setup wizard)
Route::prefix('install')->group(function () {
    Route::get('/', [\App\Http\Controllers\InstallController::class, 'index'])->name('install');
    Route::get('/requirements', [\App\Http\Controllers\InstallController::class, 'checkRequirements']);
    Route::post('/database/test', [\App\Http\Controllers\InstallController::class, 'testDatabase']);
    Route::post('/database/configure', [\App\Http\Controllers\InstallController::class, 'configureDatabase']);
    Route::post('/admin', [\App\Http\Controllers\InstallController::class, 'createAdmin']);
    Route::post('/ai', [\App\Http\Controllers\InstallController::class, 'saveAiKey']);
    Route::post('/finalize', [\App\Http\Controllers\InstallController::class, 'finalize']);
});

// Initial Setup & Onboarding (post-install, handled by EnsureAppIsSetup)
Route::get('/dev/reset', [\App\Http\Controllers\SetupController::class, 'factoryReset'])->name('dev.reset');
Route::prefix('setup')->group(function () {
    Route::get('/', [\App\Http\Controllers\SetupController::class, 'index'])->name('setup');
    Route::post('/locale', [\App\Http\Controllers\SetupController::class, 'setLocale']);
    Route::post('/user', [\App\Http\Controllers\SetupController::class, 'createUser']);
    Route::post('/ai', [\App\Http\Controllers\SetupController::class, 'saveAiKey']);
});

Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index']);
Route::post('/settings/ai', [\App\Http\Controllers\SettingsController::class, 'updateAi']);
Route::get('/settings/backup', [\App\Http\Controllers\SettingsController::class, 'fullBackup']);
Route::post('/settings/factory-reset', [\App\Http\Controllers\SettingsController::class, 'showResetProgress']);
Route::post('/settings/factory-reset/step-files', [\App\Http\Controllers\SettingsController::class, 'stepCleanFiles']);
Route::post('/settings/factory-reset/step-database', [\App\Http\Controllers\SettingsController::class, 'stepCleanDatabase']);
Route::post('/settings/factory-reset/step-finalize', [\App\Http\Controllers\SettingsController::class, 'stepFinalize']);
Route::get('/eula', [\App\Http\Controllers\SettingsController::class, 'showEula']);
