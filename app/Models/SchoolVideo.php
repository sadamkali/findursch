<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolVideo extends Model
{
    protected $fillable = ['school_id', 'created_by', 'title', 'youtube_url', 'youtube_video_id', 'status'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
