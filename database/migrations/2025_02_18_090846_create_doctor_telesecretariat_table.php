<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorTelesecretariatTable extends Migration
{
    public function up()
    {
        Schema::create('doctor_telesecretariat', function (Blueprint $table) {
            $table->id(); // This creates the 'id' column with auto-increment
            $table->unsignedBigInteger('doctor_id'); // Matches doctors.id type
            $table->unsignedBigInteger('telesecretariat_id'); // Matches telesecretariat.id type
            $table->timestamps(); // This will create 'created_at' and 'updated_at' columns

            // Foreign key constraints
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('telesecretariat_id')->references('id')->on('telesecretariat')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_telesecretariat');
    }
}
