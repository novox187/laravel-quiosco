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
        Schema::table('pedidos', function (Blueprint $table) {
            $table->unsignedBigInteger('detalle_entrega_id')->nullable(); // Usa unsignedBigInteger
            $table->foreign('detalle_entrega_id')->references('id')->on('detalles_entregas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['detalle_entrega_id']); // Elimina primero la clave foránea
            $table->dropColumn('detalle_entrega_id');    // Luego elimina la columna
        });
    }
};
