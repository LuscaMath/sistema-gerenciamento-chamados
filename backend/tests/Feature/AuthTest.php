<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('does not expose a public registration endpoint', function () {
    $this->postJson('/api/v1/register')->assertNotFound();
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

it('rejects login from a deactivated user', function () {
    $user = User::factory()->create([
        'is_active' => false,
    ]);

    $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Credenciais inválidas.');
});

it('blocks requests from a deactivated authenticated user', function () {
    $user = User::factory()->create([
        'is_active' => false,
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/me')
        ->assertForbidden()
        ->assertJsonPath('message', 'Usuário desativado.');
});
