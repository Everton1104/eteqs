<?php

/*
|--------------------------------------------------------------------------
| Configuração da ETEQS
|--------------------------------------------------------------------------
| Credenciais dos administradores usadas pelo ProductionSeeder.
| As senhas VÊM do .env (não fiquem versionadas). Lidas via config() para
| continuarem válidas após `php artisan config:cache` na produção.
*/

return [

    'admins' => [
        [
            'email' => env('PROD_ADMIN1_EMAIL', 'everton.rodrigues1104@gmail.com'),
            'name' => env('PROD_ADMIN1_NAME', 'Everton Rodrigues'),
            'password' => env('PROD_ADMIN1_PASSWORD'),
        ],
        [
            'email' => env('PROD_ADMIN2_EMAIL', 'admin@eteqs.org.br'),
            'name' => env('PROD_ADMIN2_NAME', 'Administrador ETEQS'),
            'password' => env('PROD_ADMIN2_PASSWORD'),
        ],
    ],

];
