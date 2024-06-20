<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Registro;
use App\Models\User;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Empty_;

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

        if ($caja->estado === 1) {
            $haceDiezSemanas = now()->subWeeks(10);
            $datosCajas = Caja::where('created_at', '>=', $haceDiezSemanas)
                ->where('estado', 0)
                ->pluck('dinero');

            return [
                'caja' => $caja->dinero,
                'estado' => $caja->estado,
                'historia' => $datosCajas
            ];
        } else {
            $haceDiezSemanas = now()->subWeeks(10);
            $datosCajas = Caja::where('created_at', '>=', $haceDiezSemanas)
                ->where('estado', 0)
                ->pluck('dinero');

            return [
                'caja' => 0,
                'estado' => $caja->estado,
                'historia' => $datosCajas
            ];
        }
    }

    public function store(Request $request)
    {
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = User::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario
        $caja = Caja::latest()->first(); //Obtener ultimo registro de caja
        /* historias de caja */
        $haceDiezSemanas = now()->subWeeks(10);
        $datosCajas = Caja::where('created_at', '>=', $haceDiezSemanas)
            ->where('estado', 0)
            ->pluck('dinero');


        if ($rol->rol == 'admin') {
            if (empty($caja)) {
                $registro = new Registro;
                $registro->accion = 'abrir_caja';
                $registro->user_id = $userId;
                $registro->detalle = json_encode([
                    [
                        'dinero_abrir' => $request->dinero_abrir,
                    ]
                ]);
                $registro->save();

                $newCaja = new Caja;
                $newCaja->dinero = $request->dinero_abrir;
                $newCaja->estado = 1;
                $newCaja->registro_id = $registro->id;
                $newCaja->identificador = 1;
                $newCaja->save();

                return [
                    'registro' => $registro,
                    'nuevaCaja' => [
                        'caja' => $newCaja->dinero,
                        'estado' => $newCaja->estado,
                        'historia' => $datosCajas,
                    ]
                ];
            } else if ($caja->estado == 0) {
                $registro = new Registro;
                $registro->accion = 'abrir_caja';
                $registro->user_id = $userId;
                $registro->detalle = json_encode([
                    [
                        'dinero_abrir' => $request->dinero_abrir,
                    ]
                ]);
                $registro->save();

                $newCaja = new Caja;
                $newCaja->dinero = $request->dinero_abrir;
                $newCaja->estado = 1;
                $newCaja->registro_id = $registro->id;
                $newCaja->identificador = $caja->identificador + 1;
                $newCaja->save();

                return [
                    'registro' => $registro,
                    'nuevaCaja' => [
                        'caja' => $newCaja->dinero,
                        'estado' => $newCaja->estado,
                        'historia' => $datosCajas,
                    ]
                ];
            }
        }
    }

    public function destroy(Request $request)
    {
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = User::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario
        $caja = Caja::latest()->first(); //Obtener ultimo registro de caja

        /* historias de caja */
        $haceDiezSemanas = now()->subWeeks(10);
        $datosCajas = Caja::where('created_at', '>=', $haceDiezSemanas)
            ->where('estado', 0)
            ->pluck('dinero');

        if ($rol->rol == 'admin') {


            $caja->estado = 0;
            $caja->save();

            $registro = new Registro;
            $registro->accion = 'cerrar_caja';
            $registro->user_id = $userId;
            $registro->detalle = json_encode([
                [
                    'id_caja' => $caja->id,
                    'dinero_cerrar' => $caja->dinero,
                ]
            ]);
            $registro->save();

            return [
                'registro' => $registro,
                'nuevaCaja' => [
                    'caja' => 0,
                    'estado' => $caja->estado,
                    'historia' => $datosCajas,
                ]
            ];
        }
    }
}
