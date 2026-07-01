<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePerguntaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isProfessor();
    }

    public function rules(): array
    {
        return [
            'texto' => ['required', 'string', 'max:2000'],
            'tempo_segundos' => ['required', 'integer', 'min:10', 'max:300'],
            'alternativas' => ['required', 'array', 'size:4'],
            'alternativas.*.texto' => ['required', 'string', 'max:500'],
            'correta' => ['required', 'integer', 'in:1,2,3,4'],
        ];
    }

    public function attributes(): array
    {
        return [
            'texto' => 'enunciado',
            'tempo_segundos' => 'tempo',
            'alternativas.*.texto' => 'texto da alternativa',
        ];
    }
}
