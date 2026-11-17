<?php

namespace App\\Models;

use Database\\Factories\\UserFactory;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;
use Illuminate\\Foundation\\Auth\\User as Authenticatable;
use Illuminate\\Notifications\\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Profile fields users may safely update through mass assignment.
     * Account type, status, verification and login metadata stay system-controlled.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'profile_photo',
        'district_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function ownedSchools(): HasMany
    {
        return $this->hasMany(School::class, 'owner_id');
    }

    public function savedSchools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'saved_schools')
            ->withPivot('notes')
            ->withTimestamps();
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
