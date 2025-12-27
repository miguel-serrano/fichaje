<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('registro_horarios', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->timestamp('entrada');
            $table->timestamp('salida')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('registro_horarios');
    }
};
