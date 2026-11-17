<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolProgram extends Model
{
    protected $fillable = [
        'school_id',
        'curriculum_id',
        'name',
        'level',
        'duration',
        'description',
        'is_active',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(SchoolFee::class, 'program_id');
    }
}
