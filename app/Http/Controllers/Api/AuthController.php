<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:6|confirmed', // expects password_confirmation
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Registered successfully.',
            'user' => $user,
        ], 201);
    }

    // Login user and create token
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Revoke all previous tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user,
        ]);
    }

    // Logout user (revoke all tokens)
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    // Get authenticated user profile
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => 'Not logged in'
            ], 401);
        }

        // Load profile relationship
        $user->load('profile');

        return response()->json([
            'user' => [
                'id'           => $user->id,
                'first_name'   => $user->first_name,
                'last_name'    => $user->last_name,
                'name'         => trim($user->first_name . ' ' . $user->last_name),
                'email'        => $user->email,
                'display_name' => $user->profile->display_name ?? trim($user->first_name . ' ' . $user->last_name),
                'profile'      => $user->profile ?? null,
            ]
        ]);
    }

    // Get authenticated user (general-purpose)
    public function getUser(Request $request)
    {
        try {
            $user = User::with('profile')->find($request->user()->id);

            if (!$user) {
                return response()->json([
                    'error' => 'Not logged in'
                ], 401);
            }

            // Load profile
            $user->load('profile');

            return response()->json($user);
        } catch (\Exception $e) {
            Log::error('AuthController getUser error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Server error'
            ], 500);
        }
    }
}
