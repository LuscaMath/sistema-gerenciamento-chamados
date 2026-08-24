<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketCommentRequest;
use App\Http\Resources\TicketCommentResource;
use App\Models\Ticket;
use App\Services\TicketCommentService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketCommentController extends Controller
{
    public function __construct(
        private readonly TicketCommentService $service
    ) {
    }

    public function index(Ticket $ticket): AnonymousResourceCollection
    {
        $this->authorize('view', $ticket);

        $comments = $ticket->comments()
            ->with('user')
            ->oldest()
            ->get();

        return TicketCommentResource::collection($comments);
    }

    public function store(
        StoreTicketCommentRequest $request,
        Ticket $ticket
    ): TicketCommentResource {
        $this->authorize('comment', $ticket);

        $comment = $this->service->create(
            $ticket,
            $request->user(),
            $request->validated()
        );

        return new TicketCommentResource($comment);
    }
}