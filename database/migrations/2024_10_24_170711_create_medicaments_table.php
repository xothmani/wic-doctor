<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('medicaments', function (Blueprint $table) {
            $table->bigInteger('CODE_PCT')->primary(); // Définit CODE_PCT comme clé primaire
            $table->string('NOM_COMMERCIAL');
            $table->string('PRIX_PUBLIC');
            $table->string('TARIF_REFERENCE'); 
            $table->string('CATEGORIE');
            $table->string('DCI'); // Dénomination Commune Internationale
            $table->string('AP');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicaments');
    }
};
