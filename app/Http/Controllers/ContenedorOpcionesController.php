<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContenedorOpcione;
use App\Http\Resources\ContenedorOpcioneResource;
use App\Models\Opcione;

class ContenedorOpcionesController extends Controller
{
    public function index()
    {
        $contenedores = ContenedorOpcione::with('opciones')->get();
        return response()->json(ContenedorOpcioneResource::collection($contenedores));
    }

    public function cambiarEstadoContenedor(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:contenedor_opciones,id',
            'estado' => 'required|boolean'
        ]);

        $contenedor = ContenedorOpcione::findOrFail($request->id);
        $contenedor->estado = $request->estado;
        $contenedor->save();

        return response()->json(['message' => 'Estado del contenedor actualizado correctamente.', 'contenedor' => new ContenedorOpcioneResource($contenedor)]);
    }

    public function cambiarEstadoOpcion(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:opciones,id',
            'estado' => 'required|boolean'
        ]);

        $opcion = Opcione::findOrFail($request->id);
        $contenedor = $opcion->contenedor;

        // Verificar si se está intentando desactivar la última opción activa
        $opcionesActivas = $contenedor->opciones()->where('estado', true)->count();
        if ($opcionesActivas === 1 && $request->estado === false) {
            return response()->json(['message' => 'No puedes desactivar la última opción activa. Desactiva el contenedor si deseas desactivar todas las opciones.'], 400);
        }

        $opcion->estado = $request->estado;
        $opcion->save();

        return response()->json(
            [
                'message' => 'Estado de la opción actualizado correctamente.',
                'opcion' => [
                    "id" => $opcion->id,
                    "contenedor_id" => $opcion->contenedor_id,
                    "nombre" => $opcion->nombre,
                    "pecio" => $opcion->pecio == null ? 0 : $opcion->pecio,
                    "estado" => $opcion->estado,
                ]
            ]
        );
    }
}
