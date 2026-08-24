<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;

class TicketCommentService
{
    public function create(
        Ticket $ticket,
        User $user,
        array $data
    ): TicketComment {
        return TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'content' => $data['content'],
        ])->load('user');
    }
}