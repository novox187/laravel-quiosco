<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromocionRequest extends FormRequest
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
            'nombre_promo' => ['required', 'unique:promociones,nombre'],
            'porciento_promo' => ['required', 'numeric']
        ];
    }

    public function messages(){
        return[
            'nombre_promo' => 'El Nombre de la Promocion es requerido',
            'nombre_promo.unique' => 'El nombre de la promocion ya existe',
            'porciento_promo' => 'El porcentaje de descuento es requerido',
            'porciento_promo.numeric' => 'El porcentaje de descuento debe ser un numero',
        ];
    }
    
}
