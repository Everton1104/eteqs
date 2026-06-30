<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ProductionSeeder extends Seeder
{
    /**
     * Seeder para AMBIENTE DE PRODUÇÃO.
     *
     * Cria/assegura os administradores da ETEQS lendo as credenciais de
     * config('eteqs.admins') (que por sua vez lê o .env).
     *
     * - Senhas nunca são hardcoded no código; vêm do .env.
     * - Idempotente: firstOrCreate NÃO sobrescreve usuário/senha existentes.
     *   (Para redefinir uma senha, use tinker: User::where('email', ...)->update(['password' => bcrypt('nova')]))
     * - Falha com mensagem clara se alguma senha não estiver definida.
     *
     * Uso na produção:
     *   php artisan db:seed --class=ProductionSeeder --force
     */
    public function run(): void
    {
        $admins = config('eteqs.admins', []);

        foreach ($admins as $admin) {
            $this->ensureAdmin($admin);
        }
    }

    protected function ensureAdmin(array $admin): void
    {
        $email = $admin['email'] ?? null;
        $name = $admin['name'] ?? 'Administrador';
        $password = $admin['password'] ?? null;

        if (empty($email)) {
            throw new RuntimeException('Admin sem e-mail definido em config(eteqs.admins).');
        }

        if (empty($password)) {
            throw new RuntimeException(
                "Senha não definida para o admin '{$email}'. Defina a variável de ambiente ".
                "PROD_ADMINx_PASSWORD no .env da produção e rode `php artisan config:cache` novamente."
            );
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info(
            $user->wasRecentlyCreated
                ? "✔ Admin criado: {$email}"
                : "• Admin já existia (não alterado): {$email}"
        );
    }
}
