<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{

    protected $fillable = [
        'external_id',
        'name',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'hotel_id');
    }

    public function reserves(): HasMany
    {
        return $this->hasMany(Reserve::class, 'hotel_id');
    }
}
