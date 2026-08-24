<?php

use App\Enums\TicketPriority;
use App\Enums\UserRole;
use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows requester to create a ticket', function () {
    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $category = Category::create([
        'name' => 'Hardware',
    ]);

    Sanctum::actingAs($requester);

    $response = $this->postJson('/api/v1/tickets', [
        'category_id' => $category->id,
        'title' => 'Computador não liga',
        'description' => 'O computador da sala não está iniciando.',
        'priority' => TicketPriority::HIGH->value,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.title', 'Computador não liga')
        ->assertJsonPath('data.status', 'open');

    $this->assertDatabaseHas('tickets', [
        'requester_id' => $requester->id,
        'title' => 'Computador não liga',
        'status' => 'open',
    ]);
});

it('forbids requester from viewing another requester ticket', function () {
    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $otherRequester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $category = Category::create([
        'name' => 'Hardware',
    ]);

    $ticket = \App\Models\Ticket::create([
        'requester_id' => $otherRequester->id,
        'category_id' => $category->id,
        'title' => 'Teste',
        'description' => 'Chamado de outro usuário.',
        'priority' => 'medium',
        'status' => 'open',
    ]);

    Sanctum::actingAs($requester);

    $this->getJson("/api/v1/tickets/{$ticket->id}")
        ->assertForbidden();
});

it('allows technician to assign an open ticket', function () {
    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $technician = User::factory()->create([
        'role' => UserRole::TECHNICIAN,
    ]);

    $category = Category::create([
        'name' => 'Hardware',
    ]);

    $ticket = Ticket::create([
        'requester_id' => $requester->id,
        'category_id' => $category->id,
        'title' => 'Computador não liga',
        'description' => 'Teste',
        'priority' => 'medium',
        'status' => 'open',
    ]);

    Sanctum::actingAs($technician);

    $this->patchJson("/api/v1/tickets/{$ticket->id}/assign")
        ->assertOk();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'technician_id' => $technician->id,
        'status' => 'in_progress',
    ]);
});

it('forbids another technician from assigning an assigned ticket', function () {
    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $technician = User::factory()->create([
        'role' => UserRole::TECHNICIAN,
    ]);

    $otherTechnician = User::factory()->create([
        'role' => UserRole::TECHNICIAN,
    ]);

    $category = Category::create([
        'name' => 'Hardware',
    ]);

    $ticket = Ticket::create([
        'requester_id' => $requester->id,
        'technician_id' => $technician->id,
        'category_id' => $category->id,
        'title' => 'Teste',
        'description' => 'Teste',
        'priority' => 'medium',
        'status' => 'in_progress',
    ]);

    Sanctum::actingAs($otherTechnician);

    $this->patchJson("/api/v1/tickets/{$ticket->id}/assign")
        ->assertUnprocessable();
});

it('allows responsible technician to resolve a ticket', function () {
    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $technician = User::factory()->create([
        'role' => UserRole::TECHNICIAN,
    ]);

    $category = Category::create([
        'name' => 'Hardware',
    ]);

    $ticket = Ticket::create([
        'requester_id' => $requester->id,
        'technician_id' => $technician->id,
        'category_id' => $category->id,
        'title' => 'Teste',
        'description' => 'Teste',
        'priority' => 'medium',
        'status' => 'in_progress',
    ]);

    Sanctum::actingAs($technician);

    $this->patchJson("/api/v1/tickets/{$ticket->id}/resolve", [
        'solution' => 'Problema corrigido.',
    ])->assertOk();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'resolved',
        'solution' => 'Problema corrigido.',
    ]);
});

it('allows requester to close a resolved ticket', function () {
    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $technician = User::factory()->create([
        'role' => UserRole::TECHNICIAN,
    ]);

    $category = Category::create([
        'name' => 'Hardware',
    ]);

    $ticket = Ticket::create([
        'requester_id' => $requester->id,
        'technician_id' => $technician->id,
        'category_id' => $category->id,
        'title' => 'Teste',
        'description' => 'Teste',
        'priority' => 'medium',
        'status' => 'resolved',
        'solution' => 'Resolvido.',
        'resolved_at' => now(),
    ]);

    Sanctum::actingAs($requester);

    $this->patchJson("/api/v1/tickets/{$ticket->id}/close")
        ->assertOk();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'closed',
    ]);
});

it('filters tickets by status', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $category = Category::create([
        'name' => 'Hardware',
    ]);

    Ticket::create([
        'requester_id' => User::factory()->create([
            'role' => UserRole::REQUESTER,
        ])->id,
        'category_id' => $category->id,
        'title' => 'Chamado aberto',
        'description' => 'Teste',
        'priority' => 'medium',
        'status' => 'open',
    ]);

    Ticket::create([
        'requester_id' => User::factory()->create([
            'role' => UserRole::REQUESTER,
        ])->id,
        'category_id' => $category->id,
        'title' => 'Chamado resolvido',
        'description' => 'Teste',
        'priority' => 'medium',
        'status' => 'resolved',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/tickets?status=open');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Chamado aberto');
});

it('filters tickets by priority', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $category = Category::create([
        'name' => 'Hardware',
    ]);

    Ticket::create([
        'requester_id' => $requester->id,
        'category_id' => $category->id,
        'title' => 'Alta prioridade',
        'description' => 'Teste',
        'priority' => 'high',
        'status' => 'open',
    ]);

    Ticket::create([
        'requester_id' => $requester->id,
        'category_id' => $category->id,
        'title' => 'Baixa prioridade',
        'description' => 'Teste',
        'priority' => 'low',
        'status' => 'open',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/tickets?priority=high');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Alta prioridade');
});

it('filters tickets by multiple filters', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $hardware = Category::create([
        'name' => 'Hardware',
    ]);

    $software = Category::create([
        'name' => 'Software',
    ]);

    Ticket::create([
        'requester_id' => $requester->id,
        'category_id' => $hardware->id,
        'title' => 'Chamado correto',
        'description' => 'Teste',
        'priority' => 'high',
        'status' => 'open',
    ]);

    Ticket::create([
        'requester_id' => $requester->id,
        'category_id' => $software->id,
        'title' => 'Outro chamado',
        'description' => 'Teste',
        'priority' => 'high',
        'status' => 'open',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson(
        "/api/v1/tickets?status=open&priority=high&category_id={$hardware->id}"
    );

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Chamado correto');
});
