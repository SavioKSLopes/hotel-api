<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reserve_dailies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reserve_id')
                ->constrained('reserves')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->date('date');
            $table->decimal('value', 10, 2);
            $table->timestamps();

            $table->unique(
                ['reserve_id', 'date'],
                'uq_reserve_dailies_reserve_date'
            );
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserve_dailies');
    }
};
