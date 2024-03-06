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
            'nombre' => ['required','min:4', 'string','unique:productos,nombre'],
            'precio' => ['required'],
            'categoria' => ['required'],
            'imagen' => ['required', 'image','mimes:jpeg,png,jpg,gif,svg,webp','max:2048', 'unique:productos,imagen'],
            'descripcion' => ['required'],
        ];
    }

    public function messages(){
        return[
            'nombre' => 'El nombre es requerido',
            'nombre.min' => 'El nombre debe tener minimo 10 caracteres',
            'nombre.string' => 'El nombre tienen que ser Letras',
            'nombre.unique' => 'El producto ya existe en la base de datos',
            'precio' => 'El precio es requerido',
            'categoria' => 'La categoria es Requerida',
            'imagen' => 'Debes seleccionar una imagen',
            'imagen.unique' =>  'La imagen de este producto ya existe en la base de datos',
            'imagen.image' =>  'El archivo tiene que ser una imagen' ,
            'imagen.mimes' => 'Solo son permitida las extenciones(jpeg,png,jpg,gif,svg)',
            'imagen.max' => 'El tamaño de la imagen no debe ser mayor a 2048mb',
            'descripcion' => 'La descripcion es Oblogatoria',
        ];

    }
}
