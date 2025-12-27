<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('registro_horarios', function (Blueprint $table) {
            $table->id();
            $table->char('user_id', 36);
            $table->timestamp('entrada');
            $table->timestamp('salida')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('registro_horarios');
    }
};

