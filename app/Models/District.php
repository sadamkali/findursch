<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $fillable = [
        'region_id',
        'name',
        'slug',
        'school_count',
        'is_featured',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public function counties(): HasMany
    {
        return $this->hasMany(County::class);
    }

    public function subCounties(): HasMany
    {
        return $this->hasMany(SubCounty::class);
    }
}
