<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_caja');
            $table->foreign('id_caja')->references('id')->on('cajas');
            $table->unsignedBigInteger('id_apertura');
            $table->foreign('id_apertura')->references('id')->on('aperturas_caja');
            $table->unsignedBigInteger('id_pedido');
            $table->foreign('id_pedido')->references('id')->on('pedidos');
            $table->unsignedBigInteger('usuario_modificacion')->nullable();
            $table->foreign('usuario_modificacion')->references('id')->on('employees');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transacciones');
    }
};
