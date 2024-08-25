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
        Schema::create('employees', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental
            $table->string('first_name'); // Nombre del empleado
            $table->string('last_name'); // Apellido del empleado
            $table->string('email')->unique(); // Correo electrónico único del empleado
            $table->string('phone')->nullable(); // Número de teléfono del empleado (opcional)
            $table->decimal('salary', 10, 2); // Salario del empleado
            $table->string('position'); // Puesto de trabajo del empleado
            $table->string('department'); // Departamento al que pertenece el empleado
            $table->text('address')->nullable(); // Dirección del empleado (opcional)
            $table->date('hire_date'); // Fecha de contratación del empleado
            $table->boolean('active')->default(true); // Estado activo del empleado
            $table->string('username')->unique(); // Nombre de usuario único para el empleado
            $table->string('password'); // Contraseña del empleado
            $table->timestamps(); // Marcas de tiempo created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
