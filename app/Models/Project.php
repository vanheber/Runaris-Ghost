<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'uuid',
        'description',
        'last_opened_at',
    ];

    protected $casts = [
        'last_opened_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            $project->uuid = (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function getStoragePath(string $subpath = '')
    {
        return storage_path("app/projects/{$this->uuid}/" . ltrim($subpath, '/'));
    }

    public function getDatabasePath()
    {
        return $this->getStoragePath('database.sqlite');
    }
}
