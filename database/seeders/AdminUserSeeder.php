<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Cria os administradores (professores) da ETEQS.
     * - id 1: Everton Rodrigues (senha via ADMIN_SEED_PASSWORD no .env)
     * - id 2: Administrador ETEQS (senha fixa de dev "eteqs@123")
     * Idempotente: firstOrCreate não recria nem sobrescreve existentes.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'everton.rodrigues1104@gmail.com'],
            [
                'name' => 'Everton Rodrigues',
                'password' => Hash::make(env('ADMIN_SEED_PASSWORD', '99771122')),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@eteqs.org.br'],
            [
                'name' => 'Administrador ETEQS',
                'password' => Hash::make('eteqs@123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
