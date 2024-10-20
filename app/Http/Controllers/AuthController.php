<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegistroRequest;

class AuthController extends Controller
{

    public function Register(RegistroRequest $request)
    {
        //Validar Registro
        $data = $request->validated();

        //Crear el usuario
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);

        //Restornar una respuesta
        return [
            'token' => $user->createToken('token')->plainTextToken,
            'user' => $user
        ];
    }
    public function login(LoginRequest $request)
    {

        $data = $request->validated();

        //REvisar el password
        if (!Auth::attempt($data)) {
            return response([
                'errors' => ['El email o el password son incorrectos']
            ], 422);
        }

        //AUTENTICAR AL USUARIO
        $user = Auth::user();
        return [
            'token' => $user->createToken('token')->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => 'https://res.cloudinary.com/dfrsffngq/image/upload/v1717141893/rc7kawc9b2uhopdj8z5i.png',
                'status' => 'activo'
            ],
        ];
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return [
            'user' => null
        ];
    }

    public function validarTokenwebsocket(Request $request)
    {
        $user = $request->user(); // Laravel ya autentica el token con el middleware 'auth:sanctum'

        if ($user && $user->first_name) {
            // Obtener el primer rol asignado al empleado con solo id y rol
            $rol = $user->roles()->select('id', 'rol')->first();

            return response()->json([
                'employee' => [
                    'id' => $user->id,
                    'name' => $user->first_name,
                    'role' => $rol ? $rol->rol : "sin asignar",
                    'email' => $user->email,
                    'avatar' => 'https://res.cloudinary.com/dfrsffngq/image/upload/v1717141893/rc7kawc9b2uhopdj8z5i.png',
                    'status' => $user->active,
                ],
            ], 201);
        }

        return response()->json(['error' => 'Invalid token'], 401);
    }
}
