<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(CategoriaSeeder::class);
        $this->call(PromocionSeeder::class);
        $this->call(ProductoSeeder::class);
        $this->call(ContenedorOpcionesSeeder::class);
        $this->call(ContenedorOpcioneProductoSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(PedidoSeeder::class);
    }
}
