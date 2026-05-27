<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Administrador',
            'email' => 'admin@accesos.com',
            'password' => bcrypt('admin1234'),
        ]);

        $user->assignRole(['administrador', 'autorizador']);
    }
}