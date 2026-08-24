<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createTicketForComments(array $attributes = []): Ticket
{
    $requester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    $category = Category::create([
        'name' => 'Hardware',
    ]);

    return Ticket::create([
        'requester_id' => $requester->id,
        'category_id' => $category->id,
        'title' => 'Problema técnico',
        'description' => 'Descrição do problema.',
        'priority' => 'medium',
        'status' => TicketStatus::OPEN,
        ...$attributes,
    ]);
}

it('allows requester to comment on own ticket', function () {
    $ticket = createTicketForComments();

    Sanctum::actingAs($ticket->requester);

    $this->postJson("/api/v1/tickets/{$ticket->id}/comments", [
        'content' => 'O problema continua acontecendo.',
    ])
        ->assertCreated()
        ->assertJsonPath(
            'data.content',
            'O problema continua acontecendo.'
        );

    $this->assertDatabaseHas('ticket_comments', [
        'ticket_id' => $ticket->id,
        'user_id' => $ticket->requester_id,
        'content' => 'O problema continua acontecendo.',
    ]);
});

it('forbids requester from commenting on another requester ticket', function () {
    $ticket = createTicketForComments();

    $otherRequester = User::factory()->create([
        'role' => UserRole::REQUESTER,
    ]);

    Sanctum::actingAs($otherRequester);

    $this->postJson("/api/v1/tickets/{$ticket->id}/comments", [
        'content' => 'Comentário indevido.',
    ])->assertForbidden();
});

it('allows technician to comment on ticket', function () {
    $ticket = createTicketForComments();

    $technician = User::factory()->create([
        'role' => UserRole::TECHNICIAN,
    ]);

    Sanctum::actingAs($technician);

    $this->postJson("/api/v1/tickets/{$ticket->id}/comments", [
        'content' => 'Estou analisando o problema.',
    ])->assertCreated();
});

it('allows admin to comment on ticket', function () {
    $ticket = createTicketForComments();

    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    Sanctum::actingAs($admin);

    $this->postJson("/api/v1/tickets/{$ticket->id}/comments", [
        'content' => 'Comentário administrativo.',
    ])->assertCreated();
});

it('forbids comments on closed ticket', function () {
    $ticket = createTicketForComments([
        'status' => TicketStatus::CLOSED,
        'closed_at' => now(),
    ]);

    Sanctum::actingAs($ticket->requester);

    $this->postJson("/api/v1/tickets/{$ticket->id}/comments", [
        'content' => 'Novo comentário.',
    ])->assertForbidden();
});

it('allows authorized user to list ticket comments', function () {
    $ticket = createTicketForComments();

    Sanctum::actingAs($ticket->requester);

    $this->postJson("/api/v1/tickets/{$ticket->id}/comments", [
        'content' => 'Primeiro comentário.',
    ])->assertCreated();

    $this->getJson("/api/v1/tickets/{$ticket->id}/comments")
        ->assertOk()
        ->assertJsonPath('data.0.content', 'Primeiro comentário.');
});