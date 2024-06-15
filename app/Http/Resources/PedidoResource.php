<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'numero_pedido' => $this->numero_pedido,
            'total' => $this->total,
            'total_neto' => $this->total_neto,
            'eliminado' => $this->eliminado,
            'estado' => $this->estado,
            'lugar' => $this->lugar,
            'mesa' => $this->mesa,
            'created_at' => $this->created_at,
            'user' =>  $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'calificacion' => $this->user->calificacion,
                    'rol' => $this->user->roles->pluck('rol'),
                ];
            }),
            'productos' =>  $this->whenLoaded('pedidoProductos', function () {
                return $this->pedidoProductos->map(function ($pedidoProducto) {
                    $producto = $pedidoProducto->producto;
                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'precio' => $producto->precio,
                        'descripcion' => $producto->descripcion,
                        'total_opciones' => $pedidoProducto->total_opciones,
                        'promocion' => $producto->promocion ? [
                            'id' => $producto->promocion->id,
                            'nombre' => $producto->promocion->nombre,
                            'descuento' => $producto->promocion->descuento
                        ] : null,
                        'detalles_producto' => $pedidoProducto->detallesProductoPedido->map(function ($detalle) {
                            return [
                                'id' => $detalle->id,
                                'nombre_contenedor' => $detalle->nombre_contenedor,
                                'tipo_contenedor' => $detalle->tipo_contenedor,
                                'opcion' => $detalle->opcion,
                                'precio_opcion' => $detalle->precio_opcion,
                                'cantidad' => $detalle->cantidad,
                            ];
                        }),
                    ];
                });
            }),
        ];
    }
}
