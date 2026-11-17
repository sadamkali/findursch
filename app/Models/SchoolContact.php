<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolContact extends Model
{
    protected $fillable = [
        'school_id',
        'type',
        'value',
        'label',
        'is_primary',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
