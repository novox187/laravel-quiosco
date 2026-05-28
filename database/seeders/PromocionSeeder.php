<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromocionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('promociones')->insert([
            ['nombre' => 'Descuento Estudiantes', 'descuento' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Happy Hour',            'descuento' => 15, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => '2x1 Cocteles',          'descuento' => 50, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Combo Familiar',        'descuento' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Promo Cumpleaños',      'descuento' => 25, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
