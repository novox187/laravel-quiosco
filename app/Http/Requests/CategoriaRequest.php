<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoriaRequest extends FormRequest
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
            'nombre' => ['required', 'unique:categorias,nombre'],
            'icono' => ['required']
        ];
    }

    public function messages(){
        return[
            'nombre.required' => 'El Nombre de la categoria es requerido',
            'nombre.unique' => 'Esta categoria ya existe',
            'icono' => 'El Icono de la categoria es requerido'
        ];
    }
}
