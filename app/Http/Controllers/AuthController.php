<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Menangani permintaan registrasi user baru.
     */
    public function register(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date|before_or_equal:today',
            'gender' => 'required|in:male,female',
            'height_cm' => 'required|integer|min:50|max:300',
            'weight_kg' => 'required|numeric|min:20|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Buat user baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'height_cm' => $request->height_cm,
            'weight_kg' => $request->weight_kg,
            'role' => 'user', // PENTING: Set role
            'activity' => 'jarang', // Default activity level
        ]);

        // Buat token untuk user baru
        $token = $user->createToken('auth_token')->plainTextToken;

        // 4. Kembalikan response
        return response()->json([
            'message' => 'Registrasi berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }

    /**
     * Menangani permintaan login user.
     */
    public function login(Request $request)
    {
        // Validasi input
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Cari user berdasarkan email
        $user = User::where('email', $request['email'])->firstOrFail();

        // Buat token baru
        $token = $user->createToken('auth_token')->plainTextToken;

        // Kembalikan response
        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 200);
    }
}
