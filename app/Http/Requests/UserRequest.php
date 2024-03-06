<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'observacion' => ['required', 'min:10'],
            'puntos' => ['required']
        ];
    }

    public function messages(){
        return[
            'observacion' => 'La observacion es obligatoria',
            'observacion.min' => 'Debe tener minimo 10 caracteres'
        ];
    }
}
