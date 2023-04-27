<?php

namespace App\Http\Controllers;

use App\Http\Traits\Response;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use Response;
    public function register(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|same:confirm-password',
                'roles' => 'required'
            ]);

            $input = $request->all();
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);
            $user->assignRole($request->input('roles'));

            return $this->success($user, 'User successfully registered');
        } catch (\Exception $e) {
            // Return an error response if the user couldn't be created
            return response()->json(['error' => 'User could not be created.'], 500);
        }
    }

    public function login(Request $request)
    {
        // Validate the incoming request using the already included validator method
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        try {

            $credentials = $request->only(['email', 'password']);

            if (!$token = Auth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Return a response with a JWT
            return response()->json(['token' => $token], 201);
        } catch (\Exception $e) {
            // Return an error response if the user couldn't be authenticated
            return response()->json(['error' => 'Could not authenticate user.'], 500);
        }
    }

    public function logout()
    {
        try {

            Auth::logout();

            return response()->json(['message' => 'User successfully signed out']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not sign out user.'], 500);
        }
    }

    public function me()
    {
        try {

            $user = Auth::user();

            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not get user.'], 500);
        }
    }

    public function refresh()
    {
        try {

            $token = Auth::refresh();

            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not refresh token.'], 500);
        }
    }
}
