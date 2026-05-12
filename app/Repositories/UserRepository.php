<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Data\CredentialsData;
use App\Data\UserData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function create(UserData $data): UserData
    {
        $user = User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => (string) $data->password,
        ]);

        return UserData::fromModel($user);
    }

    public function authenticate(CredentialsData $credentials): UserData
    {
        $user = User::query()->where('email', $credentials->email)->firstOrFail();

        return UserData::fromModel($user);
    }

    public function createToken(int $id): string
    {
        $user = User::query()->findOrFail($id);

        return $user->createToken('api')->plainTextToken;
    }
}
