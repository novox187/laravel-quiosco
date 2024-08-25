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
        Schema::create('registros', function (Blueprint $table) {
            $table->id();
            $table->string('accion');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias');
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->foreignId('contenedor_id')->nullable()->constrained('contenedor_opciones');
            $table->foreignId('promocion_id')->nullable()->constrained('promociones');
            $table->text('detalle')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros');
    }
};
