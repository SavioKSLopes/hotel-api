<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $fillable = [
        'name',
        'type',
        'value',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'value' => 'decimal:2',
        ];
    }
}
