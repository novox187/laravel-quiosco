<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'min:4', 'string'],
            'precio' => ['required'],
            'categoria' => ['required'],
            'imagen' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048', 'unique:productos,imagen'],
            'descripcion' => ['required'],
            'opciones_producto.*' => ['required', 'distinct'],
            'opciones_producto.*.name' => ['required'],
            'opciones_producto.*.tipo' => ['required'],
            'opciones_producto.*.opciones' => ['required'],
            'opciones_producto.*.opciones.*.nombre' => ['required'],
            'opciones_producto.*.opciones.*.precio' => ['required'],

        ];
    }

    public function messages()
    {
        return [
            'nombre' => 'El nombre es requerido',
            'nombre.min' => 'El nombre debe tener minimo 10 caracteres',
            'nombre.string' => 'El nombre tienen que ser Letras',
            'precio' => 'El precio es requerido',
            'categoria' => 'La categoria es Requerida',
            'imagen' => 'Debes seleccionar una imagen',
            'imagen.unique' =>  'La imagen de este producto ya existe en la base de datos',
            'imagen.image' =>  'El archivo tiene que ser una imagen',
            'imagen.mimes' => 'Solo son permitida las extenciones(jpeg,png,jpg,gif,svg)',
            'imagen.max' => 'El tamaño de la imagen no debe ser mayor a 2048mb',
            'descripcion' => 'La descripcion es Oblogatoria',
            'opciones_producto.*.required' => 'Todos los campos de las opciones de contenedor son requeridos',
            'opciones_producto.*.distinct' => 'No se permiten opciones de contenedor duplicadas',
            'opciones_producto.*.name.required' => 'El nombre del contenedor opciones es obligatorio',
            'opciones_producto.*.tipo.required' => 'El tipo de contenedor opciones es requerido',
            'opciones_producto.*.opciones.required' => 'Las opciones son obligatorias',
            'opciones_producto.*.opciones.*.nombre' => 'El nombre de la opcion es obligatorio',
            'opciones_producto.*.opciones.*.precio.required' => 'el precio de la opcion es requerida',
        ];
    }
}
