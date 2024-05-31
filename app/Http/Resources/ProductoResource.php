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
            'eliminado' => $this->eliminado,
            'promo_id' => $this->promo_id,
            'rating' => $this->rating,
            'categoria_id' => $this->categoria_id,
            'created_at' => $this->created_at,
            'promocion' => $this->whenLoaded('promocion', function () {
                return [
                    'id' => $this->promocion->id,
                    'nombre' => $this->promocion->nombre,
                    'descuento' => $this->promocion->descuento,
                ];
            }),
            'contenedor_opciones' => $this->whenLoaded('contenedorOpciones', function () {
                return $this->contenedorOpciones->map(function ($contenedorOpcion) {
                    return [
                        'id' => $contenedorOpcion->id,
                        'nombre' => $contenedorOpcion->nombre,
                        'tipo' => $contenedorOpcion->tipo,
                        'opciones' => $contenedorOpcion->opciones->map(function ($opcion) {
                            return [
                                'id' => $opcion->id,
                                'nombre' => $opcion->nombre,
                                'precio' => $opcion->precio,
                            ];
                        }),
                    ];
                });
            }),
        ];
    }
}
