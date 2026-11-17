<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class SchoolView extends Model
{
    /**
     * View records are append-only and the database supplies created_at.
     * The table intentionally has no updated_at column.
     */
    public $timestamps = false;

    protected $fillable = [
        'school_id',
        'user_id',
        'ip_address',
        'session_id',
        'referrer',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
