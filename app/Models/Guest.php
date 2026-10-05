<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{

    protected $fillable = [
        'name',
        'last_name',
        'phone',
    ];

    public function reserves(): HasMany
    {
        return $this->hasMany(Reserve::class);
    }
}
