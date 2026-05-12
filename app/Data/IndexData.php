<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class IndexData extends Data
{
    /**
     * @param  array<string, mixed>  $filter
     */
    public function __construct(
        public readonly int $page = 1,
        public readonly int $per_page = 15,
        public readonly array $filter = [],
        public readonly ?string $search = null,
    ) {}
}
