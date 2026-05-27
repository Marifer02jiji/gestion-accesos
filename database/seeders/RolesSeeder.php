<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'solicitante']);
        Role::create(['name' => 'autorizador']);
        Role::create(['name' => 'vigilante']);
        Role::create(['name' => 'administrador']);
    }
}