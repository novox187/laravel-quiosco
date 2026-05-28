<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categorias')->insert([
            'nombre' => 'especial',
            'public_id' => 'especial',
            'icono' => 'icono_especial.svg',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        
        DB::table('categorias')->insert([
            'nombre' => 'arroz',
            'public_id' => 'arroz',
            'icono' => 'icono_arroz.svg',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('categorias')->insert([
            'nombre' => 'parrilladas',
            'public_id' => 'parrilladas',
            'icono' => 'icono_parrilladas.svg',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('categorias')->insert([
            'nombre' => 'personal',
            'public_id' => 'personal',
            'icono' => 'icono_personal.svg',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('categorias')->insert([
            'nombre' => 'combo-amigos',
            'public_id' => 'combo-amigos',
            'icono' => 'icono_combo-amigos.svg',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('categorias')->insert([
            'nombre' => 'porciones',
            'public_id' => 'porciones',
            'icono' => 'icono_porciones.svg',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('categorias')->insert([
            'nombre' => 'bebidas-naturales',
            'public_id' => 'bebidas-naturales',
            'icono' => 'icono_bebidas-naturales.svg',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('categorias')->insert([
            'nombre' => 'bebidas-frias',
            'public_id' => 'bebidas-frias',
            'icono' => 'icono_bebidas-frias.svg',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('categorias')->insert([
            'nombre' => 'bebidas-con-alcohol',
            'public_id' => 'bebidas-con-alcohol',
            'icono' => 'icono_bebidas-con-alcohol.svg',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('categorias')->insert([
            'nombre' => 'cocteles',
            'public_id' => 'cocteles',
            'icono' => 'icono_cocteles.svg',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        

        
    }
}
