<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFicheTable extends Migration
{
    public function up()
    {
        Schema::create('fiche', function (Blueprint $table) {
            $table->string('code', 10)->primary(); // Ajout de la clé primaire personnalisée
            $table->integer('patient_id')->nullable()->unsigned();
            $table->timestamps();
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null')->onUpdate('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('fiche');
    }
}
