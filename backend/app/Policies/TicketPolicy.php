<?php

namespace App\Policies;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->role === UserRole::ADMIN) {
            return true;
        }

        if ($user->role === UserRole::TECHNICIAN) {
            return true;
        }

        return $ticket->requester_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::REQUESTER;
    }

    /**
     * Determine whether the user can assign a ticket.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->role === UserRole::TECHNICIAN;
    }

    /**
     * Determine whether the user can view technicians available for assignment.
     */
    public function viewTechnicians(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can assign a technician to a ticket.
     */
    public function assignTechnician(User $user, Ticket $ticket): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can resolve a ticket.
     */
    public function resolve(User $user, Ticket $ticket): bool
    {
        return $user->role === UserRole::TECHNICIAN && $ticket->technician_id === $user->id;
    }

    /**
     * Determine whether the user can close a ticket.
     */
    public function close(User $user, Ticket $ticket): bool
    {
        return $user->role === UserRole::REQUESTER && $ticket->requester_id === $user->id;
    }

    /**
     * Determine whether the user can comment the model.
     */
    public function comment(User $user, Ticket $ticket): bool
    {
        if ($ticket->status === TicketStatus::CLOSED) {
            return false;
        }

        if ($user->role === UserRole::ADMIN) {
            return true;
        }

        if ($user->role === UserRole::TECHNICIAN) {
            return true;
        }

        return $user->role === UserRole::REQUESTER
            && $ticket->requester_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false;
    }
}
