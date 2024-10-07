<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Registro;
use App\Models\Promocione;
use Illuminate\Http\Request;
use App\Http\Requests\PromocionRequest;
use App\Http\Resources\RegistroResource;
use App\Http\Resources\PromocionCollection;

class PromocioneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new PromocionCollection(Promocione::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PromocionRequest $request)
    {
        $datos = $request->validated();

        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol === 'admin') { //valida que tenga los permisoso necesarios
            $promocion = new Promocione;
            $promocion->nombre = $datos['nombre_promo'];
            $promocion->descuento = $datos['porciento_promo'];
            $promocion->save();

            $registro = new Registro;
            $registro->accion = 'crear';
            $registro->employee_id = $userId;
            $registro->promocion_id = $promocion->id;
            $registro->detalle = json_encode($promocion);
            $registro->save();

            $registros = Registro::where('id', $registro->id)
                ->with('user', 'pedido', 'categoria', 'producto', 'promocion')
                ->first();

            return [
                'nueva_promocion' => $promocion,
                'registro' => new RegistroResource($registros)
            ];
        } else {
            $errors = [
                'permisos' => ['No tienes el rol necesario para realizar esta accion'],
            ];
            return response()->json(['errors' => $errors], 422);
        }
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
