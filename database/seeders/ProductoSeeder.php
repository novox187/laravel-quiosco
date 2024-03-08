<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datos = [
            array(
                'nombre' =>  "big pinchos",
                'precio' => 59.9,
                'imagen' => "1709560116.jpg",
                'descripcion' => "Carne, pollo, chorizo parrillero, chorizo artesanal, camarón
                Guarnición: papas fritas/ cocidas o patacones, ensalada, salsas.",
                'disponible' => true,
                'promo_id' => null,
                'rating' => 0,
                'categoria_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'nombre' =>  "Parrillada Familiar",
                'precio' => 35,
                'imagen' => "1709560208.jpg",
                'descripcion' => "Carne, pollo, chorizo parrillero, chorizo artesanal, chuleta, camarón
                Guarnición: papas fritas/ cocidas o patacones, ensalada, salsas.",
                'disponible' => 1,
                'promo_id' => null,
                'rating' => 0,
                'categoria_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'nombre' =>  "Parrillada Personal",
                'precio' => 15,
                'imagen' => "1709560274.jpg",
                'descripcion' => "Carne, pollo, chorizo parrillero, chorizo artesanal, chuleta, camarón
                Guarnición: papas fritas/ cocidas o patacones, ensalada, salsas.",
                'disponible' => 1,
                'promo_id' => null,
                'rating' => 0,
                'categoria_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'nombre' =>  "Pechuga Rellena",
                'precio' => 15,
                'imagen' => "1709560320.jpg",
                'descripcion' => "2 suprema de pollo a la parrilla, queso, jamón
                Guarnición: papas fritas/ cocidas o patacones, ensalada, salsas.",
                'disponible' => 1,
                'promo_id' => null,
                'rating' => 0,
                'categoria_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'nombre' =>  "ALAS BBQ",
                'precio' => 7.5,
                'imagen' => "1709562067.jpg",
                'descripcion' => "12 alistas BBQ con papas fritas",
                'disponible' => 1,
                'promo_id' => null,
                'rating' => 0,
                'categoria_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'nombre' =>  "combo mixto",
                'precio' => 20,
                'imagen' => "1709562385.jpg",
                'descripcion' => "112 unidades de alitas, 4 unidades de muslo, 10 unidades decamarones,
                salsas BBQ, mostaza miel, bufalo, maracuyá
                Guarnición: papas fritas/cocidas o patacones, salsas.",
                'disponible' => 1,
                'promo_id' => null,
                'rating' => 0,
                'categoria_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'nombre' =>  "Bife",
                'precio' => 15,
                'imagen' => "1709561122.webp",
                'descripcion' => "250g",
                'disponible' => 1,
                'promo_id' => null,
                'rating' => 0,
                'categoria_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'nombre' =>  "Picaña",
                'precio' => 15,
                'imagen' => "1709561271.jpg",
                'descripcion' => "250g",
                'disponible' => 1,
                'promo_id' => null,
                'rating' => 0,
                'categoria_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'nombre' =>  "Lomo Fino",
                'precio' => 15,
                'imagen' => "1709561313.jpg",
                'descripcion' => "250g",
                'disponible' => 1,
                'promo_id' => null,
                'rating' => 0,
                'categoria_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'nombre' =>  "Rib-eye",
                'precio' => 15,
                'imagen' => "1709561404.jpg",
                'descripcion' => "250g",
                'disponible' => 1,
                'promo_id' => null,
                'rating' => 0,
                'categoria_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'nombre' =>  "Tibone",
                'precio' => 15,
                'imagen' => "1709561536.jpg",
                'descripcion' => "250g",
                'disponible' => 1,
                'promo_id' => null,
                'rating' => 0,
                'categoria_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'nombre' =>  "Baby back ribs",
                'precio' => 15,
                'imagen' => "1709561606.webp",
                'descripcion' => "250g",
                'disponible' => 1,
                'promo_id' => null,
                'rating' => 0,
                'categoria_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'nombre' =>  "Tomahawk",
                'precio' => 15,
                'imagen' => "1709561778.jpg",
                'descripcion' => "250g",
                'disponible' => 1,
                'promo_id' => null,
                'rating' => 0,
                'categoria_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
        ];

        DB::table('productos')->insert($datos);
    }
}
