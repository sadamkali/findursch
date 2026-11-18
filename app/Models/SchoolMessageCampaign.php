<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolMessageCampaign extends Model
{
    protected $fillable = [
        'school_id', 'created_by', 'name', 'channel', 'audience',
        'recipients', 'message', 'status', 'provider_reference', 'provider_error',
    ];

    protected function casts(): array
    {
        return ['recipients' => 'array'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
