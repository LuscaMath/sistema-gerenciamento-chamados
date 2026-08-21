<?php

namespace App\Services;

use App\Models\Ticket;
use App\Enums\TicketStatus;
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
}
