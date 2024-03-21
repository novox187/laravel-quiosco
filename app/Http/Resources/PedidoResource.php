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
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_pedido' => $this->numero_pedido,
            'total' => $this->total,
            'total_neto' => $this->total_neto,
            'preparado'=> $this->preparado,
            'entregado' => $this->entregado,
            'lugar' => $this->lugar,
            'mesa' => $this->mesa,
            'created_at' => $this->created_at,
            'user' =>  $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'calificacion' => $this->user->calificacion,
                ];
            }),
            'productos' =>  $this->whenLoaded('productos', function () {
                return $this->productos->map(function ($producto) {
                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'cantidad' => $producto->pivot->cantidad,
                        'precio' => $producto->precio,
                        'descripcion' => $producto->descripcion,
                        'promocion' => $producto->promocion ? [
                            'id' => $producto->promocion->id,
                            'nombre' => $producto->promocion->nombre,
                            'descuento' => $producto->promocion->descuento
                        ] : null,
                    ];
                });
            }),
        ];
    }
}
