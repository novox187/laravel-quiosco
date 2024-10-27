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
        Schema::create('aperturas_caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_caja');
            $table->foreign('id_caja')->references('id')->on('cajas');
            $table->decimal('monto_inicial', 10, 2);
            $table->unsignedBigInteger('usuario_apertura');
            $table->unsignedBigInteger('usuario_modificacion')->nullable();
            $table->timestamps();
        });
    }

    public function down():void
    {
        Schema::dropIfExists('aperturas_caja');
    }
};
