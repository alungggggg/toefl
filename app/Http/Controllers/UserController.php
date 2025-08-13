<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function index()
    {
        $result = User::get();
        return response()->json($result);
    }

    public function store(Request $request)
    {
        try{
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);
            $request->merge([
                'password' => bcrypt($request->input('password')),
            ]);
            $user = User::create($request->all());
            return response()->json($user, 201);
        }catch(\Exception $e){
            return response()->json(['error' => 'Failed to create user: ' . $e->getMessage()], 500);
        }
    }
}
