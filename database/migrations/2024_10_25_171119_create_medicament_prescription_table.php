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
        Schema::create('medicament_prescription', function (Blueprint $table) {
            $table->id(); // Clé primaire de la table d'association
            $table->bigInteger('prescription_id')->unsigned(); // Clé étrangère pour prescriptions
            $table->bigInteger('medicament_CODE_PCT'); // Clé étrangère pour medicaments
            $table->string('dosage'); // Dosage du médicament
            $table->integer('nb_de_jours'); // Nombre de jours
            $table->string('horaire'); // Horaire de prise
            $table->integer('nb_de_fois'); // Nombre de fois par jour
            $table->timestamps();

            // Définir les clés étrangères
            $table->foreign('prescription_id')->references('id')->on('prescriptions')->onDelete('cascade');
            $table->foreign('medicament_CODE_PCT')->references('CODE_PCT')->on('medicaments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicament_prescription');
    }
};
