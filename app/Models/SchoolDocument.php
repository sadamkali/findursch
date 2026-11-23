<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolDocument extends Model
{
    protected $fillable = [
        'school_id',
        'uploaded_by',
        'title',
        'description',
        'original_filename',
        'stored_path',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
