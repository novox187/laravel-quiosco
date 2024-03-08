<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
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
            'precio' => $this->precio,
            'imagen' => $this->imagen,
            'descripcion' => $this->descripcion,
            'disponible' => $this->disponible,
            'promocion' => $this->whenLoaded('promocion', function () {
                return [
                    'id' => $this->promocion->id,
                    'nombre' => $this->promocion->nombre,
                    'descuento'=> $this->promocion->descuento
                ];
            }),
            'rating' => $this->rating,
            'categoria_id' => $this->categoria_id,
        ];
    }
}
