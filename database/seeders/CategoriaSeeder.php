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
            'nombre' => 'Parrilladas',
            'icono' => 'icono_1709560050.svg',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        
        DB::table('categorias')->insert([
            'nombre' => 'Cortes de Carne',
            'icono' => 'icono_1709561002.svg',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        

        
    }
}
