<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as PasswordRules;

class RegistroRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required','confirmed', 'min:5'],
            'tcConfirmacion' => ['required']
        ];
    }

    public function messages() {
        return[
            'name' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email no es valido',
            'email.unique' => 'El email ya esta registrado',
            'password' => 'La contraseña debe tener almenos 5 caracteres',
            'password.confirmed' => 'Verifica la contraseña',
            'tcConfirmacion' => 'Acepta los terminos y condiciones para poder registrarte'
        ];
    }
}
