<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Http\Requests\ResolveTicketRequest;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $service
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = $this->service->getAllFor(
            request()->user()
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
}
