<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exception\JWTException;
use JWTAuth;

class UserController extends Controller
{
    // Fungsi Login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if(! $token = JWTAuth::attempt($credentials)){
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e){
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        return response()->json(compact('token'));
    }

    // Fungsi Register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'),201);
    }

    // Fungsi get auth user
    public function getAuthenticatedUser()
    {
        try {
            if(! $user = JWTAuth::parseToken()->authenticate()){
                return response()->json(['user_not_found'], 400);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e){
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e){
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e){
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        
        return response()->json(compact('user'));
    }

}



