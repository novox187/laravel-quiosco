<?php

namespace App\Http\Controllers;

use App\Http\Resources\PedidosDetalleCollection;
use App\Http\Resources\PedidosEnvioCollection;
use App\Models\Aperturas_caja;
use App\Models\Cierres_caja;
use App\Models\Transacciones;
use Carbon\Carbon;
use App\Models\Caja;
use App\Models\User;
use App\Models\Pedido;
use App\Models\Employee;
use App\Models\Producto;
use App\Models\Registro;
use Illuminate\Http\Request;
use App\Models\PedidoProducto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PedidoResource;
use App\Models\DetallesProductoPedido;
use App\Http\Resources\PedidoCollection;
use App\Http\Resources\PedidoEnCursoResource;
use App\Http\Resources\PedidosDetalleResource;
use App\Http\Resources\PedidosEnvioResource;
use App\Http\Resources\RegistroResource;
use App\Http\Resources\ResivosPedidoResource;
use App\Models\ContenedorOpcione;
use App\Models\DetallesEntrega;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = $user = User::find($userId); // Obtener el usuario

        $pedidos = Pedido::with('user')
            ->with('productos.promocion')
            ->with('pedidoProductos.detallesProductoPedido')
            ->where('eliminado', 0)
            ->where('estado', '>', 2)
            ->where('user_id', $userId)
            ->get();
        return [
            'pedidos' => new PedidoCollection($pedidos),
        ];
    }

    public function indexadmin(Request $request)
    {
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario
        if ($rol) {
            if ($rol->rol == 'admin' || $rol->rol == 'mesero' || $rol->rol == 'cocinero') {
                $pedidos = Pedido::with('user')
                    ->with('productos.promocion')
                    ->with('pedidoProductos.detallesProductoPedido')
                    ->where('eliminado', 0)
                    ->where('estado', '<=', 2)
                    ->get();

                return [
                    'pedidos' => new PedidoCollection($pedidos),
                ];
            }
        }
    }

    public function indexrepartidor(Request $request)
    {
        $userId = $request->user()->id;
        $user = $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        $pedidos = Pedido::where('employee_id', null)
            ->where('lugar', 'envio')
            ->where('estado', 2)
            ->with('user')
            ->with('pedidoProductos')
            ->get();

        return new PedidosEnvioCollection($pedidos);
    }

    public function pedidosPendientes(Request $request)
    {
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion

        $pedidos = Pedido::where('eliminado', 0)
            ->where('estado', '<=', 2)
            ->where('user_id', $userId)
            ->with('employee')
            ->with('pedidoProductos')
            ->get();
        return response()->json(new PedidosDetalleCollection($pedidos));
    }

    public function pedidosCheques(Request $request)
    {
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol == 'admin' || $rol->rol == 'mesero') {

            $pedidos = Pedido::with('user')
                ->where('eliminado', 0)
                ->where('estado', 3)
                ->whereDate('created_at', '=', now()->format('Y-m-d'))
                ->get();

            return [
                'pedidos' => ResivosPedidoResource::collection($pedidos)
            ];
        }
    }
    public function busquedaPedidos(Request $request)
    {
        $userId = $request->user()->id;
        $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol == 'admin' || $rol->rol == 'mesero') {

            $pedidos = Pedido::where($request->tipo, $request->pedido)
                ->with('user')
                ->where('eliminado', 0)
                ->where('estado', 3)
                ->first();

            return [
                'pedidos' => new ResivosPedidoResource($pedidos),
            ];
        }
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $userId = $request->user()->id;
        $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario


        // Verificar si la caja virtual está activa y sin cierre reciente
        $cajaVirtual = Caja::where('nombre_caja', 'Virtual')->latest()->first();
        if (!$cajaVirtual || $cajaVirtual->estado == 0) {
            return response()->json(['errors' => ['caja' => ['Lo sentimos, Yya cerramos vuelve en horario de atencion,  de lunes a viernes de 8am a 10pm.']]], 422);
        }

        // Verificar que no exista un cierre reciente para la caja virtual
        $ultimoCierre = Cierres_caja::where('id_caja', $cajaVirtual->id)->latest()->first();
        if ($ultimoCierre && $ultimoCierre->created_at->isToday()) {
            return response()->json(['errors' => ['caja' => ['La caja virtual ha sido cerrada recientemente y no está disponible.']]], 422);
        }

        // Código existente para verificar contenedores y opciones
        foreach ($request->productos as $producto) {
            foreach ($producto['detalle_Producto'] as $detalle) {
                $contenedor = ContenedorOpcione::find($detalle['idContenedor']);
                if (!$contenedor || !$contenedor->estado) {
                    return response()->json(['errors' => ['contenedor' => ['El contenedor de opciones ' . $detalle['nombreContenedor'] . ' no está activo']]], 422);
                }
                $opcion = $contenedor->opciones->firstWhere('id', $detalle['idOpcion']);
                if (!$opcion || !$opcion->estado) {
                    return response()->json(['errors' => ['opcion' => ['La opción ' . $detalle['opcion'] . ' no está activa']]], 422);
                }
            }
        }

        // Iniciar transacción y continuar con el código original
        DB::beginTransaction();
        try {
            // Lógica para crear el pedido y guardar detalles
            $nuevoCodigo = $this->generarCodigo();
            $pedido = new Pedido;
            $pedido->user_id = Auth::user()->id;
            $pedido->total = $request->total;
            $pedido->total_neto = $request->totalNeto;
            $pedido->numero_pedido = $nuevoCodigo;
            $pedido->lugar = $request->lugar;
            $pedido->pago = $request->metodoPago;
            $pedido->contacto = $request->contacto;
            $pedido->comentario = $request->ubicacionEntrega == null ? '' : $request->ubicacionEntrega['datos']['comentario'];
            $pedido->direccion = $request->ubicacionEntrega == null ?
                json_encode([
                    "telefono" => '',
                    "coordenadas" => ''
                ])
                :
                json_encode([
                    "telefono" => $request->ubicacionEntrega['datos']['telefono'],
                    "coordenadas" => $request->ubicacionEntrega['direccion']['coordenadas']
                ]);

            /*             if ($request->lugar == 'envio' && $request->metodoPago == 'efectivo') {
                $pedido->estado = 1;
            } */
            if ($request->lugar == 'envio') {
                $pedido->detalle_entrega_id = $request->ubicacionEntrega['datos']['id'];
            }
            $pedido->save();

            // Registrar la transacción en la caja virtual con su apertura actual
            $aperturaActual = Aperturas_caja::where('id_caja', $cajaVirtual->id)->latest()->first();
            if ($aperturaActual) {
                $registro_transaccion = new Transacciones;
                $registro_transaccion->id_caja = $cajaVirtual->id;
                $registro_transaccion->id_apertura = $aperturaActual->id;
                $registro_transaccion->id_pedido = $pedido->id;
                $registro_transaccion->save();
            }

            $id_pedido = $pedido->id;
            $productos = $request->productos;

            foreach ($productos as $producto) {
                $pedidoProducto = new PedidoProducto;
                $pedidoProducto->pedido_id = $id_pedido;
                $pedidoProducto->producto_id = $producto['id'];
                $pedidoProducto->total_opciones = $producto['total_opciones'];
                $pedidoProducto->save();

                foreach ($producto['detalle_Producto'] as $detalle) {
                    $NuevoDetalle = new DetallesProductoPedido;
                    $NuevoDetalle->pedido_producto_id = $pedidoProducto->id;
                    $NuevoDetalle->nombre_contenedor = $detalle['nombreContenedor'];
                    $NuevoDetalle->tipo_contenedor = $detalle['tipoContenedor'];
                    $NuevoDetalle->opcion = $detalle['opcion'];
                    $NuevoDetalle->precio_opcion = $detalle['precio'];
                    $NuevoDetalle->cantidad = $detalle['cantidad'];
                    $NuevoDetalle->save();
                }
            }

            DB::commit();

            $pedidos = Pedido::with('employee')
                ->with('pedidoProductos')
                ->where('id', $pedido->id)
                ->with('user')
                ->first();

            return [
                'data' => new PedidosDetalleResource($pedidos),
                'message' => 'Pedido realizado Correctamente, estará listo en unos minutos',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['errors' => ['pedido' => ['Ha ocurrido un error al realizar el pedido. Inténtelo nuevamente.']]], 500);
        }
    }


    private function generarCodigo()
    {
        // Traemos el último código de la base de datos
        $ultimoCodigo = Pedido::latest()->first();

        if ($ultimoCodigo) {
            // Extraer la letra y los números del último código
            $letra = substr($ultimoCodigo->numero_pedido, 0, 1);
            $numeros = (int)substr($ultimoCodigo->numero_pedido, 2); // Cambio aquí para manejar el formato A-000

            if ($numeros < 999) {
                // Incrementar los números
                $numeros++;
            } else {
                // Reiniciar los números y cambiar la letra si es Z
                $numeros = 0;
                $letra = $letra === 'Z' ? 'A' : ++$letra;
            }
        } else {
            // Establecer el primer código como A-000 si no hay códigos en la base de datos
            $letra = 'A';
            $numeros = 0;
        }

        // Formatear el nuevo código
        $nuevoCodigo = $letra . '-' . str_pad($numeros, 3, '0', STR_PAD_LEFT);

        // Verificar que el nuevo código no exista en la base de datos
        while (Pedido::where('numero_pedido', $nuevoCodigo)->exists()) {
            // Incrementar los números y actualizar el nuevo código
            $numeros = $numeros < 999 ? ++$numeros : 0;
            $letra = $numeros === 0 ? ($letra === 'Z' ? 'A' : ++$letra) : $letra;
            $nuevoCodigo = $letra . '-' . str_pad($numeros, 3, '0', STR_PAD_LEFT);
        }

        return $nuevoCodigo;
    }

    public function asignarrepartidor(Request $request, Pedido $pedido)
    {
        $userId = $request->user()->id;

        // Verificar si el pedido ya tiene un repartidor asignado
        if ($pedido->employee_id) {
            return response()->json([
                'errors' => ['pedido' => ['Este pedido ya está asignado a un repartidor']]
            ], 422);
        }

        // Asignar el repartidor al pedido y guardar
        $pedido->employee()->associate($userId)->save();

        // Obtener los datos del pedido con relaciones
        $pedidoDatos = $pedido->load(['user', 'pedidoProductos']);

        return response()->json([
            'message' => 'Pedido asignado correctamente',
            'data' => new PedidosEnvioResource($pedidoDatos)
        ], 200);
    }

    public function cancelarentrega(Pedido $pedido)
    {
        $pedido->employee_id = null;
        $pedido->save();

        return [
            'mensaje' => 'Pedido cancelado correctamente'
        ];
    }

    public function finalizarentega(Pedido $pedido)
    {
        $pedido->estado = 3;
        $pedido->save();

        $informacionPedido = Pedido::where('id', $pedido->id)->with('employee')
            ->with('user')
            ->first();

        return [
            'email' => $informacionPedido->user->email,
            'payload' => [
                'mensaje' => 'Pedido finalizado correctamente',
                'numeroPedido ' => $informacionPedido->numero_pedido,
                'id_pedido ' => $informacionPedido->id,
                'nombreTrabajador ' => $informacionPedido->employee->first_name,
            ]
        ];
    }
    /**
     * Display the specified resource.
     */
    public function datosPanel(Pedido $pedido)
    {
        // Top productos
        $topProductos = DB::table('pedido_productos')
            ->select('pedido_productos.producto_id', 'productos.nombre', DB::raw('COUNT(*) as repeticiones'))
            ->join('productos', 'pedido_productos.producto_id', '=', 'productos.id')
            ->groupBy('pedido_productos.producto_id', 'productos.nombre')
            ->orderBy('repeticiones', 'desc')
            ->limit(5)
            ->get();

        $topProductosArray = $topProductos->toArray();
        $productoIds = array_column($topProductosArray, 'producto_id');
        $nombres = array_column($topProductosArray, 'nombre');
        $repeticiones = array_column($topProductosArray, 'repeticiones');

        // Usuarios del mes y mes pasado
        $usuariosMes = User::whereMonth('created_at', '=', date('m'))->count();

        // Usuarios del mes pasado
        $usuariosMesPasado = User::whereRaw('MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)')->count();

        // Pedidos hoy y día anterior
        $pedidosHoy = DB::table('pedidos')
            ->whereDate('created_at', '=', now()->format('Y-m-d'))
            ->where('estado', 3)
            ->select(DB::raw('SUM(total) as total'))
            ->first();

        $totalHoy = $pedidosHoy->total ?? 0;

        $pedidosDiaAnterior = DB::table('pedidos')
            ->whereDate('created_at', '=', now()->subDay()->format('Y-m-d'))
            ->where('estado', 3)
            ->select(DB::raw('SUM(total) as total'))
            ->first();

        $totalDiaAnterior = $pedidosDiaAnterior->total ?? 0;

        // Pedidos mes actual y mes pasado
        $pedidosMesActual = DB::table('pedidos')
            ->whereMonth('created_at', '=', date('m'))
            ->where('estado', 3)
            ->select(DB::raw('SUM(total) as total'))
            ->first();

        $total = $pedidosMesActual->total ?? 0;

        $pedidosMesPasado = DB::table('pedidos')
            ->whereRaw('MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))')
            ->where('estado', 3)
            ->select(DB::raw('SUM(total) as total'))
            ->first();

        $totalMesPasado = $pedidosMesPasado->total ?? 0;

        return [
            'ingresoMes' => [
                'nombre' => 'Dinero',
                'fecha' => 'Mes',
                'tipo' => 'dinero',
                'cantidad' => $total,
                'comparacion' => $this->sacarPorcentaje($total, $totalMesPasado),
                'fechaComparacion' => 'Mes pasado'
            ],
            'ingresoHoy' => [
                'nombre' => 'Dinero',
                'fecha' => 'Hoy',
                'tipo' => 'dinero',
                'cantidad' => $totalHoy,
                'comparacion' => $this->sacarPorcentaje($totalHoy, $totalDiaAnterior),
                'fechaComparacion' => 'Dia de ayer'
            ],
            'usuariosMes' => [
                'nombre' => 'Usuarios Nuevos',
                'fecha' => 'Mes',
                'tipo' => 'usuarios',
                'cantidad' => $usuariosMes,
                'comparacion' => $this->sacarPorcentaje($usuariosMes, $usuariosMesPasado),
                'fechaComparacion' => 'Mes pasado'
            ],
            'topProductos' => [
                'productoIds' => $productoIds,
                'nombres' => $nombres,
                'repeticiones' => $repeticiones,
            ],
            'topProductosTabla' => [
                'productos' => $topProductos,
            ],
        ];
    }

    private function sacarPorcentaje($total, $total2)
    {
        if ($total2 && $total) {
            $diferencia = $total - $total2;
            $porcentaje = ($diferencia / $total2) * 100;

            return round($porcentaje);
        }

        if (!$total2 && !$total) {
            return round(0);
        }

        if (!$total2 && $total > 0) {
            return round(100);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $pedido)
    {
        $verificacion = $request->identificador;
        /* 0 por confirmar y cobrar */
        /* 1 en proceso de preparacion */
        /* 2 listo para entregar */
        /* 3 entregado */

        $datosPedido = Pedido::where('id', $pedido)->first(); //Obtener los datos del pedido 
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario
        $ultimoCaja = Caja::latest()
            ->first(); //Se trae el ultimo registro de la tabla cajas


        if ($ultimoCaja->estado == 1) {
            /* CONFIRMAR PEDIDO MESERO */
            if ($verificacion == 0) {
                if ($rol->rol === 'mesero' || $rol->rol === 'admin') {

                    Pedido::where('id', $pedido)->update([
                        'estado' => 1,
                        'pago' => $request->pago,
                        'efectivo' => $request->dineroCliente,
                    ]);

                    $registro = new Registro;
                    $registro->accion = 'cobro';
                    $registro->employee_id = $userId;
                    $registro->pedido_id = $datosPedido->id;
                    $registro->save();

                    /*                     $caja = new Caja;
                    if (!$ultimoCaja) {
                        $caja->dinero = $datosPedido->total;
                    } else {
                        $caja->dinero = $ultimoCaja->dinero + $datosPedido->total;
                    }
                    $caja->identificador = $ultimoCaja->identificador;
                    $caja->registro_id = $registro->id;
                    $caja->save(); */
                } else {
                    $errors = [
                        'permisos' => ['No tienes el rol necesario para realizar esta accion'],
                    ];
                    return response()->json(['errors' => $errors], 422);
                }
            }

            /* COMPLETAR PEDIDO MESERO */
            if ($verificacion == 1) {
                if ($rol->rol === 'cocinero' || $rol->rol === 'admin') {

                    Pedido::where('id', $pedido)->update([
                        'estado' => 2,
                    ]);

                    $registro = new Registro;
                    $registro->accion = 'preparacion';
                    $registro->employee_id = $userId;
                    $registro->pedido_id = $datosPedido->id;
                    $registro->save();
                } else {
                    $errors = [
                        'permisos' => ['No tienes el rol necesario para realizar esta accion'],
                    ];
                    return response()->json(['errors' => $errors], 422);
                }
            }

            /* ENTREGAR PEDIDO MESERO */
            if ($verificacion == 2) {
                if ($rol->rol === 'mesero' || $rol->rol === 'admin') {

                    Pedido::where('id', $pedido)->update([
                        'estado' => 3,
                    ]);

                    $registro = new Registro;
                    $registro->accion = 'entrega';
                    $registro->employee_id = $userId;
                    $registro->pedido_id = $datosPedido->id;
                    $registro->save();
                } else {
                    $errors = [
                        'permisos' => ['No tienes el rol necesario para realizar esta accion'],
                    ];
                    return response()->json(['errors' => $errors], 422);
                }
            }

            $pedido = Pedido::find($pedido);
            $registros = Registro::where('id', $registro->id)
                ->with('employee', 'pedido', 'categoria', 'producto')
                ->first();
            return [
                'id' => $pedido->id,
                'estado' => $pedido->estado,
                'response' =>  'Ha sido actualizado',
                'registro' => new RegistroResource($registros)
            ];
        } else {
            $errors = [
                'caja' => ['Caja esta cerrada, no puedes realizar ninguna accion'],
            ];
            return response()->json(['errors' => $errors], 422);
        }
    }

    public function CrearDetalleEntrega(Request $request)
    {
        try {
            // Validación de los datos de entrada
            $validated = $request->validate([
                'ubicacionEntrega.datos.distanciaKm' => 'required|string',
                'ubicacionEntrega.datos.distanciaFueraDelRadio' => 'required|string',
                'ubicacionEntrega.datos.precioTotal' => 'required|string',
                'ubicacionEntrega.datos.telefono' => 'required|digits_between:9,10',
                'ubicacionEntrega.datos.comentario' => 'nullable|string',
                'ubicacionEntrega.direccion.display_name' => 'required|string',
                'ubicacionEntrega.direccion.coordenadas.lat' => 'required|string',
                'ubicacionEntrega.direccion.coordenadas.lon' => 'required|string',
            ]);

            // Verificar cuántos DetalleEntrega tiene el usuario
            $userId = $request->user()->id; // Obtener el ID del usuario autenticado

            // Limpiar las unidades de los campos distanciaKm y distanciaFueraDelRadio
            $distanciaKm = floatval(str_replace(" km", "", $validated['ubicacionEntrega']['datos']['distanciaKm'])); // Eliminar " km" y convertir a float
            $distanciaFueraDelRadio = floatval(str_replace(" km", "", $validated['ubicacionEntrega']['datos']['distanciaFueraDelRadio'])); // Eliminar " km" y convertir a float
            $precioTotal = floatval($validated['ubicacionEntrega']['datos']['precioTotal']); // Convertir a float
            $latitud = floatval($validated['ubicacionEntrega']['direccion']['coordenadas']['lat']); // Convertir a float
            $longitud = floatval($validated['ubicacionEntrega']['direccion']['coordenadas']['lon']); // Convertir a float

            // Crear un nuevo DetalleEntrega
            $detalleEntrega = new DetallesEntrega();
            $detalleEntrega->user_id = $userId; // Asociar el detalle con el usuario autenticado
            $detalleEntrega->distancia_km = $distanciaKm;
            $detalleEntrega->distancia_fuera_radio = $distanciaFueraDelRadio;
            $detalleEntrega->precio_total = $precioTotal;
            $detalleEntrega->telefono = $validated['ubicacionEntrega']['datos']['telefono'];
            $detalleEntrega->comentario = $validated['ubicacionEntrega']['datos']['comentario'] ?? ''; // Comentario es opcional
            $detalleEntrega->direccion_mapa = $validated['ubicacionEntrega']['direccion']['display_name'];
            $detalleEntrega->latitud = $latitud;
            $detalleEntrega->longitud = $longitud;

            // Guardar el detalle de la entrega
            $detalleEntrega->save();

            // Formatear la respuesta
            $response = [
                'datos' => [
                    'id' => $detalleEntrega->id,
                    'telefono' => $detalleEntrega->telefono,
                    'comentario' => $detalleEntrega->comentario,
                    'distanciaKm' => "{$detalleEntrega->distancia_km} km",
                    'distanciaFueraDelRadio' => "{$detalleEntrega->distancia_fuera_radio} m",
                    'precioTotal' => "{$detalleEntrega->precio_total}",
                ],
                'direccion' => [
                    'coordenadas' => [
                        'lat' => $detalleEntrega->latitud,
                        'lon' => $detalleEntrega->longitud,
                    ],
                    'display_name' => $detalleEntrega->direccion_mapa,
                ],
            ];

            // Responder con éxito
            return response()->json($response, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Manejo de errores de validación
            return response()->json([
                'error' => 'Datos de entrada inválidos.',
                'message' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            // Captura de errores generales
            return response()->json([
                'error' => 'Hubo un error al procesar la solicitud.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function direccionesindex(Request $request)
    {
        $userId = $request->user()->id; // Obtener el ID del usuario autenticado

        // Obtener todos los registros de DetallesEntrega del usuario
        $pedidosUsuario = DetallesEntrega::where('user_id', $userId)
            ->where('eliminado', 0)
            ->get();

        // Formatear cada detalle al formato requerido
        $detallesFormateados = $pedidosUsuario->map(function ($detalle) {
            return [
                'datos' => [
                    'id' => $detalle->id,
                    'telefono' => $detalle->telefono,
                    'comentario' => $detalle->comentario,
                    'distanciaKm' => "{$detalle->distancia_km} km",
                    'distanciaFueraDelRadio' => "{$detalle->distancia_fuera_radio} m",
                    'precioTotal' => "{$detalle->precio_total}",
                ],
                'direccion' => [
                    'coordenadas' => [
                        'lat' => $detalle->latitud,
                        'lon' => $detalle->longitud,
                    ],
                    'display_name' => $detalle->direccion_mapa,
                ],
            ];
        });

        return response()->json($detallesFormateados, 200);
    }


    public function EditarDetalleEntrega(Request $request, $id)
    {
        // Obtener el ID del usuario autenticado
        $userId = $request->user()->id;

        // Buscar el DetalleEntrega que se desea editar
        $detalleEntrega = DetallesEntrega::where('id', $id)->where('user_id', $userId)->first();

        // Validar si el recurso existe y pertenece al usuario
        if (!$detalleEntrega) {
            return response()->json(['error' => 'DetalleEntrega no encontrado o no autorizado.'], 404);
        }

        // Actualizar los campos con los nuevos valores
        $detalleEntrega->telefono = $request->ubicacionEntrega['datos']['telefono'] ?? $detalleEntrega->telefono;
        $detalleEntrega->comentario = $request->ubicacionEntrega['datos']['comentario'] ?? $detalleEntrega->comentario;

        // Guardar los cambios
        $detalleEntrega->save();

        // Formatear la respuesta
        $response = [
            'datos' => [
                'id' => $detalleEntrega->id,
                'telefono' => $detalleEntrega->telefono,
                'comentario' => $detalleEntrega->comentario,
            ]
        ];

        return response()->json($response, 200);
    }



    public function eliminarDireccion($id)
    {
        // Buscar la dirección por su ID
        $direccion = DetallesEntrega::find($id);

        if (!$direccion) {
            return response()->json(['message' => 'Dirección no encontrada'], 404);
        }

        // Marcar la dirección como eliminada
        $direccion->eliminado = true;
        $direccion->save();

        return response()->json(['message' => 'Dirección Eliminada correctamente'], 200);
    }
}
