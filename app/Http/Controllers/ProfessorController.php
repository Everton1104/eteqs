<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfessorRequest;
use App\Http\Requests\UpdateProfessorRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessorController extends Controller
{
    /**
     * Lista os professores (role = professor).
     */
    public function index(Request $request): View
    {
        $professores = User::where('role', User::ROLE_PROFESSOR)
            ->filtro($request->get('q'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('professores.index', compact('professores'));
    }

    /**
     * Formulário de cadastro de professor.
     */
    public function create(): View
    {
        return view('professores.create');
    }

    /**
     * Salva um novo professor.
     */
    public function store(StoreProfessorRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => User::ROLE_PROFESSOR,
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('professores.index')
            ->with('success', 'Professor cadastrado com sucesso.');
    }

    /**
     * Formulário de edição de professor.
     */
    public function edit(User $professor): View
    {
        $this->garantirProfessor($professor);

        return view('professores.edit', compact('professor'));
    }

    /**
     * Atualiza um professor. A senha só é alterada se preenchida.
     */
    public function update(UpdateProfessorRequest $request, User $professor): RedirectResponse
    {
        $this->garantirProfessor($professor);

        $dados = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $dados['password'] = $request->password;
        }

        $professor->update($dados);

        return redirect()
            ->route('professores.index')
            ->with('success', 'Professor atualizado com sucesso.');
    }

    /**
     * Remove um professor.
     */
    public function destroy(User $professor): RedirectResponse
    {
        $this->garantirProfessor($professor);

        $professor->delete();

        return redirect()
            ->route('professores.index')
            ->with('success', 'Professor removido com sucesso.');
    }

    /**
     * Garante que o usuário manipulado seja de fato um professor,
     * evitando excluir/editar administradores via rota.
     */
    protected function garantirProfessor(User $professor): void
    {
        abort_unless($professor->isProfessor(), 404);
    }
}
