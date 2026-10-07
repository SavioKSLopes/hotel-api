<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('hotel_id')->after('reserve_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending')->after('value');
            $table->string('external_reference')->nullable()->after('status');
            $table->json('metadata')->nullable()->after('external_reference');
            $table->timestamp('paid_at')->nullable()->after('metadata');
            $table->index('hotel_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('hotel_id');
            $table->dropIndex('status');
            $table->dropForeign(['hotel_id']);
            $table->dropColumn([
                'hotel_id',
                'status',
                'external_reference',
                'metadata',
                'paid_at',
            ]);
        });
    }
};
