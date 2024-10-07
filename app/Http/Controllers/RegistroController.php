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
            ->with('employee', 'pedido', 'categoria', 'producto', 'promocion')
            /* ->limit(50) */
            ->get();
        return RegistroResource::collection($registros);
    }
    public function registroVer($id)
    {
        $registros = Registro::where('id', $id)
            ->with('employee', 'pedido', 'categoria', 'producto', 'promocion')
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
        } else if ($registros->accion === 'crear' || $registros->accion === 'eliminar' && $registros->producto !== null) {
            return [
                'datos' => new RegistroResource($registros),
            ];
        } else if ($registros->accion === 'cambiar_estado') {
            return [
                'datos' => new RegistroResource($registros),
                'estado' => json_decode($registros->detalle)
            ];
        } else if ($registros->accion === 'cobro' || $registros->accion === 'entrega' || $registros->accion === 'preparacion' && $registros->pedido !== null) {
            return [
                'datos' => new RegistroResource($registros),
            ];
        } else if ($registros->accion === 'crear' || $registros->accion === 'eliminar' && $registros->categoria !== null) {
            return [
                'datos' => new RegistroResource($registros),
            ];
        } else if ($registros->accion === 'editar' && $registros->categoria !== null) {
            $registrosd2 = Registro::where('categoria_id', $registros->categoria_id)
                ->orderBy('id', 'desc')
                ->where('id', '<', $registros->id)
                ->first();

            $nuevo = json_decode($registros->detalle, true);
            $viejo = json_decode($registrosd2->detalle, true);

            return [
                'datos' => new RegistroResource($registros),
                'cambios' => $this->encontrarDiferencias($viejo, $nuevo)
            ];
        } else if ($registros->accion === 'crear' || $registros->accion === 'eliminar'  && $registros->promocion !== null) {
            return [
                'datos' => new RegistroResource($registros),
            ];
        } else {
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
                } else if ($key === 'updated_at' || $key === 'created_at') {
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

    private function formatearFecha($fechaISO)
    {
        $fecha = Carbon::parse($fechaISO);
        $dia = $fecha->day;
        $mes = $fecha->month - 1; // Los meses en Carbon son indexados desde 1 (enero) a 12 (diciembre)
        $anio = $fecha->year;
        $horas = $fecha->hour;
        $minutos = $fecha->minute;

        $mesesEnEspanol = [
            "enero", "febrero", "marzo", "abril", "mayo", "junio",
            "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
        ];

        // Formatea los minutos para que siempre tengan dos dígitos
        $minutosFormateados = str_pad($minutos, 2, '0', STR_PAD_LEFT);

        return "{$dia} de {$mesesEnEspanol[$mes]} de {$anio} a las {$horas}:{$minutosFormateados}";
    }
}
