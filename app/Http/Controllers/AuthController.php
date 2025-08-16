<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request){
        try{

            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $credentials = [
                "username" => $request->username,
                'password' => $request->password,
            ];

            if (Auth::attempt($credentials)) {
                $userData["token"] = $request
                    ->user()
                    ->createToken('auth_token',['*'],now()->addDay(), $request->user()->uuid )->plainTextToken;
                $userData["name"] = $request->user()->name;
                return response()->json(['status' => true, "data" => $userData], 200);
            }else{
                return response()->json(["status" => false, 'message' => 'Invalid username or password'], status: 401);

            }
        }catch(\Exception $e){
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    
    }

    public function logout(Request $request){
        try{
            $user = $request->user();
            if ($user && method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }
            return response()->json([
                'status' => true,
                'message' => 'Berhasil logout'
            ], 200);

        }catch(\Exception $e){
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
