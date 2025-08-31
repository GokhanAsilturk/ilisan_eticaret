<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Address;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:today'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'birth_date' => $request->birth_date
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        // Merge guest cart if session_token provided
        if ($request->filled('session_token')) {
            $this->cartService->mergeGuestCart($user, $request->session_token);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'birth_date' => $user->birth_date,
                'email_verified_at' => $user->email_verified_at
            ],
            'token' => $token
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        // Merge guest cart if session_token provided
        if ($request->filled('session_token')) {
            $this->cartService->mergeGuestCart($user, $request->session_token);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'birth_date' => $user->birth_date,
                'email_verified_at' => $user->email_verified_at
            ],
            'token' => $token
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'birth_date' => $user->birth_date,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at
            ]
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:today'
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'birth_date' => $request->birth_date
        ]);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'birth_date' => $user->birth_date,
                'email_verified_at' => $user->email_verified_at
            ]
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()]
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }

    public function addresses(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'addresses' => $addresses->map(function ($address) {
                return [
                    'id' => $address->id,
                    'title' => $address->title,
                    'first_name' => $address->first_name,
                    'last_name' => $address->last_name,
                    'phone' => $address->phone,
                    'address_line_1' => $address->address_line_1,
                    'address_line_2' => $address->address_line_2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country,
                    'is_default' => $address->is_default
                ];
            })
        ]);
    }

    public function storeAddress(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:2|in:TR',
            'is_default' => 'boolean'
        ]);

        $user = $request->user();

        // If this is set as default, remove default from other addresses
        if ($request->boolean('is_default')) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            'title' => $request->title,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'is_default' => $request->boolean('is_default')
        ]);

        return response()->json([
            'address' => [
                'id' => $address->id,
                'title' => $address->title,
                'first_name' => $address->first_name,
                'last_name' => $address->last_name,
                'phone' => $address->phone,
                'address_line_1' => $address->address_line_1,
                'address_line_2' => $address->address_line_2,
                'city' => $address->city,
                'state' => $address->state,
                'postal_code' => $address->postal_code,
                'country' => $address->country,
                'is_default' => $address->is_default
            ]
        ], 201);
    }

    public function updateAddress(Request $request, int $addressId): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($addressId);

        $request->validate([
            'title' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:2|in:TR',
            'is_default' => 'boolean'
        ]);

        // If this is set as default, remove default from other addresses
        if ($request->boolean('is_default') && !$address->is_default) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update([
            'title' => $request->title,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'is_default' => $request->boolean('is_default')
        ]);

        return response()->json([
            'address' => [
                'id' => $address->id,
                'title' => $address->title,
                'first_name' => $address->first_name,
                'last_name' => $address->last_name,
                'phone' => $address->phone,
                'address_line_1' => $address->address_line_1,
                'address_line_2' => $address->address_line_2,
                'city' => $address->city,
                'state' => $address->state,
                'postal_code' => $address->postal_code,
                'country' => $address->country,
                'is_default' => $address->is_default
            ]
        ]);
    }

    public function deleteAddress(Request $request, int $addressId): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($addressId);
        $address->delete();

        return response()->json(['message' => 'Address deleted successfully']);
    }
}
