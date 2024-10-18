<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'first_name' => 'required|regex:/^[\pL\s-]+$/u|string',
            'last_name' => 'required|regex:/^[\pL\s-]+$/u|string',
            'age' => 'nullable|integer',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'sometimes|in:admin,pharmacist,user', // Add this line for roles
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Users::create([
            'first_name' => $req->first_name,
            'last_name' => $req->last_name,
            'age' => $req->age,
            'email' => $req->email,
            'password' => Hash::make($req->password),
            'role' => $req->role,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(['message' => 'User successfully registered'], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    // Adjusted the 'direct' method to use JWTAuth
    public function direct()
    {
        $type = JWTAuth::user()->type_of_user;

        if ($type == 'user') {
            return view('welcome');
        } else {
            return view('index');
        }
    }

    // Adjusted 'me' method to use JWTAuth
    public function me()
    {
        return response()->json(JWTAuth::user());
    }

    // Adjusted 'logout' method to invalidate JWT token
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh(JWTAuth::getToken()));
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60 // Adjusted TTL
        ]);
    }
}
