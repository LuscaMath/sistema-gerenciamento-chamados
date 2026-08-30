<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows admin to list managed users', function () {
    $admin = User::factory()->create([
        'name' => 'Administrador',
        'role' => UserRole::ADMIN,
    ]);

    $technician = User::factory()->create([
        'name' => 'Técnico',
        'role' => UserRole::TECHNICIAN,
    ]);

    Sanctum::actingAs($admin);

    $this->getJson('/api/v1/users')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.1.id', $technician->id);
});

it('allows admin to create users with a selected role', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    Sanctum::actingAs($admin);

    $this->postJson('/api/v1/users', [
        'name' => 'Nova Técnica',
        'email' => 'tecnica@example.com',
        'password' => 'password123',
        'role' => UserRole::TECHNICIAN->value,
    ])
        ->assertCreated()
        ->assertJsonPath('data.email', 'tecnica@example.com')
        ->assertJsonPath('data.role', UserRole::TECHNICIAN->value);

    $user = User::query()->where('email', 'tecnica@example.com')->firstOrFail();

    expect(Hash::check('password123', $user->password))->toBeTrue();
});

it('allows admin to update a managed user without replacing the password', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $user = User::factory()->create([
        'name' => 'Solicitante Antigo',
        'email' => 'solicitante@example.com',
        'role' => UserRole::REQUESTER,
    ]);

    $password = $user->password;

    Sanctum::actingAs($admin);

    $this->putJson("/api/v1/users/{$user->id}", [
        'name' => 'Técnico Atualizado',
        'email' => 'tecnico@example.com',
        'role' => UserRole::TECHNICIAN->value,
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Técnico Atualizado')
        ->assertJsonPath('data.role', UserRole::TECHNICIAN->value);

    $user->refresh();

    expect($user->password)->toBe($password);
});

it('forbids non-admin users from managing users', function () {
    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    Sanctum::actingAs($requester);

    $this->getJson('/api/v1/users')->assertForbidden();

    $this->postJson('/api/v1/users', [
        'name' => 'Usuário Indevido',
        'email' => 'indevido@example.com',
        'password' => 'password123',
        'role' => UserRole::ADMIN->value,
    ])->assertForbidden();
});

it('validates the role of managed users', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    Sanctum::actingAs($admin);

    $this->postJson('/api/v1/users', [
        'name' => 'Usuário Inválido',
        'email' => 'invalido@example.com',
        'password' => 'password123',
        'role' => 'invalid',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('role');
});
