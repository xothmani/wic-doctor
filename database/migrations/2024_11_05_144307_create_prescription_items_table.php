<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrescriptionItemsTable extends Migration
{
    /**
     * Exécuter la migration.
     */
    public function up(): void
    {
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id(); // ID de l'élément
            $table->bigInteger('prescription_id')->unsigned(); // Clé étrangère vers la table des prescriptions
            $table->string('type');  // Type de l'élément: 'radio', 'analyse', 'vaccin', 'autre'
            $table->string('name');  // Nom ou description de l'élément
            $table->timestamps();  // Les timestamps (created_at et updated_at)

            // Définir la contrainte de clé étrangère
            $table->foreign('prescription_id')
                ->references('id')
                ->on('prescriptions')
                ->onDelete('cascade')  // Si la prescription est supprimée, supprimer les éléments associés
                ->onUpdate('cascade');  // Cascade lors de la mise à jour (si nécessaire)
        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
}
