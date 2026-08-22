<?php

namespace App\Services;

use App\Models\Ticket;
use App\Enums\TicketStatus;
use App\Models\User;
use App\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\Collection;

class TicketService
{
    public function create(array $data, User $requester): Ticket
    {
        $ticket = Ticket::create([
            ...$data,
            'requester_id' => $requester->id,
            'status' => TicketStatus::OPEN,
        ]);

        return $ticket->load([
            'requester',
            'technician',
            'category',
        ]);
    }

    public function getAllFor(User $user): Collection
    {
        $query = Ticket::query()
            ->with(['requester', 'technician', 'category'])
            ->latest();

        if ($user->role === \App\Enums\UserRole::REQUESTER) {
            $query->where('requester_id', $user->id);
        }

        return $query->get();
    }

    public function getById(Ticket $ticket): Ticket
    {
        return $ticket->load([
            'requester',
            'technician',
            'category',
        ]);
    }

    public function assign(Ticket $ticket, User $technician): Ticket
    {
        if ($ticket->technician_id !== null) {
            throw new BusinessException('O chamado já possui um técnico responsável.');
        }

        if ($ticket->status !== TicketStatus::OPEN) {
            throw new BusinessException('Somente chamados abertos podem ser assumidos.');
        }

        $ticket->update([
            'technician_id' => $technician->id,
            'status' => TicketStatus::IN_PROGRESS,
        ]);

        return $ticket->refresh()->load([
            'requester',
            'technician',
            'category',
        ]);
    }

    public function resolve(Ticket $ticket, array $data): Ticket
    {
        if ($ticket->status !== TicketStatus::IN_PROGRESS) {
            throw new BusinessException(
                'Somente chamados em atendimento podem ser resolvidos.'
            );
        }

        $ticket->update([
            'solution' => $data['solution'],
            'status' => TicketStatus::RESOLVED,
            'resolved_at' => now(),
        ]);

        return $ticket->refresh()->load([
            'requester',
            'technician',
            'category',
        ]);
    }

    public function close(Ticket $ticket): Ticket
    {
        if ($ticket->status !== TicketStatus::RESOLVED) {
            throw new BusinessException(
                'Somente chamados resolvidos podem ser fechados.'
            );
        }

        $ticket->update([
            'status' => TicketStatus::CLOSED,
            'closed_at' => now(),
        ]);

        return $ticket->refresh()->load([
            'requester',
            'technician',
            'category',
        ]);
    }
}
