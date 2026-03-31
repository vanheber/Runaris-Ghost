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
        'image_uuid',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    /**
     * Cards that are connected to this card.
     */
    public function connections()
    {
        return $this->belongsToMany(Card::class, 'card_connections', 'card_uuid', 'related_card_uuid', 'uuid', 'uuid')
                    ->withPivot('metadata')
                    ->withTimestamps();
    }
}
