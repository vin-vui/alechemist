<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chrono des paliers d'empâtage.
 *
 * L'ébullition porte son chrono sur le brassin (`brewings.boil_start`), parce
 * qu'elle est unique. L'empâtage enchaîne plusieurs paliers de durées
 * différentes : l'horodatage descend donc au palier. Nullable, comme
 * `boil_start` : un palier non démarré n'a pas d'heure de début.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brewing_steps', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable()->after('time');
        });
    }

    public function down(): void
    {
        Schema::table('brewing_steps', function (Blueprint $table) {
            $table->dropColumn('started_at');
        });
    }
};
