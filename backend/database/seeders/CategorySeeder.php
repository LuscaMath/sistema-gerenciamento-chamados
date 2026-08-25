<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::insert([
            [
                'name' => 'Hardware',
                'description' => 'Problemas relacionados a equipamentos físicos.',
                'is_active' => true,
            ],
            [
                'name' => 'Software',
                'description' => 'Problemas relacionados a sistemas e programas.',
                'is_active' => true,
            ],
            [
                'name' => 'Rede',
                'description' => 'Problemas relacionados a conexão e infraestrutura de rede.',
                'is_active' => true,
            ],
            [
                'name' => 'Acesso',
                'description' => 'Problemas relacionados a autenticação e permissões.',
                'is_active' => true,
            ],
        ]);
    }
}