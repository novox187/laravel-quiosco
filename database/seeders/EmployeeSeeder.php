<?php

namespace Database\Seeders;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'first_name' => 'Carlos',
                'last_name' => 'Andrade',
                'email' => 'carlos.andrade@quiosco.com',
                'phone' => '0991234567',
                'salary' => 1500.00,
                'position' => 'Gerente General',
                'department' => 'Administración',
                'address' => 'Av. Amazonas 123, Quito',
                'hire_date' => Carbon::parse('2023-01-15'),
                'username' => 'cadmin',
                'password' => 'password123',
                'rol' => 'admin',
            ],
            [
                'first_name' => 'María',
                'last_name' => 'Pérez',
                'email' => 'maria.perez@quiosco.com',
                'phone' => '0987654321',
                'salary' => 600.00,
                'position' => 'Mesera',
                'department' => 'Atención al Cliente',
                'address' => 'Av. 6 de Diciembre 456, Quito',
                'hire_date' => Carbon::parse('2023-06-01'),
                'username' => 'mperez',
                'password' => 'password123',
                'rol' => 'mesero',
            ],
            [
                'first_name' => 'Pedro',
                'last_name' => 'Gómez',
                'email' => 'pedro.gomez@quiosco.com',
                'phone' => '0976543210',
                'salary' => 900.00,
                'position' => 'Chef de Cocina',
                'department' => 'Cocina',
                'address' => 'Av. Eloy Alfaro 789, Quito',
                'hire_date' => Carbon::parse('2022-11-20'),
                'username' => 'pgomez',
                'password' => 'password123',
                'rol' => 'cocinero',
            ],
            [
                'first_name' => 'Jorge',
                'last_name' => 'Vásquez',
                'email' => 'jorge.vasquez@quiosco.com',
                'phone' => '0965432109',
                'salary' => 550.00,
                'position' => 'Repartidor',
                'department' => 'Entregas',
                'address' => 'Av. La Prensa 234, Quito',
                'hire_date' => Carbon::parse('2024-02-10'),
                'username' => 'jvasquez',
                'password' => 'password123',
                'rol' => 'repartidor',
            ],
            [
                'first_name' => 'Sofía',
                'last_name' => 'Núñez',
                'email' => 'sofia.nunez@quiosco.com',
                'phone' => '0954321098',
                'salary' => 700.00,
                'position' => 'Cajera',
                'department' => 'Administración',
                'address' => 'Av. Naciones Unidas 567, Quito',
                'hire_date' => Carbon::parse('2023-09-05'),
                'username' => 'snunez',
                'password' => 'password123',
                'rol' => 'cajero',
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
