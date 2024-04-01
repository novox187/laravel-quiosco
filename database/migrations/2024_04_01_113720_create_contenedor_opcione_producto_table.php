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
        Schema::create('contenedor_opcione_producto', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('contenedor_opcione_id');
            $table->unsignedBigInteger('producto_id');
            // Otros campos si son necesarios

            $table->foreign('contenedor_opcione_id')->references('id')->on('contenedor_opciones')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');

            // Restricción de clave única para producto_id
            $table->unique('producto_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contenedor_opcione_producto');
    }
};
