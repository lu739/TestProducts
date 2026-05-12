<?php

namespace App\Contracts\Repositories;

use App\Data\CredentialsData;
use App\Data\UserData;
use App\Models\User;

interface UserRepositoryInterface
{
    public function create(UserData $data): UserData;

    public function authenticate(CredentialsData $credentials): UserData;

    public function createToken(int $id): string;
}
