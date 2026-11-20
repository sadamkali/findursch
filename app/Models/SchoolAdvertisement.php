<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolAdvertisement extends Model
{
    protected $fillable = [
        'school_id', 'created_by', 'title', 'placement', 'destination_url',
        'description', 'status', 'starts_at', 'ends_at', 'image_url', 'image_alt', 'cta_label', 'is_platform_ad',
    ];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
