<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReserveDaily extends Model
{

    protected $fillable = [
        'id',
        'reserve_id',
        'date',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'value' => 'decimal:2',
        ];
    }

    public function reserve():belongsTo
    {
        return $this->belongsTo(Reserve::class);
    }
}
