<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers public users as requesters', function () {
    $response = $this->withHeader('Origin', 'http://localhost:5173')->postJson('/api/v1/register', [
        'name' => 'Novo Solicitante',
        'email' => 'solicitante@example.com',
        'password' => 'password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('user.email', 'solicitante@example.com')
        ->assertJsonPath('user.role', UserRole::REQUESTER->value);

    $this->assertDatabaseHas('users', [
        'email' => 'solicitante@example.com',
        'role' => UserRole::REQUESTER->value,
    ]);
});

it('does not allow a public registration to define a role', function () {
    $this->postJson('/api/v1/register', [
        'name' => 'Administrador Indevido',
        'email' => 'admin-indevido@example.com',
        'password' => 'password123',
        'role' => UserRole::ADMIN->value,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('role');

    $this->assertDatabaseMissing('users', [
        'email' => 'admin-indevido@example.com',
    ]);
});

it('authenticates a user through the session login endpoint', function () {
    $user = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $this->withHeader('Origin', 'http://localhost:5173')->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.role', UserRole::REQUESTER->value);

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid login credentials', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'senha-incorreta',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Credenciais inválidas.');
});
