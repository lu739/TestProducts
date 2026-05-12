<?php

namespace App\Policies;

use App\Models\User;

class ProductPolicy
{
    public function action(?User $user): bool
    {
        return $user !== null;
    }
}
