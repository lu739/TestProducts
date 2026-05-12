<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Data\CredentialsData;
use App\Data\UserData;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->userRepository->create($request->toUserData());
        $token = $this->userRepository->createToken($user->id);

        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $this->userRepository->authenticate(CredentialsData::from($validated));

        $token = $this->userRepository->createToken($user->id);

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(UserData::fromModel($request->user())->toArray());
    }

    public function logout(Request $request): JsonResponse
    {
        if ($plain = $request->bearerToken()) {
            PersonalAccessToken::findToken($plain)?->delete();
        } else {
            $request->user()->currentAccessToken()?->delete();
        }

        return response()->json(['message' => 'ok']);
    }
}
