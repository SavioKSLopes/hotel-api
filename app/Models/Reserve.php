<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reserve extends Model
{

    protected $fillable = [
        'external_id',
        'hotel_id',
        'room_id',
        'guest_id',
        'check_in',
        'check_out',
        'total',
        'coupon_id',
        'discount_total',
        'fee_total',
        'final_total'
    ];

    protected function casts():array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'fee_total' => 'decimal:2',
            'final_total' => 'decimal:2',
        ];
    }

    public function hotel():belongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function room():belongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function guest():belongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function reserveDailies():HasMany
    {
        return $this->hasMany(ReserveDaily::class);
    }

    public function payments():HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
