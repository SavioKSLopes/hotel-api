<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $fillable = [
        'external_id',
        'name',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
