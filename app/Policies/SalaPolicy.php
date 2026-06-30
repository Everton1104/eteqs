<?php

namespace App\Policies;

use App\Models\Sala;
use App\Models\User;

class SalaPolicy
{
    /**
     * Usuário só gerencia suas próprias salas.
     */
    protected function isOwner(User $user, Sala $sala): bool
    {
        return $sala->user_id === $user->id;
    }

    public function view(User $user, Sala $sala): bool
    {
        return $this->isOwner($user, $sala);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Sala $sala): bool
    {
        return $this->isOwner($user, $sala);
    }

    public function delete(User $user, Sala $sala): bool
    {
        return $this->isOwner($user, $sala);
    }
}
