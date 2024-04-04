<?php

namespace App\Http\Controllers;

use App\Models\DetallesProductoPedido;
use Carbon\Carbon;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use App\Models\PedidoProducto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PedidoCollection;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pedidos = Pedido::with('user')
            ->with('productos.promocion')
            ->with('pedidoProductos.detallesProductoPedido')
            ->get();

        return [
            'pedidos' => new PedidoCollection($pedidos),
        ];
    }

    public function productostop()
    {
        /*         $productos = DB::table('pedido_productos')
            ->select('producto_id', DB::raw('SUM(cantidad) as total_vendido'))
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->limit(2)
            ->pluck('producto_id'); // Obtener solo los IDs de los productos más vendidos

        $productosMasVendidos = Producto::with('promocion')
            ->whereIn('id', $productos)
            ->get();

        return [
            'productos_mas_vendidos' => $productosMasVendidos
        ]; */
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

        return [
            'data' => $pedido,
            'message' => 'Pedido realizado Correctamente, estara listo en unos minutos'
        ];
    }



    /**
     * Display the specified resource.
     */
    public function show(Pedido $pedido)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $pedido)
    {
        $verificaion = $request->identificador;


        if ($verificaion == 1) {

            Pedido::where('id', $pedido)->update([
                'preparado' => 1,
            ]);
        } else if ($verificaion == 0) {
            Pedido::where('id', $pedido)->update([
                'entregado' => 1,
            ]);
        }


        return [
            'response' =>  'A sido actualizado correactamente'
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
