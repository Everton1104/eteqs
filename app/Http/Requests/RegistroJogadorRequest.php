<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validação do cadastro do jogador (aluno) ao entrar na sala via QR/PIN.
 * Nome e sobrenome são campos separados e obrigatórios.
 */
class RegistroJogadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // rota pública (alunos não têm conta)
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'min:2', 'max:80'],
            'sobrenome' => ['required', 'string', 'min:2', 'max:80'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'sobrenome' => 'sobrenome',
        ];
    }
}
