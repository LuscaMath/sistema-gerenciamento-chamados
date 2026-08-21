<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('create a technician user', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/users', [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'role' => UserRole::TECHNICIAN,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.name', 'John Doe');
});