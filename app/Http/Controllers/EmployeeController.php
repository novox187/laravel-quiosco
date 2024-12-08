<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginEmployeeRequest;
use App\Http\Requests\RegisterEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $employes = Employee::all();
        $usuariosConRol = [];

        foreach ($employes as $employee) {
            $rol = $employee->roles()->select('roles.id as role_id', 'roles.rol')->first();
            $usuarioConRol = [
                'id' => $employee->id,
                'name' => $employee->first_name,
                'role' => $rol->rol ? $rol->rol : "sin asignar",
                'email' => $employee->email,
                'avatar' => 'https://res.cloudinary.com/dfrsffngq/image/upload/v1717141893/rc7kawc9b2uhopdj8z5i.png',
                'status' => $employee->active
            ];
            $usuariosConRol[] = $usuarioConRol;
        }

        return $usuariosConRol;
    }
    public function Employeeforid(Employee $employee)
    {
        // Obtener el primer rol asignado al empleado con solo id y rol
        $rol = $employee->roles()->first();

        // Añadir el rol al empleado para la respuesta, si tiene alguno asignado
        if ($rol) {
            $employee->setAttribute('rol', $rol);
        }

        return response()->json($employee);
    }


    public function login(LoginEmployeeRequest $request)
    {
        $data = $request->validated();

        // Buscar al empleado por el correo electrónico y contraseña
        $employee = Employee::where('email', $data['email'])->first();
        $rol = $employee->roles()->first();

        if (!$employee || !password_verify($data['password'], $employee->password)) {
            return response([
                'errors' => ['El correo electrónico o la contraseña son incorrectos']
            ], 422);
        }

        // Autenticar al empleado y generar un token
        $token = $employee->createToken('token')->plainTextToken;

        // Devolver el token y la información del empleado
        return [
            'token' => $token,
            'id' => $employee->id,
            'name' => $employee->first_name,
            'rol' => $rol->rol,
            'email' => $employee->email,
            'avatar' => 'https://res.cloudinary.com/dfrsffngq/image/upload/v1717141893/rc7kawc9b2uhopdj8z5i.png',
            'status' => 'activo'
        ];
    }

    public function register(RegisterEmployeeRequest $request)
    {
        $data = $request->validated();

        // Crear el empleado
        $employee = Employee::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'salary' => $data['salary'],
            'position' => $data['position'],
            'department' => $data['department'],
            'address' => $data['address'] ?? null,
            'hire_date' => $data['hire_date'],
            'active' => true,
            'username' => $data['username'],
            'password' => bcrypt($data['password']),
        ]);

        // Asignar roles si es necesario
            $employee->roles()->attach(1, ['created_at' => now(), 'updated_at' => now()]);

        // Obtener el primer rol asignado al empleado con solo id y rol
        $rol = $employee->roles()->select('roles.id as role_id', 'roles.rol')->first();

        // Añadir el rol al empleado para la respuesta
        $employee->setAttribute('rol', $rol);

        return response()->json([
            'message' => 'Empleado registrado exitosamente',
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->first_name,
                'role' => $rol ? $rol->rol : "sin asignar",
                'email' => $employee->email,
                'avatar' => 'https://res.cloudinary.com/dfrsffngq/image/upload/v1717141893/rc7kawc9b2uhopdj8z5i.png',
                'status' => $employee->active
            ],
        ], 201);
    }

    public function noHayEmployees()
    {
        // Verificar si no hay empleados registrados
        $sinEmpleados = Employee::count() === 0;

        // Retornar true si no hay empleados, false en caso contrario
        return response()->json([
            'sin_empleados' => $sinEmpleados
        ]);
    }


    public function registerPrimerEmployee(RegisterEmployeeRequest $request)
    {
        // Verificar si no hay empleados registrados
        if (Employee::count() === 0) {
            $data = $request->validated();

            // Crear el rol de administrador si no existe
            $adminRole = Role::firstOrCreate(
                ['rol' => 'admin'], // Condición para buscar
                ['eliminar' => 1, 'editar' => 1, 'ver' => 1, 'preparar_pedidos' => 1, 'entregar_pedidos' => 1] // Valores predeterminados
            );

            // Crear el empleado
            $employee = Employee::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'salary' => $data['salary'],
                'position' => $data['position'],
                'department' => $data['department'],
                'address' => $data['address'] ?? null,
                'hire_date' => $data['hire_date'],
                'active' => true,
                'username' => $data['username'],
                'password' => bcrypt($data['password']),
            ]);

            // Asignar el rol de administrador al primer empleado
            $employee->roles()->attach($adminRole->id, ['created_at' => now(), 'updated_at' => now()]);

            // Obtener el primer rol asignado al empleado
            $rol = $employee->roles()->select('roles.id as role_id', 'roles.rol')->first();

            // Añadir el rol al empleado para la respuesta
            $employee->setAttribute('rol', $rol);

            return response()->json([
                'message' => 'Primer empleado registrado exitosamente como administrador',
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->first_name,
                    'role' => $rol->rol ?? "sin asignar",
                    'email' => $employee->email,
                    'avatar' => 'https://res.cloudinary.com/dfrsffngq/image/upload/v1717141893/rc7kawc9b2uhopdj8z5i.png',
                    'status' => $employee->active,
                ],
            ], 201);
        }

        return response()->json([
            'message' => 'No es posible registrar otro primer empleado, ya existen empleados en el sistema.',
        ], 403);
    }


    public  function trabajadorEnSession(Request $request)
    {
        $usuario = $request->user();
        $rol = $usuario->roles()->first();

        if (!$rol) {
            $usuarioConRol = [
                'name' => $usuario->name,
                'email' => $usuario->email
            ];

            return $usuarioConRol;
        }

        $usuarioConRol = [
            'id' => $usuario->id,
            'name' => $usuario->first_name,
            'rol' => $rol->rol,
            'email' => $usuario->email,
            'avatar' => 'https://res.cloudinary.com/dfrsffngq/image/upload/v1717141893/rc7kawc9b2uhopdj8z5i.png',
            'status' => 'activo'
        ];

        return $usuarioConRol;
    }

    public function indexroles()
    {
        $roles = Role::all('id', 'rol');

        return $roles;
    }

    public function editaremployee(Employee $employee, Request $request)
    {
        // Validación de los datos recibidos
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'salary' => 'required|numeric',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'hire_date' => 'required|date',
            'active' => 'required|boolean',
            'username' => 'required|string|max:255',
        ]);

        // Actualización de los datos del empleado
        $employee->update($request->except('password'));

        // Ocultar la contraseña antes de devolver la respuesta
        $employee->makeHidden('password');

        // Obtener el primer rol asignado al empleado con solo id y rol
        $rol = $employee->roles()->select('roles.id as role_id', 'roles.rol')->first();

        // Añadir el rol al empleado para la respuesta
        $employee->setAttribute('rol', $rol);

        return response()->json([
            "message" => "Empleado actualizado con éxito",
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->first_name,
                'role' => $rol->rol ? $rol->rol : "sin asignar",
                'email' => $employee->email,
                'avatar' => 'https://res.cloudinary.com/dfrsffngq/image/upload/v1717141893/rc7kawc9b2uhopdj8z5i.png',
                'status' => $employee->active
            ],
        ], 200);
    }
}
