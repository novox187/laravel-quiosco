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
        Schema::create('detalles_entregas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->boolean('favorito')->default(0);
            $table->boolean('eliminado')->default(0);
            $table->string('distancia_km', 10)->nullable();
            $table->string('distancia_fuera_radio', 10)->nullable();
            $table->decimal('precio_total', 8, 2)->nullable();
            $table->string('telefono', 15);
            $table->text('comentario')->nullable();
            $table->text('direccion_mapa')->nullable();
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_entregas');
    }
};
