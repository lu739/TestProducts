<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = Category::query()->pluck('id');

        if ($categoryIds->isEmpty()) {
            return;
        }

        $ids = $categoryIds->all();

        foreach (range(1, 40) as $_) {
            Product::factory()->create([
                'category_id' => fake()->randomElement($ids),
            ]);
        }
    }
}
