<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolFee extends Model
{
    protected $fillable = [
        'school_id',
        'program_id',
        'category',
        'label',
        'amount',
        'currency',
        'period',
        'academic_year',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(SchoolProgram::class, 'program_id');
    }
}
