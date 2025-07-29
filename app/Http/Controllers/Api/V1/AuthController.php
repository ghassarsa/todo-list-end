<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:6',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $freePlan = Plan::where('name', 'Free')->first();
        if (!$freePlan) {
            return response()->json(['message' => 'Default plan not found.'], 500);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'plan_id' => $freePlan->id,
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Login gagal, email atau password salah'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil!',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function me() {
        return response()->json(Auth::user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function oAuthUrl() {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $url]);
    }

    public function oAuthCallback (Request $request) {
        $user = Socialite::driver('google')->stateless()->user();

        $existingUser = User::where('email', $user->getEmail())->first();
        if ($existingUser) {
            $token = $existingUser->createToken('auth_token')->plainTextToken;
            $existingUser->update([
                'avatar' => $user->avatar ?? $user->getAvatar()
            ]);
            return response()->json([
                'massage' => 'Login Successful',
                'user' => $existingUser,
                'token' => $token,
            ]);
        } else {
            $freePlan = Plan::where('name', 'Free')->first();
            if (!$freePlan) {
                return response()->json(['massage' => 'Default Plan Not Found'], 500);
            }

            $newUser = User::create([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'password' => null,
                'plan_id' => $freePlan->id,
                'avatar' => $user->getAvatar(),
            ]);

            $token = $newUser->createToken('auth_token')->plainTextToken;
            return response()->json([
                'massage' => 'User created and logged in successfully',
                'user' => $newUser,
                'token' => $token,
            ], 201);
        }
    }
}
