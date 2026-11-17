<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedSlot extends Model
{
    protected $fillable = [
        'school_id',
        'slot_type',
        'context_type',
        'context_id',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
