<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContenedorOpcioneProductoSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $contenedores = DB::table('contenedor_opciones')->pluck('id', 'nombre');
        $productos = DB::table('productos')->select('id', 'nombre', 'categoria_id')->get();

        $reglas = [
            'especial'             => ['Guarnición', 'Punto de cocción', 'Salsas', 'Extras'],
            'arroz'                => ['Salsas', 'Extras'],
            'parrilladas'          => ['Guarnición', 'Punto de cocción', 'Salsas'],
            'personal'             => ['Guarnición', 'Salsas', 'Extras'],
            'combo-amigos'         => ['Salsas', 'Bebida incluida'],
            'porciones'            => ['Salsas'],
            'bebidas-naturales'    => [],
            'bebidas-frias'        => [],
            'bebidas-con-alcohol'  => [],
            'cocteles'             => [],
        ];

        $categorias = DB::table('categorias')->pluck('nombre', 'id');

        $rows = [];
        foreach ($productos as $p) {
            $categoria = $categorias[$p->categoria_id] ?? null;
            $contenedoresAplicables = $reglas[$categoria] ?? [];

            foreach ($contenedoresAplicables as $nombreContenedor) {
                if (isset($contenedores[$nombreContenedor])) {
                    $rows[] = [
                        'contenedor_opcione_id' => $contenedores[$nombreContenedor],
                        'producto_id' => $p->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (!empty($rows)) {
            DB::table('contenedor_opcione_producto')->insert($rows);
        }
    }
}
