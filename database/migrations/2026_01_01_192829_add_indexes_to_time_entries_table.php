<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            // Índice para buscar entradas abiertas por usuario (consulta muy frecuente)
            $table->index(['user_id', 'salida'], 'idx_user_open_entries');

            // Índice para consultas por rango de fechas
            $table->index(['entrada'], 'idx_entrada_date');
            $table->index(['salida'], 'idx_salida_date');

            // Índice compuesto para consultas de usuario por fecha
            $table->index(['user_id', 'entrada'], 'idx_user_entrada');

            // Índice para consultas de reportes diarios/mensuales
            $table->index(['entrada', 'salida'], 'idx_date_range');

            // Índice para consultas de tiempo trabajado por usuario
            $table->index(['user_id', 'entrada', 'salida'], 'idx_user_time_range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropIndex('idx_user_open_entries');
            $table->dropIndex('idx_entrada_date');
            $table->dropIndex('idx_salida_date');
            $table->dropIndex('idx_user_entrada');
            $table->dropIndex('idx_date_range');
            $table->dropIndex('idx_user_time_range');
        });
    }
};
