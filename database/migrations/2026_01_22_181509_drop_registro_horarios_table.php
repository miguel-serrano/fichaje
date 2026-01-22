<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina la tabla registro_horarios (obsoleta).
 *
 * La funcionalidad de registro horario ahora usa la tabla time_entries.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('registro_horarios');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('registro_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('entrada');
            $table->timestamp('salida')->nullable();
            $table->timestamps();
        });
    }
};
