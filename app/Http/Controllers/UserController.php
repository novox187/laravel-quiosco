<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
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
        $pedido->observacion = $data['observacion'];
        $pedido->save();

        return [
            'data' => $pedido->id
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
