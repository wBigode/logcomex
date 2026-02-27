<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePokemonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'max:255'],
            'altura' => ['required', 'integer', 'min:1'],
            'peso' => ['required', 'numeric', 'min:0.01'],
            'sprite' => ['required', 'string', 'max:255'], //@TODO Implementar uplaod de imagem via S3, atualmente apenas suportado uma URL da imagem
            'ativo' => ['required', 'boolean'],
        ];
    }
}
