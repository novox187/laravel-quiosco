<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContenedorOpcioneResource extends JsonResource
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
            'tipo' => $this->tipo,
            'opciones' => $this->opciones->map(function($opcion) {
                return [
                    'nombre' => $opcion->nombre,
                    'icono' => $opcion->icono,
                    'precio' => $opcion->precio
                ];
            })
        ];
    }
}
