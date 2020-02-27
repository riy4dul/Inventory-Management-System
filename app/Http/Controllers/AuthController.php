<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
     public function __construct()
    {
        $this->middleware('JWT', ['except' => ['login','signup']]);
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if ($token = $this->guard()->attempt($credentials)) {

            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
    public function me()
    {
        return response()->json($this->guard()->user());
    }
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    public function signup(Request $request)
    {
    	$validatedData = $request->validate([
        'name' => 'required',
        'email' => 'required',
        'password' => 'required|min:8',
    	]);

    	$user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make(request('password'));
        $user->save();

        return $this->login($request);

    }
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }
    public function guard()
    {
        return Auth::guard();
    }
}
