<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lote_id' => 'required|exists:lotes,id',
            'lectura_actual' => 'required|numeric|min:0',
            'lectura_anterior' => 'nullable|numeric|min:0|lte:lectura_actual',
            'fecha_toma' => 'required|date',
            'fecha_anterior' => 'nullable|date|before:fecha_toma',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
