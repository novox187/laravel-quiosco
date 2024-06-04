<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use App\Models\PedidoProducto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PedidoResource;
use App\Models\DetallesProductoPedido;
use App\Http\Resources\PedidoCollection;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($correo)
    {

        $usuario = User::Where('email', $correo)->first();

        if ($usuario->admin == 0) {

            $pedidos = Pedido::with('user')
                ->with('productos.promocion')
                ->with('pedidoProductos.detallesProductoPedido')
                ->where('eliminado', 0)
                ->where('estado', '<=', 2)
                ->where('user_id', $usuario->id)
                ->get();

            return [
                'pedidos' => new PedidoCollection($pedidos),
            ];
        } else {

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Traemos el ultimo codigo de la base de datos
        $ultimoCodigo = Pedido::latest()->first();

        if ($ultimoCodigo) {
            // Extraer la letra y los números del último código
            $letra = substr($ultimoCodigo->numero_pedido, 0, 1);
            $numeros = (int)substr($ultimoCodigo->numero_pedido, 1);

            if ($numeros < 999) {
                // Incrementar los números
                $numeros++;
            } else {
                // Reiniciar los números y cambiar la letra si es Z
                $numeros = 0;
                $letra = $letra === 'Z' ? 'A' : ++$letra;
            }
        } else {
            // Establecer el primer código como A000 si no hay códigos en la base de datos
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





        // Almacenar orden
        $pedido = new Pedido;
        $pedido->user_id = Auth::user()->id;
        $pedido->total = $request->total;
        $pedido->total_neto = $request->totalNeto;
        $pedido->numero_pedido = $nuevoCodigo;
        $pedido->lugar = $request->lugar;
        $pedido->mesa = $request->mesa;
        $pedido->save();

        // Obtener el ID del pedido
        $id_pedido = $pedido->id;

        // Obtener los productos
        $productos = $request->productos;

        // Almacenar los PedidoProducto asociados al pedido
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

        $pedidos = Pedido::with('user')
            ->with('productos.promocion')
            ->with('pedidoProductos.detallesProductoPedido')
            ->where('id', $pedido->id)
            ->first();

        return [
            'data' => new PedidoResource($pedidos),
            'message' => 'Pedido realizado Correctamente, estara listo en unos minutos'
        ];
    }



    /**
     * Display the specified resource.
     */
    public function datosPanel(Pedido $pedido)
    {
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

        $usuariosMes = User::whereDoesntHave('roles')
            ->whereMonth('created_at', '=', date('m'))
            ->count();

        $usuariosMesPasado = User::whereDoesntHave('roles')
            ->whereRaw('MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)')
            ->count();


        $pedidosHoy = DB::table('pedidos')
            ->whereDate('created_at', '=', now()->format('Y-m-d'))
            ->where('estado', 3)
            ->select('total')
            ->get();

        $totalHoy = $pedidosHoy->sum('total');

        $pedidosDiaAnterior = DB::table('pedidos')
            ->whereDate('created_at', '=', now()->subDay()->format('Y-m-d'))
            ->where('estado', 3)
            ->select('total')
            ->get();

        $totalDiaAnterior = $pedidosDiaAnterior->sum('total');

        $pedidosMesActual = DB::table('pedidos')
            ->whereMonth('created_at', '=', date('m'))
            ->where('estado', 3)
            ->select('total')
            ->get();
        $total = $pedidosMesActual->sum('total');

        $pedidosMesPasado = DB::table('pedidos')
            ->whereRaw('MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))')
            ->where('estado', 3)
            ->select('total')
            ->get();

        $totalMesPasado = $pedidosMesPasado->sum('total');


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
        /* 0 es por preparar */
        /* 1 por entregar */
        /* 2 entregado */
        if ($verificacion == 0) {
            Pedido::where('id', $pedido)->update([
                'estado' => 1,
            ]);
        }
        if ($verificacion == 1) {
            Pedido::where('id', $pedido)->update([
                'estado' => 2,
            ]);
        }
        if ($verificacion == 2) {
            Pedido::where('id', $pedido)->update([
                'estado' => 3,
            ]);
        }

        $pedido = Pedido::find($pedido);

        return [
            'id' => $pedido->id,
            'estado' => $pedido->estado,
            'response' =>  'Ha sido actualizado'
        ];
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pedido $pedido)
    {
        //
    }
}
