<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * School records store their school type in schools.type_id.
     * Specify the foreign key explicitly so relationship counts and eager
     * loading never assume the conventional school_type_id column.
     */
    public function schools(): HasMany
    {
        return $this->hasMany(School::class, 'type_id');
    }
}
