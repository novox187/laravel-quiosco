<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResivosPedidoResource extends JsonResource
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
            'created_at' => $this->created_at,
            'user' =>  $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'rol' => $this->user->roles->pluck('rol'),
                ];
            }),
        ];
    }
}
