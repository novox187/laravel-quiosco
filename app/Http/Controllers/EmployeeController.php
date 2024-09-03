<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginEmployeeRequest;
use App\Http\Requests\RegisterEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

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
        if (isset($data['roles'])) {
            $employee->roles()->attach($data['roles']);
        }

        return response()->json([
            'message' => 'Empleado registrado exitosamente',
            'employee' => $employee,
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

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        //
    }
}
