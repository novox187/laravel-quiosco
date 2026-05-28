<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();
        $productos = DB::table('productos')->select('id', 'precio')->get();
        $opcionesPorContenedor = DB::table('opciones')
            ->select('opciones.id', 'opciones.nombre', 'opciones.precio', 'opciones.contenedor_id', 'contenedor_opciones.nombre as contenedor_nombre', 'contenedor_opciones.tipo as contenedor_tipo')
            ->join('contenedor_opciones', 'opciones.contenedor_id', '=', 'contenedor_opciones.id')
            ->get()
            ->groupBy('contenedor_id');

        $contenedoresPorProducto = DB::table('contenedor_opcione_producto')
            ->select('producto_id', 'contenedor_opcione_id')
            ->get()
            ->groupBy('producto_id');

        if (empty($userIds) || $productos->isEmpty()) {
            return;
        }

        $lugares = ['mesa', 'local', 'domicilio'];
        $estados = [0, 1, 2, 3]; // 0=pendiente, 1=preparando, 2=listo, 3=entregado

        $totalPedidos = 30;

        for ($i = 1; $i <= $totalPedidos; $i++) {
            $diasAtras = rand(0, 29);
            $horasAtras = rand(8, 22);
            $fecha = Carbon::now()->subDays($diasAtras)->setTime($horasAtras, rand(0, 59), 0);

            $lugar = $lugares[array_rand($lugares)];
            $mesa = $lugar === 'mesa' ? rand(1, 12) : null;
            $estado = $estados[array_rand($estados)];

            $cantidadProductos = rand(1, 4);
            $productosSeleccionados = $productos->random($cantidadProductos);

            $totalNetoPedido = 0;
            $pedidoLineas = [];

            foreach ($productosSeleccionados as $producto) {
                $cantidadProducto = rand(1, 3);
                $descuentoLinea = rand(0, 1) ? 0 : rand(5, 15);

                $contenedoresDelProducto = $contenedoresPorProducto[$producto->id] ?? collect();
                $detalles = [];
                $totalOpcionesLinea = 0;

                foreach ($contenedoresDelProducto as $rel) {
                    $opcionesContenedor = $opcionesPorContenedor[$rel->contenedor_opcione_id] ?? collect();
                    if ($opcionesContenedor->isEmpty()) {
                        continue;
                    }

                    $tipoContenedor = $opcionesContenedor->first()->contenedor_tipo;
                    $nombreContenedor = $opcionesContenedor->first()->contenedor_nombre;

                    if ($tipoContenedor === 'unico') {
                        if (rand(0, 1)) {
                            $opcion = $opcionesContenedor->random();
                            $detalles[] = [
                                'nombre_contenedor' => $nombreContenedor,
                                'tipo_contenedor' => $tipoContenedor,
                                'opcion' => $opcion->nombre,
                                'precio_opcion' => $opcion->precio,
                                'cantidad' => 1,
                            ];
                            $totalOpcionesLinea += $opcion->precio;
                        }
                    } else {
                        $cantidadMultiples = rand(0, min(3, $opcionesContenedor->count()));
                        if ($cantidadMultiples > 0) {
                            $opcionesElegidas = $opcionesContenedor->random($cantidadMultiples);
                            foreach ($opcionesElegidas as $opcion) {
                                $detalles[] = [
                                    'nombre_contenedor' => $nombreContenedor,
                                    'tipo_contenedor' => $tipoContenedor,
                                    'opcion' => $opcion->nombre,
                                    'precio_opcion' => $opcion->precio,
                                    'cantidad' => 1,
                                ];
                                $totalOpcionesLinea += $opcion->precio;
                            }
                        }
                    }
                }

                $subtotalLinea = ($producto->precio + $totalOpcionesLinea) * $cantidadProducto;
                $subtotalConDescuento = $subtotalLinea * (1 - $descuentoLinea / 100);
                $totalNetoPedido += $subtotalConDescuento;

                $pedidoLineas[] = [
                    'producto_id' => $producto->id,
                    'descuento' => $descuentoLinea,
                    'total_opciones' => $totalOpcionesLinea,
                    'cantidad' => $cantidadProducto,
                    'detalles' => $detalles,
                ];
            }

            $totalConIVA = round($totalNetoPedido * 1.15, 2);

            $pedidoId = DB::table('pedidos')->insertGetId([
                'user_id' => $userIds[array_rand($userIds)],
                'total' => $totalConIVA,
                'total_neto' => round($totalNetoPedido, 2),
                'numero_pedido' => str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'lugar' => $lugar,
                'mesa' => $mesa,
                'observacion' => rand(0, 4) === 0 ? 'Sin cebolla, por favor.' : null,
                'eliminado' => false,
                'estado' => $estado,
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);

            foreach ($pedidoLineas as $linea) {
                $pedidoProductoId = DB::table('pedido_productos')->insertGetId([
                    'pedido_id' => $pedidoId,
                    'producto_id' => $linea['producto_id'],
                    'descuento' => $linea['descuento'],
                    'total_opciones' => $linea['total_opciones'],
                    'created_at' => $fecha,
                    'updated_at' => $fecha,
                ]);

                foreach ($linea['detalles'] as $detalle) {
                    DB::table('detalles_producto_pedidos')->insert([
                        'pedido_producto_id' => $pedidoProductoId,
                        'nombre_contenedor' => $detalle['nombre_contenedor'],
                        'tipo_contenedor' => $detalle['tipo_contenedor'],
                        'opcion' => $detalle['opcion'],
                        'precio_opcion' => $detalle['precio_opcion'],
                        'cantidad' => $detalle['cantidad'] * $linea['cantidad'],
                        'created_at' => $fecha,
                        'updated_at' => $fecha,
                    ]);
                }
            }
        }
    }
}
