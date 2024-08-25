<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistroResource extends JsonResource
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
            'accion' => $this->accion,
            'created_at' => $this->created_at,
            'user' =>  $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'name' => $this->employee->first_name,
                    'rol' => $this->employee->roles ? $this->employee->roles->pluck('rol') : [],
                ];
            }),
            'pedido' =>  $this->whenLoaded('pedido', function () {
                return [
                    'id' => $this->pedido->id,
                    'numero_pedido' => $this->pedido->numero_pedido,
                    'efectivo' => $this->pedido->efectivo,
                    'total' => $this->pedido->total,
                    'lugar' => $this->pedido->lugar,
                    'created_at' => $this->pedido->created_at,
                    'mesa' => $this->pedido->mesa
                ];
            }),
            'categoria' =>  $this->whenLoaded('categoria', function () {
                return [
                    'id' => $this->categoria->id,
                    'nombre' => $this->categoria->nombre,
                ];
            }),
            'producto' =>  $this->whenLoaded('producto', function () {
                return [
                    'id' =>  $this->producto->id,
                    'nombre' =>  $this->producto->nombre,
                ];
            }),
            'promocion' =>  $this->whenLoaded('promocion', function () {
                return [
                    'id' =>  $this->promocion->id,
                    'nombre' =>  $this->promocion->nombre,
                ];
            }),
            'detalle' => json_decode($this->detalle, true),
        ];
    }
}
