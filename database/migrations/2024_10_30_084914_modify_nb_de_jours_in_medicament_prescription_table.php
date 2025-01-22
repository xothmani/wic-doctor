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
        Schema::table('medicament_prescription', function (Blueprint $table) {
            $table->string('nb_de_jours')->change(); // Change 'nb_de_jours' to VARCHAR
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicament_prescription', function (Blueprint $table) {
            $table->integer('nb_de_jours')->change(); // Revert 'nb_de_jours' back to integer if rolled back
        });
    }
};
