<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@email.com',
            'password' => '12345678',
            'role' => UserRole::ADMIN,
        ]);

        User::create([
            'name' => 'Técnico',
            'email' => 'tecnico@email.com',
            'password' => '12345678',
            'role' => UserRole::TECHNICIAN,
        ]);

        User::create([
            'name' => 'Solicitante',
            'email' => 'requester@email.com',
            'password' => '12345678',
            'role' => UserRole::REQUESTER,
        ]);
    }
}