<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //Register new user
    public function register(RegisterRequest $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role,
        ]);
        return response()->noContent();
    }

    //Login user
    public function login(LoginRequest $request)
    {
        //validate the request then check if email and password is correct
        if (!Auth::attempt($request->validated())) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        //create authenticated user's token
        $token = Auth::user()->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token
        ]);
    }

    //me
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    //Logout user
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->noContent();
    }
}
