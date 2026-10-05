<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Room extends Model
{

    protected $fillable = [
        'hotel_id',
        'external_id',
        'name',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    public function reserves(): HasMany
    {
        return $this->hasMany(Reserve::class);
    }
}
