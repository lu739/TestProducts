<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_is_public_and_paginated(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(20)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'price', 'category_id', 'category', 'created_at', 'updated_at'],
                ],
                'links',
                'meta',
            ]);

        $this->assertCount(15, $response->json('data'));
        $this->assertSame(15, $response->json('meta.per_page'));
    }

    public function test_products_index_accepts_per_page_between_10_and_15(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(20)->create(['category_id' => $category->id]);

        $this->assertSame(10, $this->getJson('/api/products?per_page=10')->json('meta.per_page'));
        $this->assertSame(15, $this->getJson('/api/products?per_page=15')->json('meta.per_page'));
    }

    public function test_products_index_rejects_invalid_per_page(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id]);

        $this->getJson('/api/products?per_page=100')->assertUnprocessable();
        $this->getJson('/api/products?per_page=5')->assertUnprocessable();
        $this->getJson('/api/products?per_page=not-int')->assertUnprocessable();
    }

    public function test_product_show_includes_category(): void
    {
        $category = Category::factory()->create(['name' => 'Books']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Novel',
        ]);

        $this->getJson('/api/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('data.name', 'Novel')
            ->assertJsonPath('data.category.name', 'Books');
    }

    public function test_product_store_requires_authentication(): void
    {
        $category = Category::factory()->create();

        $this->postJson('/api/products', [
            'name' => 'X',
            'price' => 10,
            'category_id' => $category->id,
        ])->assertUnauthorized();
    }

    public function test_product_store_creates_with_valid_token(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/products', [
                'name' => 'Widget',
                'description' => 'A widget',
                'price' => 19.99,
                'category_id' => $category->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Widget')
            ->assertJsonPath('data.category.id', $category->id);

        $this->assertDatabaseHas('products', [
            'name' => 'Widget',
            'category_id' => $category->id,
        ]);
    }

    public function test_product_update_and_delete_require_token(): void
    {
        $product = Product::factory()->create();

        $this->putJson('/api/products/'.$product->id, ['name' => 'Y'])->assertUnauthorized();
        $this->deleteJson('/api/products/'.$product->id)->assertUnauthorized();
    }

    public function test_product_update_with_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $product = Product::factory()->create(['name' => 'Old']);

        $this->withToken($token)
            ->patchJson('/api/products/'.$product->id, ['name' => 'New'])
            ->assertOk()
            ->assertJsonPath('data.name', 'New');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'New']);
    }

    public function test_product_delete_with_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $product = Product::factory()->create();

        $this->withToken($token)
            ->deleteJson('/api/products/'.$product->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
