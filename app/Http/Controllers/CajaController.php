<?php

namespace App\Http\Controllers;

use App\Models\Aperturas_caja;
use App\Models\Caja;
use App\Models\Employee;
use App\Models\User;
use App\Models\Pedido;
use App\Models\Registro;
use Illuminate\Http\Request;
use App\Http\Resources\RegistroResource;
use App\Models\Cierres_caja;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    public function index()
    {
        $cajas = Caja::where('estado', 1)
            ->get('id');

        return $cajas ? $cajas : "no hay cajas";
    }

    public function datoscajas()
    {
        $cajas = Caja::with(['aperturas', 'cierres', 'transacciones'])->get();

        if ($cajas->isEmpty()) {
            $newCaja = new Caja;
            $newCaja->nombre_caja = "virtual";
            $newCaja->estado = 0;
            $newCaja->save();
    
            $cajas = Caja::with(['aperturas', 'cierres', 'transacciones'])->get();
        }

        $resultado = $cajas->map(function ($caja) {
            $totalVentas = $caja->cierres->sum('total_ventas');
            $ultimaApertura = $caja->aperturas->last();
            $ultimoCierre = $caja->cierres->last();
            $montoActual = $ultimaApertura && !$ultimoCierre ? $ultimaApertura->monto_inicial : null;

            // Obtener el total de las transacciones relacionadas a los pedidos
            $totalPedidos = $caja->transacciones->sum(function ($transaccion) {
                $pedido = Pedido::find($transaccion->id_pedido);
                return $pedido ? $pedido->total : 0;
            });

            if ($montoActual !== null) {
                $montoActual += $totalPedidos;
            }

            // Datos estadísticos adicionales
            $numeroAperturas = $caja->aperturas->count();
            $numeroCierres = $caja->cierres->count();
            $numeroTransacciones = $caja->transacciones->count();
            $promedioVentasPorCierre = $numeroCierres > 0 ? $totalVentas / $numeroCierres : 0;
            $promedioTransacciones = $numeroTransacciones > 0 ? $totalPedidos / $numeroTransacciones : 0;

            // Resumen de transacciones desde la última apertura hasta el cierre
            $transaccionesResumen = $ultimaApertura ? $caja->transacciones->where('id_apertura', $ultimaApertura->id)->all() : [];

            return [
                'id' => $caja->id,
                'nombre_caja' => $caja->nombre_caja,
                'estado' => $caja->estado,
                'monto_actual' => $montoActual,
                'total_ventas' => $totalVentas,
                'numero_aperturas' => $numeroAperturas,
                'numero_cierres' => $numeroCierres,
                'numero_transacciones' => $numeroTransacciones,
                'promedio_ventas_por_cierre' => $promedioVentasPorCierre,
                'promedio_transacciones' => $promedioTransacciones,
                'ultima_apertura' => $ultimaApertura,
                'ultimo_cierre' => $ultimoCierre,
                'resumen_transacciones' => $transaccionesResumen,
            ];
        });

        return response()->json($resultado);
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


    public function abrirCaja(Request $request)
    {
        $userId = $request->user()->id; // Obtener el id del usuario del token de autenticación
        $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        // Verificar que el usuario tenga el rol de "admin"
        if ($rol->rol !== 'admin') {
            throw new \Exception('No tiene permisos para abrir la caja.');
        }

        $cajaId = $request->input('caja_id');
        $montoInicial = $request->input('monto_inicial');

        return DB::transaction(function () use ($cajaId, $montoInicial, $userId) {
            // Verificar si la caja existe
            $caja = Caja::find($cajaId);
            if (!$caja) {
                throw new \Exception('La caja no existe.');
            }

            // Verificar si la caja tiene un cierre
            $ultimaApertura = Aperturas_caja::where('id_caja', $cajaId)
                ->orderBy('id', 'desc')
                ->first();

            if ($ultimaApertura) {
                $cierre = $ultimaApertura->cierre;
                if (!$cierre) {
                    throw new \Exception('La caja ya está abierta');
                }
            }

            // Crear una nueva apertura para la caja
            $aperturaCaja = Aperturas_caja::create([
                'id_caja' => $cajaId,
                'monto_inicial' => $montoInicial,
                'usuario_apertura' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Actualizar el estado de la caja
            $caja->estado = 1; // 1 para "abierta"
            $caja->save();

            $aperturaCaja->estadoCaja = $caja->estado;

            return $aperturaCaja;
        });
    }

    public function cerrarCaja(Request $request)
    {
        $userId = $request->user()->id; // Obtener el id del usuario del token de autenticación
        $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        // Verificar que el usuario tenga el rol de "admin"
        if ($rol->rol !== 'admin') {
            throw new \Exception('No tiene permisos para cerrar la caja.');
        }

        $cajaId = $request->input('caja_id');
        $montoFinal = $request->input('monto_final'); // Dinero físico contado al cerrar la caja

        return DB::transaction(function () use ($cajaId, $userId, $montoFinal) {
            // Verificar si la caja existe y está abierta
            $caja = Caja::find($cajaId);
            if (!$caja) {
                throw new \Exception('La caja no existe.');
            }

            if ($caja->estado !== 1) { // 1 para "abierta"
                throw new \Exception('La caja no está abierta.');
            }

            // Verificar si hay pedidos pendientes
            $pedidosPendientes = Pedido::where('estado', '!=', 3)->count();
            if ($pedidosPendientes > 0) {
                throw new \Exception('No se puede cerrar la caja, hay pedidos pendientes.');
            }

            // Obtener la última apertura de la caja
            $ultimaApertura = Aperturas_caja::where('id_caja', $cajaId)
                ->orderBy('id', 'desc')
                ->first();

            if (!$ultimaApertura) {
                throw new \Exception('No se encontró una apertura para la caja.');
            }

            // Obtener datos para el cierre
            $montoActual = $ultimaApertura->monto_inicial;

            // Obtener el total de las transacciones relacionadas a los pedidos
            $totalPedidos = $caja->transacciones->where('id_apertura', $ultimaApertura->id)->sum(function ($transaccion) {
                $pedido = Pedido::find($transaccion->id_pedido);
                return $pedido ? $pedido->total : 0;
            });

            if ($montoActual !== null) {
                $montoActual += $totalPedidos;
            }

            // Calcular la discrepancia entre el monto esperado y el monto físico
            $discrepancia = $montoFinal - $montoActual;

            // Crear un cierre para la última apertura
            $cierreCaja = Cierres_caja::create([
                'id_caja' => $cajaId,
                'id_apertura' => $ultimaApertura->id,
                'monto_final' => $montoFinal,
                'total_ventas' => $totalPedidos,
                'discrepancia' => $discrepancia,
                'usuario_cierre' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Actualizar el estado de la caja
            $caja->estado = 0; // 0 para "cerrada"
            $caja->save();

            $cierreCaja->estadoCaja = $caja->estado;

            return $cierreCaja;
        });
    }
}
