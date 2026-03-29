<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GalleryItem extends Model
{
    protected $connection = 'sqlite_project';

    protected $fillable = [
        'uuid',
        'name',
        'file_path',
        'thumb_path',
        'type', // image, etc.
        'category', // cover, illustration, character, setting, object
        'dimensions',
        'filesize',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->uuid = (string) Str::uuid();
        });
    }
}
