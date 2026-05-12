<?php

namespace App\Data;

use Spatie\LaravelData\Data;

final class CredentialsData extends Data
{
    public function __construct(
        public ?string $email = null,
        public ?string $password = null,
    ) {}
}
