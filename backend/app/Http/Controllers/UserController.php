<?php

namespace App\Http\Controllers;

use App\Events\UserCreated;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
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
        $userData =  $request->except('image');
        $user = User::where('uuid', $userData['uuid']);

        if($request->file('image')){
            $image = $request->file('image')->store('images', 'local');
            $user->avatar_url = $image;
        }

        $user->update($user);
        $user->save();

//        $image = $request->file('image')->store('images', 's3');
//        Storage::disk('s3')->put('avatars/1', $image);
//        Storage::disk('local')->put('example', $image);

        return response()->json([
            "message" => "success",
            "data" => $user
        ],  201);
    }

    public function show(Request $request)
    {
        $data = $request->all();
        $user = User::where('username', $data['username'])->get()->first();

        if(!$user){
            return response()->json([
                'message' => "User does not exist",
            ], 404);
        }

        $verifiedUser = Hash::check($data['password'], $user['password']);

        if(!$verifiedUser){
            return response()->json([
                'message' => "Incorrect credentials",
            ], 401);
        }

        return response()->json([
            "message" => "User logged in",
            $user => New UserResource($user)
        ]);
    }

    public function delete(Request $request)
    {
        User::where('uuid', $request->uuid)->get()->first()->delete();

        return response()->json([
            "message" => "User deleted"
        ], 204);
    }
}

