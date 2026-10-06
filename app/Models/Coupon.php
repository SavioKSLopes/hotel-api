<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_purchase',
        'valid_from',
        'valid_until',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
            'active' => 'boolean',
            'value' => 'decimal:2',
            'min_purchase' => 'decimal:2',
        ];
    }

    public function isValid(): bool
    {
        return $this->active
            && $this->valid_from <= now()
            && $this->valid_until >= now();
    }

    public function canApplyTo(float $purchaseAmount): bool
    {

        if ($this->type === 'fixed') {
            $minimumRequired = $this->min_purchase ?? ($this->value / 0.4);
            return $purchaseAmount >= $minimumRequired;
        }

        return true;
    }

    public function getMinimumPurchaseMessage(): string
    {
        if ($this->type === 'fixed') {
            $minimumRequired = $this->min_purchase ?? ($this->value / 0.4);
            return "Este cupom só pode ser usado em compras acima de R$ " . number_format($minimumRequired, 2, ',', '.');
        }

        return "";
    }

}
