<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
