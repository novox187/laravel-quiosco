<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoActualizarRequest extends FormRequest
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
            'nombre' => ['required'],
            'precio' => ['required'],
            'peso' => ['required'],
            'descripcion' => ['required']
        ];
    }

    public function messages(){
        return[
            'nombre' => 'El nombre es requerido',
            'precio' => 'El precio es requerido',
            'peso' => 'El peso es requerido',
            'descripcion' => 'La descripcion es Oblogatoria',
        ];

    }
}
