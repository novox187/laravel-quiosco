<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Employee;
use App\Models\User;
use App\Models\Pedido;
use App\Models\Registro;
use Illuminate\Http\Request;
use App\Http\Resources\RegistroResource;

class CajaController extends Controller
{
    public function index()
    {
        $caja = Caja::latest()->first();

        if (empty($caja)) {
            return [
                'caja' => 0,
                'estado' => 0,
                'historia' => []
            ];
        }

        if ($caja->estado === 'abierta') {
            $haceDiezSemanas = now()->subWeeks(10);
            $datosCajas = Caja::where('created_at', '>=', $haceDiezSemanas)
                ->where('estado', 'cerrada')
                ->pluck('nombre_caja');

            return [
                'caja' => $caja->nombre_caja,
                'estado' => $caja->estado,
                'historia' => $datosCajas
            ];
        } else {
            $haceDiezSemanas = now()->subWeeks(10);
            $datosCajas = Caja::where('created_at', '>=', $haceDiezSemanas)
                ->where('estado', 'cerrada')
                ->pluck('nombre_caja');

            return [
                'caja' => 0,
                'estado' => $caja->estado,
                'historia' => $datosCajas
            ];
        }
    }

    public function store(Request $request)
    {
        $userId = $request->user()->id; // Obtener el id del usuario del token de autenticación
        $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol == 'admin') {

            $newCaja = new Caja;
            $newCaja->nombre_caja = $request->nombre_caja;
            $newCaja->estado = 0;
            $newCaja->save();

            $registro = new Registro;
            $registro->accion = 'crear';
            $registro->employee_id = $userId;
            $registro->detalle = json_encode([
                [
                    'nombre_caja' => $request->nombre_caja,
                    'id_usuario' => $userId
                ]
            ]);
            $registro->save();

            return [
                'registro' => $registro,
                'nuevaCaja' => [
                    'caja' => $newCaja->nombre_caja,
                    'estado' => $newCaja->estado,
                ]
            ];
        } else {
            $errors = [
                'permisos' => ['No tienes el rol necesario para realizar esta acción'],
            ];
            return response()->json(['errors' => $errors], 422);
        }
    }

    public function destroy(Request $request)
    {
        $userId = $request->user()->id; // Obtener el id del usuario del token de autenticación
        $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario
        $caja = Caja::latest()->first(); // Obtener último registro de caja

        /* Historias de caja */
        $haceDiezSemanas = now()->subWeeks(10);
        $datosCajas = Caja::where('created_at', '>=', $haceDiezSemanas)
            ->where('estado', 'cerrada')
            ->pluck('nombre_caja');

        $pedidosPendientes = Pedido::where('estado', '<', 3)->get();

        if ($rol->rol == 'admin') {

            if ($pedidosPendientes->count() == 0) {
                $caja->estado = 'cerrada';
                $caja->save();

                $registro = new Registro;
                $registro->accion = 'cerrar_caja';
                $registro->employee_id = $userId;
                $registro->detalle = json_encode([
                    [
                        'id_caja' => $caja->id,
                        'nombre_caja' => $caja->nombre_caja,
                    ]
                ]);
                $registro->save();

                $registros = Registro::where('id', $registro->id)
                    ->with('user', 'pedido', 'categoria', 'producto')
                    ->first();

                return [
                    'nuevaCaja' => [
                        'caja' => 0,
                        'estado' => $caja->estado,
                        'historia' => $datosCajas,
                    ],
                    'registro' => new RegistroResource($registros)
                ];
            } else {
                $errors = [
                    'pedidos' => ['Tienes pedidos pendientes, complétalos antes de cerrar la caja'],
                ];
                return response()->json(['errors' => $errors], 422);
            }
        } else {
            $errors = [
                'permisos' => ['No tienes el rol necesario para realizar esta acción'],
            ];
            return response()->json(['errors' => $errors], 422);
        }
    }
}
