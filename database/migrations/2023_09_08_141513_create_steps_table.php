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
        Schema::create('steps', function (Blueprint $table) {
            $table->id();
            $table->float('quantity');
            $table->enum('unit', ['g', 'l', '°C']);
            $table->string('field');
            $table->integer('time')->nullable();
            $table->enum('type', ['Preparation', 'Mash', 'Rinse', 'Boil', 'Yeast', 'Primary', 'Secondary', 'Tertiary', 'Bottle', 'Dry Hop', 'Aroma']);
            $table->foreignId('recipe_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('steps');
    }
};
