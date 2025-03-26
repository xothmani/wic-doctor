<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorRequestsB2BTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctor_requests_b2b', function (Blueprint $table) {
            $table->id(); // Colonne ID primaire auto-incrémentée
            $table->string('name'); // Nom du docteur
            $table->string('lastname'); // Prénom du docteur
            $table->string('email')->unique(); // Email unique
            $table->string('Phone')->nullable(); // Téléphone
            $table->unsignedBigInteger('speciality_id')->nullable(); // Référence à la spécialité
            $table->text('description')->nullable(); // Description
            $table->string('adresse')->nullable(); // Adresse
            $table->string('pays')->nullable(); // Pays
            $table->string('departement')->nullable(); // Département
            $table->string('region')->nullable(); // Région
            $table->string('gouvernorat')->nullable(); // Gouvernorat
            $table->string('ville')->nullable(); // Ville
            $table->string('type')->nullable(); // Type (ex: B2B, etc.)
            $table->string('code_parent')->nullable(); // Code parrain (utilisez ce nom exact si la DB l'exige)
            $table->string('code_doctor')->nullable(); // Code du docteur
            $table->timestamps(); // created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctor_requests_b2b');
    }
}
