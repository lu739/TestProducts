<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function view(?User $user): bool
    {
        return true;
    }

    public function action(?User $user): bool
    {
        return $user !== null;
    }
}
