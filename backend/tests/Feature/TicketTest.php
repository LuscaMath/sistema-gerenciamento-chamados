<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
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

    $ticket = Ticket::create([
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

it('allows admin to assign an open ticket to a technician', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $technician = User::factory()->create([
        'role' => UserRole::TECHNICIAN,
    ]);

    $ticket = createOpenTicket();

    Sanctum::actingAs($admin);

    $this->patchJson("/api/v1/tickets/{$ticket->id}/assign-technician", [
        'technician_id' => $technician->id,
    ])
        ->assertOk()
        ->assertJsonPath('data.technician.id', $technician->id)
        ->assertJsonPath('data.status', TicketStatus::IN_PROGRESS->value);

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'technician_id' => $technician->id,
        'status' => TicketStatus::IN_PROGRESS->value,
    ]);
});

it('forbids non-admin users from assigning a technician manually', function () {
    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $technician = User::factory()->create([
        'role' => UserRole::TECHNICIAN,
    ]);

    $ticket = createOpenTicket();

    Sanctum::actingAs($requester);

    $this->patchJson("/api/v1/tickets/{$ticket->id}/assign-technician", [
        'technician_id' => $technician->id,
    ])->assertForbidden();
});

it('requires the manually assigned user to be a technician', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $ticket = createOpenTicket();

    Sanctum::actingAs($admin);

    $this->patchJson("/api/v1/tickets/{$ticket->id}/assign-technician", [
        'technician_id' => $requester->id,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('technician_id');
});

it('does not allow admin to reassign a ticket that already has a technician', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $currentTechnician = User::factory()->create([
        'role' => UserRole::TECHNICIAN,
    ]);

    $newTechnician = User::factory()->create([
        'role' => UserRole::TECHNICIAN,
    ]);

    $ticket = createOpenTicket([
        'technician_id' => $currentTechnician->id,
        'status' => TicketStatus::IN_PROGRESS,
    ]);

    Sanctum::actingAs($admin);

    $this->patchJson("/api/v1/tickets/{$ticket->id}/assign-technician", [
        'technician_id' => $newTechnician->id,
    ])->assertUnprocessable();
});

it('allows manual assignment only for open tickets', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $technician = User::factory()->create([
        'role' => UserRole::TECHNICIAN,
    ]);

    $ticket = createOpenTicket([
        'status' => TicketStatus::RESOLVED,
    ]);

    Sanctum::actingAs($admin);

    $this->patchJson("/api/v1/tickets/{$ticket->id}/assign-technician", [
        'technician_id' => $technician->id,
    ])->assertUnprocessable();
});

it('forbids admin from assuming or resolving tickets', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $technician = User::factory()->create([
        'role' => UserRole::TECHNICIAN,
    ]);

    $openTicket = createOpenTicket();
    $inProgressTicket = createOpenTicket([
        'technician_id' => $technician->id,
        'status' => TicketStatus::IN_PROGRESS,
    ]);

    Sanctum::actingAs($admin);

    $this->patchJson("/api/v1/tickets/{$openTicket->id}/assign")
        ->assertForbidden();

    $this->patchJson("/api/v1/tickets/{$inProgressTicket->id}/resolve", [
        'solution' => 'Tentativa administrativa.',
    ])->assertForbidden();
});

it('lists only technicians for administrators', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $technician = User::factory()->create([
        'role' => UserRole::TECHNICIAN,
    ]);

    User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    Sanctum::actingAs($admin);

    $this->getJson('/api/v1/technicians')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $technician->id);
});

it('forbids non-admin users from listing technicians', function () {
    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    Sanctum::actingAs($requester);

    $this->getJson('/api/v1/technicians')->assertForbidden();
});

function createOpenTicket(array $attributes = []): Ticket
{
    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $category = Category::create([
        'name' => fake()->unique()->word(),
    ]);

    return Ticket::create([
        'requester_id' => $requester->id,
        'category_id' => $category->id,
        'title' => 'Chamado para atribuição',
        'description' => 'Descrição do chamado.',
        'priority' => TicketPriority::MEDIUM,
        'status' => TicketStatus::OPEN,
        ...$attributes,
    ]);
}
