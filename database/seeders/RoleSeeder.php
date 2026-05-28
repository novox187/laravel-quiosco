<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('roles')->insert([
            [
                'rol' => 'admin',
                'eliminar' => true,
                'editar' => true,
                'ver' => true,
                'preparar_pedidos' => true,
                'entregar_pedidos' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'rol' => 'mesero',
                'eliminar' => false,
                'editar' => false,
                'ver' => true,
                'preparar_pedidos' => false,
                'entregar_pedidos' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'rol' => 'cocinero',
                'eliminar' => false,
                'editar' => false,
                'ver' => true,
                'preparar_pedidos' => true,
                'entregar_pedidos' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'rol' => 'repartidor',
                'eliminar' => false,
                'editar' => false,
                'ver' => true,
                'preparar_pedidos' => false,
                'entregar_pedidos' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'rol' => 'cajero',
                'eliminar' => false,
                'editar' => true,
                'ver' => true,
                'preparar_pedidos' => false,
                'entregar_pedidos' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
