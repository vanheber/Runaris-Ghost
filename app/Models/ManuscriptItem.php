<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ManuscriptItem extends Model
{
    use HasFactory;

    protected $connection = 'sqlite_project';

    protected $fillable = [
        'uuid',
        'parent_uuid',
        'type',
        'title',
        'order',
        'content_updated_at',
        'word_count'
    ];

    public function parent()
    {
        return $this->belongsTo(ManuscriptItem::class, 'parent_uuid', 'uuid');
    }

    public function children()
    {
        return $this->hasMany(ManuscriptItem::class, 'parent_uuid', 'uuid')->orderBy('order');
    }
}
