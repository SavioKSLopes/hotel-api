<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserves', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 50)->unique();

            $table->foreignId('hotel_id')
                ->constrained('hotels')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('room_id')
                ->constrained('rooms')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('guest_id')
                ->constrained('guests')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->date('check_in');
            $table->date('check_out');
            $table->decimal('total', 10, 2);
            $table->timestamps();

            $table->index('hotel_id', 'idx_reserves_hotel_id');
            $table->index('guest_id', 'idx_reserves_guest_id');
            $table->index(
                ['room_id', 'check_in', 'check_out'],
                'idx_reserves_room_dates'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserves');
    }
};
