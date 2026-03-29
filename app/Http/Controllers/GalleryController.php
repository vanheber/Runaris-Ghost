<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GalleryController extends Controller
{
    protected $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function index($project_uuid)
    {
        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $items = GalleryItem::orderBy('created_at', 'desc')->get();
        
        return response()->json($items);
    }

    public function store(Request $request, $project_uuid)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // max 10MB
            'category' => 'nullable|string'
        ]);

        $project = Project::where('uuid', $project_uuid)->firstOrFail();
        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = $baseName . '-' . time() . '.' . $extension;
        $thumbName = 'thumb-' . $fileName;

        $projectPath = "projects/{$project->uuid}/assets";
        Storage::disk('local')->makeDirectory($projectPath);

        // Process Main Image (Kindle size: max 1200px longest side)
        $img = $this->imageManager->decode($file->getRealPath());
        $width = $img->width();
        $height = $img->height();
        
        if ($width > 1200 || $height > 1200) {
            $img->scaleDown(1200, 1200);
        }

        $fullPath = storage_path("app/{$projectPath}/{$fileName}");
        $img->save($fullPath);

        // Process Thumbnail (200x200 cover)
        $thumb = $this->imageManager->decode($file->getRealPath());
        $thumb->cover(200, 200);
        $thumbPath = storage_path("app/{$projectPath}/{$thumbName}");
        $thumb->save($thumbPath);

        $galleryItem = GalleryItem::create([
            'name' => $file->getClientOriginalName(),
            'file_path' => "{$projectPath}/{$fileName}",
            'thumb_path' => "{$projectPath}/{$thumbName}",
            'category' => $request->category ?? 'illustration',
            'dimensions' => "{$img->width()}x{$img->height()}",
            'filesize' => filesize($fullPath),
        ]);

        return response()->json($galleryItem);
    }

    public function destroy($project_uuid, $item_uuid)
    {
        $item = GalleryItem::where('uuid', $item_uuid)->firstOrFail();
        
        $files = [$item->file_path, $item->thumb_path];

        foreach ($files as $f) {
            if ($f) {
                $p = storage_path("app/{$f}");
                if (file_exists($p)) {
                    unlink($p);
                }
            }
        }

        $item->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Update gallery item details.
     */
    public function update(Request $request, $project_uuid, $item_uuid)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $item = GalleryItem::where('uuid', $item_uuid)->firstOrFail();
        $item->update(['name' => $request->name]);

        return response()->json($item);
    }

    /**
     * Helper to get full storage URL (via temporary or symbolic route)
     */
    public function showImage($project_uuid, $item_uuid, $type = 'full')
    {
        $item = GalleryItem::where('uuid', $item_uuid)->first();
        if (!$item) abort(404);
        
        $path = $type === 'thumb' ? $item->thumb_path : $item->file_path;
        $fullPath = storage_path("app/{$path}");

        if (!file_exists($fullPath)) abort(404);
        
        return response()->file($fullPath);
    }
}
