<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class IndexData extends Data
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $per_page = 15,
    ) {}
}
