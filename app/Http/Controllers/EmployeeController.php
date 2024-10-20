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
        if (isset($data['rol_id'])) {
            $employee->roles()->attach($data['rol_id'], ['created_at' => now(), 'updated_at' => now()]);
        }

        // Obtener el primer rol asignado al empleado con solo id y rol
        $rol = $employee->roles()->select('roles.id as role_id', 'roles.rol')->first();

        // Añadir el rol al empleado para la respuesta
        $employee->setAttribute('rol', $rol);

        return response()->json([
            'message' => 'Empleado registrado exitosamente',
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->first_name,
                'role' => $rol->rol ? $rol->rol : "sin asignar",
                'email' => $employee->email,
                'avatar' => 'https://res.cloudinary.com/dfrsffngq/image/upload/v1717141893/rc7kawc9b2uhopdj8z5i.png',
                'status' => $employee->active
            ],
        ], 201);
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
}
