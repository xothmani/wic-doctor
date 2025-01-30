<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFicheCodeToConsultationsTable extends Migration
{
    public function up()
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->string('fiche_code', 10)->nullable(); // Ajoute la colonne fiche_code

            // Définir la clé étrangère
            $table->foreign('fiche_code')->references('code')->on('fiche')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Supprimer la clé étrangère et la colonne
            $table->dropForeign(['fiche_code']);
            $table->dropColumn('fiche_code');
        });
    }
}
