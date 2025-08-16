<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Ambil semua data users dengan caching
    public function index()
    {
        // Cache selama 1 jam
        $users = Cache::remember('users_all', 60 * 60, function () {
            return User::all();
        });

        return response()->json([
            "status" => true,
            "data" => $users
        ]);
    }

    // Ambil user berdasarkan ID dengan caching
    public function show($id)
    {
        $user = Cache::remember("user_{$id}", 60 * 60, function () use ($id) {
            return User::find($id);
        });

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            "status" => true,
            "data" => $user
        ]);
    }

    // Tambah user baru (hapus cache supaya data fresh)
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|string|max:255|unique:users,username',
                'password' => 'required|string|min:6',
                'birth_date' => 'nullable|date',
                'gender' => 'nullable|in:M,F',
                'education_level' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'role' => 'required|string|in:student,admin',
                'is_active' => 'boolean'
            ]);

            // Enkripsi password sebelum menyimpannya
            $validated['password'] = Hash::make($validated['password']);

            // Buat pengguna baru dengan data yang sudah divalidasi dan password yang dienkripsi
            $user = User::create($validated);

            // Hapus cache agar data terbaru terambil
            Cache::forget('users_all');

            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }

    // Update user
    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id, // Corrected line
                'username' => 'required|string|max:255|unique:users,username,' . $user->id, // Corrected line
                'password' => 'nullable|string|min:6',
                'birth_date' => 'nullable|date',
                'gender' => 'nullable|in:M,F',
                'education_level' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'role' => 'required|string|in:student,admin',
                'is_active' => 'required|boolean'
            ]);

            // Memeriksa dan mengenkripsi password jika ada
            if ($request->has('password') && $request->password !== "") {
                $validated['password'] = Hash::make($request->password);
            }

            // Menyimpan perubahan ke database
            $user->update($validated);

            // Hapus cache user spesifik dan daftar user
            Cache::forget("user_{$id}");
            Cache::forget('users_all');

            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);

        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }

    // Hapus user
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->delete();

        // Hapus cache
        Cache::forget("user_{$id}");
        Cache::forget('users_all');

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully',
            'data' => $user
        ]);
    }
}
