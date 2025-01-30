<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToFicheTable extends Migration
{
    public function up()
    {
        Schema::table('fiche', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('set null');
        });
    }

    public function down()
    {
        Schema::table('fiche', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}
