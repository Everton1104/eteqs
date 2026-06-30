<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Em produção: usa o ProductionSeeder (senhas via .env, sem hardcoded).
     * Em desenvolvimento: usa o AdminUserSeeder (contas de teste com senhas fixas).
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->call(ProductionSeeder::class);
        } else {
            $this->call(AdminUserSeeder::class);
        }
    }
}
