<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateProfessorRequest extends FormRequest
{
    /**
     * Somente administradores podem editar professores.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Regras de validação para a edição de professor.
     * A senha é opcional: se enviada em branco, não é alterada.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $professor = $this->route('professor');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($professor)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ];
    }
}
