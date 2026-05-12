<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_token(): void
    {
        $response = $this->postJson(route('api.register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Str0ng-test!Pass',
            'password_confirmation' => 'Str0ng-test!Pass',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_login_returns_token(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('secret-secret'),
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => 'login@example.com',
            'password' => 'secret-secret',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_user_endpoint_requires_token(): void
    {
        $this->getJson(route('api.user'))->assertUnauthorized();
    }

    public function test_user_endpoint_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->getJson(route('api.user'), [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk()
            ->assertJson(['email' => $user->email]);
    }

    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)
            ->postJson(route('api.logout'))
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
