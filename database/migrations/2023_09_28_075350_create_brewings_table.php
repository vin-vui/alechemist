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
        Schema::create('brewings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('volume')->nullable();
            $table->string('alcohol')->nullable();
            $table->float('initial_density', 4, 3)->nullable();
            $table->float('final_density', 4, 3)->nullable();
            $table->float('before_boil_density')->nullable();
            $table->float('carbonation')->nullable();
            $table->date('date_start');
            $table->text('note');
            $table->foreignId('recipe_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brewings');
    }
};
