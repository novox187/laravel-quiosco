<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Registro;
use Illuminate\Http\Request;
use App\Http\Resources\RegistroResource;

class RegistroController extends Controller
{
    public function index()
    {
        $registros = Registro::orderBy('id', 'desc')
            /*  ->whereDate('created_at', Carbon::today()) */
            ->where('contenedor_id', null)
            ->with('user', 'pedido', 'categoria', 'producto', 'promocion')
            ->limit(50)
            ->get();
        return RegistroResource::collection($registros);
    }
    public function registroVer($id)
    {
        $registros = Registro::where('id', $id)
            ->with('user', 'pedido', 'categoria', 'producto', 'promocion')
            ->first();

        if ($registros->accion === 'editar') {
            $registrosd2 = Registro::where('producto_id', $registros->producto_id)
                ->orderBy('id', 'desc')
                ->where('id', '<', $registros->id)
                ->first();


            // Convertir el JSON a array asociativo
            $nuevo = json_decode($registros->detalle, true);
            $viejo = json_decode($registrosd2->detalle, true);

            return [
                'datos' => new RegistroResource($registros),
                'cambios' => $this->encontrarDiferencias($viejo, $nuevo)
            ];
        } else if($registros->accion === 'crear' || $registros->accion === 'eliminar' && $registros->producto !== null){
            return [
                'datos' => new RegistroResource($registros),
            ];
        }else if($registros->accion === 'cambiar_estado'){
            return [
                'datos' => new RegistroResource($registros),
                'estado' => json_decode($registros->detalle)
            ];
        }else{
            return [
                'datos' => new RegistroResource($registros),
                'cambios' => [
                    'en desarrollo'
                ]
            ];
        }
    }

    function encontrarDiferencias($d1, $d2)
    {
        $cambios = [];

        foreach ($d1 as $key => $value) {
            if (array_key_exists($key, $d2) && $d2[$key] !== $value) {
                if ($key === 'descripcion') {
                    $cambios[] = "Modificó la descripción";
                } else {
                    $cambios[] = "Cambió el $key de " . (is_string($value) ? $value : json_encode($value)) . " a " . (is_string($d2[$key]) ? $d2[$key] : json_encode($d2[$key]));
                }
            }
        }

        /*         // También debemos verificar si hay nuevos campos en d2 que no están en d1
        foreach ($d2 as $key => $value) {
            if (!array_key_exists($key, $d1)) {
                $cambios[$key] = [
                    'old' => null,
                    'new' => $value
                ];
            }
        } */


        return  $cambios;
    }
}
