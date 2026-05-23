<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'              => 'Juan Solicitante',
                'email'             => 'solicitante@accesos.com',
                'password'          => Hash::make('solicitante123'),
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'María Autorizadora',
                'email'             => 'autorizador@accesos.com',
                'password'          => Hash::make('autorizador123'),
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'Carlos Vigilante',
                'email'             => 'vigilante@accesos.com',
                'password'          => Hash::make('vigilante123'),
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ];

        foreach ($users as $userData) {
            $user = \App\Models\User::create($userData);
            $roleName = explode('@', $userData['email'])[0];
            $user->assignRole($roleName);
        }
    }
}