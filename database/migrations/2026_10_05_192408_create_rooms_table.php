<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')
                ->constrained('hotels')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('external_id', 50);
            $table->string('name');
            $table->timestamps();

            $table->unique(
                ['hotel_id', 'external_id'],
                'uq_rooms_hotel_external_id'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
