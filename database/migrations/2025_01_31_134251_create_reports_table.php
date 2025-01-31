<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
	    $table->string('fiche_id', 10);
	    $table->string('title');
	    $table->text('description')->nullable();
            $table->string('file_path');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('fiche_id')->references('code')->on('fiche')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('reports');
    }
};

