<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function Register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);
        if ($validator->fails()) {
            return response()->json([
                "message" => "fail",
                "error" => $validator->errors(),
                "auth" => false
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            "message" => "user created",
            'access_token' => $token,
            'token_type' => "Bearer",
            "auth" => true
        ]);
    }

    public function Login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => "required",
            "password" => "required"
        ]);

        if (!Auth::attempt($request->only('email','password'))) {
            return response()->json([
                "message" => 'unauthorized',
                "error" => ["email or password fail"],
                "auth" => false
            ]);
        }else {
            $user = User::where('email',$request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;
        $authuser = auth()->user();
        return response()->json([
            "message" => "hi ".$user->name,
            'access_token' => $token,
            "token_type" => "Bearer",
            "auth" => true,
            "user" => $authuser
        ],200);
        }
    }

    public function Logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            "message" => "goodbye",
            "auth" => false
        ]);
    }
}