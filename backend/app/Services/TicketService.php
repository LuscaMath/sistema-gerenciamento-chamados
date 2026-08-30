<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Exceptions\BusinessException;
use App\Models\Ticket;
use App\Models\User;
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

    public function getAllFor(User $user, array $filters = []): Collection
    {
        $query = Ticket::query()
            ->with(['requester', 'technician', 'category'])
            ->latest();

        if ($user->role === UserRole::REQUESTER) {
            $query->where('requester_id', $user->id);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
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

    public function getTechnicians(): Collection
    {
        return User::query()
            ->where('role', UserRole::TECHNICIAN)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function assign(Ticket $ticket, User $technician): Ticket
    {
        if ($technician->role !== UserRole::TECHNICIAN) {
            throw new BusinessException('O usuário selecionado deve ser um técnico.');
        }

        if (! $technician->is_active) {
            throw new BusinessException('O técnico selecionado está desativado.');
        }

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
