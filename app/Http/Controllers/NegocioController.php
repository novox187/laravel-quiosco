<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class NegocioController extends Controller
{
    // Mostrar todos los negocios
    public function index(Request $request)
    {
        $employee = $request->user();
        $rol = $employee->roles()->first();

        if ($rol && $rol->rol == 'admin') {
            // Obtiene el primer negocio, asumiendo que solo hay uno
            $negocio = Negocio::first();

            // Verifica si se encontró un negocio
            if ($negocio) {
                return response()->json($negocio);
            } else {
                return response()->json(['message' => 'No se encontró ningún negocio.'], 404); // 404 Not Found
            }
        }

        return response()->json(['error' => 'No tienes permiso para realizar esta acción.'], 403); // 403 Forbidden
    }

    // Almacenar un nuevo negocio
    public function store(Request $request, Negocio $negocio)
    {
        $request->validate([
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,webp,avif|max:2048', // Asegúrate de que el tipo de archivo sea correcto
            'nombre' => 'nullable|string|max:255',
            'eslogan' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'ruc' => 'nullable|string|max:20',
        ]);

        $employee = $request->user();
        $rol = $employee->roles()->first();

        if ($rol && $rol->rol == 'admin') {
            $url = null; // Inicializa la variable

            if ($request->hasFile('logo')) { // Verifica si se ha subido un archivo
                try {
                    $uploadedFileUrl = Cloudinary::upload($request->logo->getRealPath(), ['format' => 'avif']);
                    $url = $uploadedFileUrl->getSecurePath();
                    $public_id = $uploadedFileUrl->getPublicId();
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Error al subir la imagen: ' . $e->getMessage()], 500);
                }
            }

            $negocio = new Negocio;
            $negocio->logo = $url; // Asigna solo si se ha subido una imagen
            $negocio->public_id = $public_id; // Asigna solo si se ha subido una imagen
            $negocio->nombre = $request->nombre;
            $negocio->eslogan = $request->eslogan;
            $negocio->direccion = $request->direccion;
            $negocio->telefono = $request->telefono;
            $negocio->email = $request->email;
            $negocio->ruc = $request->ruc;
            $negocio->save();

            return response()->json($negocio, 201); // 201 Created
        }

        return response()->json(['error' => 'No tienes permiso para realizar esta acción.'], 403); // 403 Forbidden
    }

    // Mostrar un negocio específico
    public function show(Negocio $negocio)
    {
        return response()->json($negocio);
    }
    // Actualizar un negocio específico
    public function update(Request $request, $id)
    {
        $request->validate([
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,webp,avif|max:2048',
            'nombre' => 'nullable|string|max:255',
            'eslogan' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'ruc' => 'nullable|string|max:20',
        ]);

        $employee = $request->user();
        $rol = $employee->roles()->first();

        if ($rol && $rol->rol == 'admin') {
            $negocio = Negocio::find($id);

            if (!$negocio) {
                return response()->json(['error' => 'Negocio no encontrado'], 404);
            }
            
            if ($negocio->public_id) {
                Cloudinary::destroy($negocio->public_id);
                // Solo actualiza los campos que se han enviado en la solicitud
                if ($request->hasFile('logo')) {
                    try {
                        $uploadedFileUrl = Cloudinary::upload($request->file('logo')->getRealPath(), ['folder' => 'productos', 'format' => 'avif']);
                        $negocio->logo = $uploadedFileUrl->getSecurePath();
                        $negocio->public_id = $uploadedFileUrl->getPublicId();
                    } catch (\Exception $e) {
                        return response()->json(['error' => 'Error al subir la imagen: ' . $e->getMessage()], 500);
                    }
                }
            }


            // Actualiza solo los campos que se han proporcionado
            $negocio->nombre = $request->nombre ?? $negocio->nombre;
            $negocio->eslogan = $request->eslogan ?? $negocio->eslogan;
            $negocio->direccion = $request->direccion ?? $negocio->direccion;
            $negocio->telefono = $request->telefono ?? $negocio->telefono;
            $negocio->email = $request->email ?? $negocio->email;
            $negocio->ruc = $request->ruc ?? $negocio->ruc;
            $negocio->save();

            return response()->json($request);
        }

        return response()->json(['error' => 'No tienes permiso para realizar esta acción.'], 403);
    }

    // Eliminar un negocio específico
    public function destroy(Request $request, Negocio $negocio)
    {
        $employee = $request->user();
        $rol = $employee->roles()->first();
        if ($rol->rol == 'admin') {
            $negocio->delete();
            return response()->json(null, 204); // 204 No Content
        }
    }
}
