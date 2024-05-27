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
            $table->dropColumn('preparado');
            $table->dropColumn('entregado');
            $table->boolean('eliminado')->default(0);
            $table->tinyInteger('estado')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->boolean('preparado');
            $table->boolean('entregado');
            $table->dropColumn('eliminado');
            $table->dropColumn('estado');
        });
    }
};
