<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriaProductoResource extends JsonResource
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
            'nombre' => $this->nombre,
            'icono' => $this->icono,
            'productos' =>  $this->whenLoaded('productos', function () {
                return $this->productos->map(function ($producto) {
                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'imagen' => $producto->imagen,
                        'eliminado' => $producto->eliminado
                    ];
                });
            }),
        ];
    }
}
