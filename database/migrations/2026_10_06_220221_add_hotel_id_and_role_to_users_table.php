<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('receptionist')->after('hotel_id');
            $table->boolean('is_active')->default(true)->after('role');

            $table->index(['hotel_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['hotel_id', 'role']);
            $table->dropForeign(['hotel_id']);
            $table->dropColumn(['hotel_id', 'role', 'is_active']);
        });
    }
};
