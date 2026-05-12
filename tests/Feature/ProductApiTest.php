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
                    '*' => ['id', 'name', 'description', 'price', 'category_id', 'category', 'created_at', 'updated_at', 'deleted_at'],
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

    public function test_products_index_is_ordered_by_id_descending(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id]);

        $ids = collect($this->getJson('/api/products')->json('data'))->pluck('id')->all();

        $sorted = $ids;
        rsort($sorted, SORT_NUMERIC);

        $this->assertSame($sorted, $ids);
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
            ->assertJsonPath('data.category.name', 'Books')
            ->assertJsonPath('data.deleted_at', null);
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

    public function test_product_store_validates_name_price_and_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/products', [
                'name' => '',
                'price' => 0,
                'category_id' => 999_999,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'price', 'category_id']);

        $this->withToken($token)
            ->postJson('/api/products', [
                'name' => 'Ok',
                'price' => 0,
                'category_id' => $category->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price'])
            ->assertJsonFragment(['Цена должна быть числом больше 0.']);

        $this->withToken($token)
            ->postJson('/api/products', [
                'name' => 'Ok',
                'price' => 10,
                'category_id' => $category->id,
            ])
            ->assertCreated();
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

    public function test_product_delete_with_token_soft_deletes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $product = Product::factory()->create();

        $this->withToken($token)
            ->deleteJson('/api/products/'.$product->id)
            ->assertNoContent();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_product_force_destroy_with_token_removes_row(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $product = Product::factory()->create();

        $this->withToken($token)
            ->deleteJson('/api/products/'.$product->id.'/force')
            ->assertNoContent();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_products_index_excludes_soft_deleted(): void
    {
        $category = Category::factory()->create();
        $visible = Product::factory()->create(['category_id' => $category->id]);
        $hidden = Product::factory()->create(['category_id' => $category->id]);
        $hidden->delete();

        $response = $this->getJson('/api/products');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($visible->id, $ids);
        $this->assertNotContains($hidden->id, $ids);
        foreach ($response->json('data') as $row) {
            $this->assertNull($row['deleted_at']);
        }
    }

    public function test_products_index_with_token_includes_soft_deleted(): void
    {
        $category = Category::factory()->create();
        $visible = Product::factory()->create(['category_id' => $category->id]);
        $hidden = Product::factory()->create(['category_id' => $category->id]);
        $hidden->delete();

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/products');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($visible->id, $ids);
        $this->assertContains($hidden->id, $ids);

        $hiddenRow = collect($response->json('data'))->firstWhere('id', $hidden->id);
        $this->assertNotNull($hiddenRow);
        $this->assertNotNull($hiddenRow['deleted_at']);
    }

    public function test_guest_cannot_view_soft_deleted_product(): void
    {
        $product = Product::factory()->create();
        $product->delete();

        $this->getJson('/api/products/'.$product->id)->assertNotFound();
    }

    public function test_authenticated_user_can_view_soft_deleted_product(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $product = Product::factory()->create();
        $product->delete();

        $response = $this->withToken($token)
            ->getJson('/api/products/'.$product->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $product->id);

        $this->assertNotNull($response->json('data.deleted_at'));
    }

    public function test_cannot_update_soft_deleted_product(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $product = Product::factory()->create(['name' => 'Gone']);
        $product->delete();

        $this->withToken($token)
            ->patchJson('/api/products/'.$product->id, ['name' => 'Back'])
            ->assertNotFound();
    }

    public function test_product_restore_with_token_restores_soft_deleted(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $product = Product::factory()->create(['name' => 'Hidden']);
        $product->delete();

        $this->withToken($token)
            ->postJson('/api/products/'.$product->id.'/restore')
            ->assertOk()
            ->assertJsonPath('data.name', 'Hidden')
            ->assertJsonPath('data.deleted_at', null);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'deleted_at' => null,
        ]);
    }

    public function test_product_restore_returns_422_when_not_trashed(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $product = Product::factory()->create();

        $this->withToken($token)
            ->postJson('/api/products/'.$product->id.'/restore')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Товар не скрыт, восстановление не требуется.');
    }

    public function test_product_restore_requires_authentication(): void
    {
        $product = Product::factory()->create();
        $product->delete();

        $this->postJson('/api/products/'.$product->id.'/restore')->assertUnauthorized();
    }
}
