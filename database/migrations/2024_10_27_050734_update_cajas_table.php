<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            // Eliminar la columna "dinero"
            if (Schema::hasColumn('cajas', 'dinero')) {
                $table->dropColumn('dinero');
            }

            // Cambiar el tipo de dato de la columna "identificador" a texto y luego renombrar a "nombre_caja"
            $table->string('identificador')->change();
            $table->renameColumn('identificador', 'nombre_caja');

            // Eliminar la columna "registro_id" y su clave foránea
            if (Schema::hasColumn('cajas', 'registro_id')) {
                $table->dropForeign(['registro_id']);
                $table->dropColumn('registro_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            // Agregar de vuelta la columna "dinero"
            $table->double('dinero')->nullable();

            // Renombrar la columna "nombre_caja" a "identificador" y cambiar su tipo a integer
            $table->renameColumn('nombre_caja', 'identificador');
            $table->integer('identificador')->change();

            // Agregar de vuelta la columna "registro_id"
            $table->foreignId('registro_id')->nullable()->constrained();
        });
    }
};
