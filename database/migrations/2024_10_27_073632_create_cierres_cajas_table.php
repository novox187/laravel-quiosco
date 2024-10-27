<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cierres_caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_caja');
            $table->foreign('id_caja')->references('id')->on('cajas');
            $table->unsignedBigInteger('id_apertura');
            $table->foreign('id_apertura')->references('id')->on('aperturas_caja');
            $table->decimal('monto_final', 10, 2);
            $table->decimal('total_ventas', 10, 2);
            $table->decimal('discrepancia', 10, 2)->nullable();
            $table->unsignedBigInteger('usuario_cierre');
            $table->foreign('usuario_cierre')->references('id')->on('employees');
            $table->unsignedBigInteger('usuario_modificacion')->nullable();
            $table->foreign('usuario_modificacion')->references('id')->on('employees');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cierres_caja');
    }
};
