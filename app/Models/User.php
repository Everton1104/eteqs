<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Papéis disponíveis no sistema.
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_PROFESSOR = 'professor';

    /**
     * Verifica se o usuário é administrador.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Verifica se o usuário é professor.
     */
    public function isProfessor(): bool
    {
        return $this->role === self::ROLE_PROFESSOR;
    }

    /**
     * Scope: filtra por nome ou e-mail (busca da listagem).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeFiltro($query, ?string $termo)
    {
        if (blank($termo)) {
            return $query;
        }

        return $query->where(function ($q) use ($termo) {
            $q->where('name', 'like', "%{$termo}%")
              ->orWhere('email', 'like', "%{$termo}%");
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
