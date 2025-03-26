<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsultationsTable extends Migration
{
    public function up()
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->date('dateConsultation');
            $table->string('raison');
            $table->text('motif');
            $table->integer('patient_id')->nullable()->unsigned();
            $table->timestamps();
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null')->onUpdate('set null');

        });
    }

    public function down()
    {
        Schema::dropIfExists('consultations');
    }
}
