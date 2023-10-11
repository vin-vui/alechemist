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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('method');
            $table->string('ferment');
            $table->float('volume');
            $table->string('efficiency');
            $table->string('color');
            $table->string('bitterness');
            $table->string('alcohol');
            $table->integer('boil_time');
            $table->float('initial_density', 4, 3);
            $table->float('final_density', 4, 3);
            $table->float('before_boil_density')->nullable();
            $table->Float('carbonation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
