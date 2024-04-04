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
        Schema::create('detalles_producto_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_producto_id')->constrained()->onDelete('cascade');
            $table->string('nombre_contenedor');
            $table->string('tipo_contenedor');
            $table->string('opcion');
            $table->double('precio_opcion');
            $table->integer('cantidad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_producto_pedidos');
    }
};
