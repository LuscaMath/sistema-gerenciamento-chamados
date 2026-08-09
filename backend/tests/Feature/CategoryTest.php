<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows admin to create a category', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/categories', [
        'name' => 'Hardware',
        'description' => 'Problemas relacionados a hardware.',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.name', 'Hardware');

    $this->assertDatabaseHas('categories', [
        'name' => 'Hardware',
        'is_active' => true,
    ]);
});

it('forbids requester from creating a category', function () {
    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    Sanctum::actingAs($requester);

    $this->postJson('/api/v1/categories', [
        'name' => 'Hardware',
    ])->assertForbidden();
});

it('requires category name', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    Sanctum::actingAs($admin);

    $this->postJson('/api/v1/categories', [
        'description' => 'Teste',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('requires category name to be unique', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    Category::create([
        'name' => 'Hardware',
    ]);

    Sanctum::actingAs($admin);

    $this->postJson('/api/v1/categories', [
        'name' => 'Hardware',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('allows admin to update a category', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $category = Category::create([
        'name' => 'Hardware',
    ]);

    Sanctum::actingAs($admin);

    $this->putJson("/api/v1/categories/{$category->id}", [
        'name' => 'Infraestrutura',
        'description' => 'Problemas de infraestrutura.',
    ])->assertOk();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Infraestrutura',
    ]);
});

it('allows admin to deactivate a category', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $category = Category::create([
        'name' => 'Hardware',
    ]);

    Sanctum::actingAs($admin);

    $this->patchJson(
        "/api/v1/categories/{$category->id}/deactivate"
    )->assertOk();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'is_active' => false,
    ]);
});

it('allows admin to activate a category', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $category = Category::create([
        'name' => 'Hardware',
        'is_active' => false,
    ]);

    Sanctum::actingAs($admin);

    $this->patchJson(
        "/api/v1/categories/{$category->id}/activate"
    )->assertOk();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'is_active' => true,
    ]);
});