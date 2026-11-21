<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolPost extends Model
{
    protected $fillable = [
        'school_id', 'created_by', 'type', 'title', 'description', 'image_url', 'image_alt', 'venue',
        'starts_at', 'ends_at', 'deadline', 'award', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'deadline' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
