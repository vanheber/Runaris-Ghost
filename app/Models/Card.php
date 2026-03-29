<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $connection = 'sqlite_project';

    protected $fillable = [
        'uuid',
        'type',
        'title',
        'summary',
        'file_path',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];
}
