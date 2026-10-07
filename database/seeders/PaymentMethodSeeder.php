<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Credit Card',
                'external_id' => '1',
            ],
            [
                'name' => 'Debit Card',
                'external_id' => '2',
            ],
            [
                'name' => 'PIX',
                'external_id' => '3',
            ],
            [
                'name' => 'Boleto',
                'external_id' => '4',
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['name' => $method['name']],
                $method
            );
        }
    }
}
