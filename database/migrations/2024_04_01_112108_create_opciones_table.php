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
        Schema::create('opciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('icono');
            $table->string('public_id');
            $table->double('precio');
            $table->unsignedBigInteger('contenedor_id');
            $table->foreign('contenedor_id')->references('id')->on('contenedor_opciones');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opciones', function (Blueprint $table) {
            $table->dropForeign(['contenedor_id']); // Eliminar la restricción de clave externa
        });
    
        Schema::dropIfExists('opciones'); // Eliminar la tabla
    }
};
