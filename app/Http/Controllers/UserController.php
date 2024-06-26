<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pedido;
use App\Models\Registro;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\RegistroResource;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public  function index(Request $request)
    {
        $usuariosSinRol = User::whereDoesntHave('roles')->get(['id', 'name', 'email','estado', 'calificacion']);

        $usuarios = [];
        foreach ($usuariosSinRol as $usuario) {
            $usuarioFormateado = [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'calificacion' => $usuario->calificacion,
                'avatar' => 'https://res.cloudinary.com/dfrsffngq/image/upload/v1717141893/rc7kawc9b2uhopdj8z5i.png',
                'status' => $usuario->estado
            ];
            $usuarios[] = $usuarioFormateado;
        }
    
        return $usuarios;
    }
    public  function usuarioEnSession(Request $request)
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
            'name' => $usuario->name,
            'rol' => $rol->rol,
            'email' => $usuario->email,
            'avatar' => 'https://res.cloudinary.com/dfrsffngq/image/upload/v1717141893/rc7kawc9b2uhopdj8z5i.png',
            'status' => 'activo'
        ];

        return $usuarioConRol;
    }
    public  function equipoTrabajo()
    {
        $usuarios = User::whereHas('roles')->get();

        $usuariosConRol = [];
        foreach ($usuarios as $usuario) {
            $rol = $usuario->roles()->first();
            $usuarioConRol = [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'role' => $rol->rol,
                'email' => $usuario->email,
                'avatar' => 'https://res.cloudinary.com/dfrsffngq/image/upload/v1717141893/rc7kawc9b2uhopdj8z5i.png',
                'status' => $usuario->estado
            ];
            $usuariosConRol[] = $usuarioConRol;
        }

        return $usuariosConRol;
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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();

        //Actualizamos la calificacion del usuario
        $user = User::findOrFail($request->id_user);
        $user->calificacion = $data['puntos'];
        $user->save();

        //Agregamos la observacion al pedido
        $pedido = Pedido::findOrFail($request->id_pedido);
        $pedido->eliminado = 1;
        $pedido->save();

        $registro = new Registro;
        $registro->accion = 'eliminar';
        $registro->user_id = $request->id_user;
        $registro->pedido_id = $request->id_pedido;
        $registro->detalle = $data['observacion'];
        $registro->save();

        $registros = Registro::where('id', $registro->id)
        ->with('user', 'pedido', 'categoria', 'producto')
        ->first();

        return [
            'data' => $pedido->id,
            'registro' => new RegistroResource($registros)
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
