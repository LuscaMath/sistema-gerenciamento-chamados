<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function getAll(): Collection
    {
        return User::query()->orderBy('name')->get();
    }

    public function create(array $data): User
    {
        return User::create([
            ...$data,
            'is_active' => true,
        ]);
    }

    public function update(User $user, array $data): User
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return $user->refresh();
    }

    public function deactivate(User $user, User $authenticatedUser): User
    {
        if ($user->is($authenticatedUser)) {
            throw new BusinessException('Não é possível desativar o próprio usuário.');
        }

        $user->update([
            'is_active' => false,
        ]);

        return $user->refresh();
    }

    public function activate(User $user): User
    {
        $user->update([
            'is_active' => true,
        ]);

        return $user->refresh();
    }
}
