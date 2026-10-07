<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reserves', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->constrained()->after('guest_id');

            $table->decimal('discount_total', 10, 2)->default(0)->after('coupon_id');
            $table->decimal('fee_total', 10, 2)->default(0)->after('discount_total');
            $table->decimal('final_total', 10, 2)->after('fee_total');
        });
    }

    public function down(): void
    {
        Schema::table('reserves', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_id', 'discount_total', 'fee_total', 'final_total']);
        });
    }
};
