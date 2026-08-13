<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill avant l'ALTER : en MySQL strict, passer une colonne en NOT NULL
        // echoue (erreur 1138) si une seule ligne existante porte un NULL. Deux
        // update cibles par colonne (plutot qu'un DB::raw('COALESCE(..., NOW())'))
        // pour rester portable SQLite/MySQL (NOW() n'existe pas sous SQLite, utilise
        // par les tests) et ne jamais ecraser une valeur deja presente.
        $now = now();
        DB::table('recipes')->whereNull('created_at')->update(['created_at' => $now]);
        DB::table('recipes')->whereNull('updated_at')->update(['updated_at' => $now]);
        DB::table('brewings')->whereNull('created_at')->update(['created_at' => $now]);
        DB::table('brewings')->whereNull('updated_at')->update(['updated_at' => $now]);

        Schema::table('recipes', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable(false)->change();
            $table->timestamp('updated_at')->nullable(false)->change();
        });
        Schema::table('brewings', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable(false)->change();
            $table->timestamp('updated_at')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });
        Schema::table('brewings', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });
    }
};
