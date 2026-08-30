<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $service
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        return UserResource::collection($this->service->getAll());
    }

    public function store(StoreUserRequest $request): UserResource
    {
        $this->authorize('create', User::class);

        return new UserResource($this->service->create($request->validated()));
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $this->authorize('update', $user);

        return new UserResource($this->service->update($user, $request->validated()));
    }

    public function deactivate(User $user): UserResource
    {
        $this->authorize('deactivate', $user);

        return new UserResource($this->service->deactivate($user, request()->user()));
    }

    public function activate(User $user): UserResource
    {
        $this->authorize('activate', $user);

        return new UserResource($this->service->activate($user));
    }
}
