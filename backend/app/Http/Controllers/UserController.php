<?php

namespace App\Http\Controllers;

use App\Events\UserCreated;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function store(UserRequest $request, ): JsonResponse{
        $request->password = Hash::make($request->password);
        $user = User::create($request->all());
        $token = $this->createToken($user);

        UserCreated::dispatch($user);
        return response()->json([
            'message' => 'User created',
            'user'  => $user,
            'token' => $token
        ],201);
    }

    private function createToken(User $user): string
    {
        $token = $user->createToken('api_token');
        return $token->plainTextToken;
    }

    public function update(Request $request): JsonResponse
    {
        $image = $request->file('image')->store('images', 's3');
//        Storage::disk('s3')->put('avatars/1', $image);
//        Storage::disk('local')->put('example', $image);
        return response()->json([
            "message" => "success",
            "data" => $image
        ]);
    }
}

