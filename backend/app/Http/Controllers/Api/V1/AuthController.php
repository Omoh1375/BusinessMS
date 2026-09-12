<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
}