<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContenedorOpcionesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $contenedores = [
            ['nombre' => 'Guarnición',       'tipo' => 'unico'],
            ['nombre' => 'Bebida incluida',  'tipo' => 'unico'],
            ['nombre' => 'Punto de cocción', 'tipo' => 'unico'],
            ['nombre' => 'Salsas',           'tipo' => 'multiple'],
            ['nombre' => 'Extras',           'tipo' => 'multiple'],
        ];

        $contenedorIds = [];
        foreach ($contenedores as $c) {
            $contenedorIds[$c['nombre']] = DB::table('contenedor_opciones')->insertGetId([
                'nombre' => $c['nombre'],
                'tipo' => $c['tipo'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $opciones = [
            // Guarnición (unico)
            ['nombre' => 'Papas fritas',  'precio' => 0, 'contenedor' => 'Guarnición'],
            ['nombre' => 'Papas cocidas', 'precio' => 0, 'contenedor' => 'Guarnición'],
            ['nombre' => 'Patacones',     'precio' => 0, 'contenedor' => 'Guarnición'],
            ['nombre' => 'Arroz blanco',  'precio' => 0, 'contenedor' => 'Guarnición'],
            ['nombre' => 'Ensalada',      'precio' => 0, 'contenedor' => 'Guarnición'],

            // Bebida incluida (unico)
            ['nombre' => 'Gaseosa cola',    'precio' => 0, 'contenedor' => 'Bebida incluida'],
            ['nombre' => 'Gaseosa naranja', 'precio' => 0, 'contenedor' => 'Bebida incluida'],
            ['nombre' => 'Agua sin gas',    'precio' => 0, 'contenedor' => 'Bebida incluida'],
            ['nombre' => 'Jugo natural',    'precio' => 0.50, 'contenedor' => 'Bebida incluida'],

            // Punto de cocción (unico)
            ['nombre' => 'Bien cocido',  'precio' => 0, 'contenedor' => 'Punto de cocción'],
            ['nombre' => 'Tres cuartos', 'precio' => 0, 'contenedor' => 'Punto de cocción'],
            ['nombre' => 'Medio',        'precio' => 0, 'contenedor' => 'Punto de cocción'],
            ['nombre' => 'Jugoso',       'precio' => 0, 'contenedor' => 'Punto de cocción'],

            // Salsas (multiple)
            ['nombre' => 'BBQ',          'precio' => 0, 'contenedor' => 'Salsas'],
            ['nombre' => 'Mostaza miel', 'precio' => 0, 'contenedor' => 'Salsas'],
            ['nombre' => 'Búfalo',       'precio' => 0, 'contenedor' => 'Salsas'],
            ['nombre' => 'Ranch',        'precio' => 0, 'contenedor' => 'Salsas'],
            ['nombre' => 'Ketchup',      'precio' => 0, 'contenedor' => 'Salsas'],
            ['nombre' => 'Mayonesa',     'precio' => 0, 'contenedor' => 'Salsas'],
            ['nombre' => 'Ají picante',  'precio' => 0, 'contenedor' => 'Salsas'],

            // Extras (multiple) - con costo adicional
            ['nombre' => 'Queso extra',  'precio' => 1.00, 'contenedor' => 'Extras'],
            ['nombre' => 'Tocino extra', 'precio' => 1.50, 'contenedor' => 'Extras'],
            ['nombre' => 'Aguacate',     'precio' => 1.50, 'contenedor' => 'Extras'],
            ['nombre' => 'Huevo frito',  'precio' => 1.00, 'contenedor' => 'Extras'],
            ['nombre' => 'Cebolla caramelizada', 'precio' => 0.75, 'contenedor' => 'Extras'],
        ];

        foreach ($opciones as $o) {
            DB::table('opciones')->insert([
                'nombre' => $o['nombre'],
                'precio' => $o['precio'],
                'contenedor_id' => $contenedorIds[$o['contenedor']],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
