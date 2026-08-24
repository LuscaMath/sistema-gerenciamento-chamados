<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Http\Requests\ResolveTicketRequest;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = $this->service->getAllFor(
            $request->user(),
            $request->only([
                'status',
                'priority',
                'category_id',
            ])
        );

        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request): TicketResource
    {
        $this->authorize('create', Ticket::class);

        $ticket = $this->service->create(
            $request->validated(),
            $request->user()
        );

        return new TicketResource($ticket);
    }

    public function show(Ticket $ticket): TicketResource
    {
        $this->authorize('view', $ticket);

        $ticket = $this->service->getById($ticket);

        return new TicketResource($ticket);
    }

    public function assign(Ticket $ticket): TicketResource
    {
        $this->authorize('assign', $ticket);

        $ticket = $this->service->assign(
            $ticket,
            request()->user()
        );

        return new TicketResource($ticket);
    }

    public function resolve(ResolveTicketRequest $request, Ticket $ticket): TicketResource
    {
        $this->authorize('resolve', $ticket);

        $ticket = $this->service->resolve(
            $ticket,
            $request->validated()
        );

        return new TicketResource($ticket);
    }

    public function close(Ticket $ticket): TicketResource
    {
        $this->authorize('close', $ticket);

        $ticket = $this->service->close($ticket);

        return new TicketResource($ticket);
    }
}
