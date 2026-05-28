<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $password = Hash::make('password123');

        $users = [
            ['name' => 'Carlos Administrador',  'email' => 'admin@quiosco.com',     'rol' => 'admin'],
            ['name' => 'Maria Mesera',          'email' => 'maria@quiosco.com',     'rol' => 'mesero'],
            ['name' => 'Luis Mesero',           'email' => 'luis@quiosco.com',      'rol' => 'mesero'],
            ['name' => 'Pedro Cocinero',        'email' => 'pedro@quiosco.com',     'rol' => 'cocinero'],
            ['name' => 'Ana Cocinera',          'email' => 'ana@quiosco.com',       'rol' => 'cocinero'],
            ['name' => 'Jorge Repartidor',      'email' => 'jorge@quiosco.com',     'rol' => 'repartidor'],
            ['name' => 'Diego Repartidor',      'email' => 'diego@quiosco.com',     'rol' => 'repartidor'],
            ['name' => 'Sofia Cajera',          'email' => 'sofia@quiosco.com',     'rol' => 'cajero'],
        ];

        $rolesMap = DB::table('roles')->pluck('id', 'rol');

        foreach ($users as $u) {
            $userId = DB::table('users')->insertGetId([
                'name' => $u['name'],
                'email' => $u['email'],
                'email_verified_at' => $now,
                'password' => $password,
                'calificacion' => rand(3, 5),
                'estado' => 'activo',
                'eliminado' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $rolesMap[$u['rol']],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
