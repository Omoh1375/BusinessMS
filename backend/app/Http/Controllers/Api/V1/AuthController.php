<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $business = Business::create([
                'name' => $data['business_name'],
                'business_type' => $data['business_type'],
            ]);

            $business->users()->attach($user->id, [
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $token = $user->createToken('businessms')->plainTextToken;

            return [
                'user' => $user,
                'business' => $business,
                'token' => $token,
            ];
        });

        return response()->json([
            'message' => 'Registration successful.',
            'data' => $result,
        ], 201);
    }

    public function login(Request $request): JsonResponse
        {
            $credentials = $request->validate([
                'email' => [
                    'required',
                    'email',
                ],

                'password' => [
                    'required',
                    'string',
                ],
            ]);

            $user = User::where('email', $credentials['email'])->first();

            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'message' => 'The provided credentials are incorrect.',
                ], 422);
            }

            $token = $user->createToken('businessms')->plainTextToken;

            return response()->json([
                'message' => 'Login successful.',
                'data' => [
                    'user' => $user,
                    'businesses' => $user->businesses,
                    'token' => $token,
                ],
            ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Authenticated user retrieved successfully.',
            'data' => [
                'user' => $user,
                'businesses' => $user->businesses,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
        {
            $request->user()->currentAccessToken()?->delete();

            return response()->json([
                'message' => 'Logout successful.',
            ]);
    }

    public function currentBusiness(): JsonResponse
    {
        $business = app('currentBusiness');

        return response()->json([
            'message' => 'Current business retrieved successfully.',
            'data' => [
                'business' => $business,
            ],
        ]);
    }
}