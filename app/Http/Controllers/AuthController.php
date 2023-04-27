<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    use ApiResponse;

    // public
    public function register(Request $request)
    {
        $mailController = new EmailController();
        try {
            DB::beginTransaction();
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|same:confirm-password',
            ]);

            $input = $request->all();
            $input['password'] = Hash::make($input['password']);
            $input['otp'] = rand(100000, 999999);
            $input['token'] = "1dy09eODblmBUCTnIwiY-hbXdzCpZC3jyR4l0ZJgqQqO9L7J3zsZOobdJ";

            unset($input['roles']);

            $user = User::create($input);
            $user->assignRole($request->input('roles'));

            if ($user->hasRole('user')) {
                $mailController->otpMail($input);
            }

            DB::commit();
            return $this->success($user, 'User successfully registered');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function confirmOTP(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = User::where('email', $request->email)->first();
            if ($user->otp == $request->otp) {
                $user->update([
                    'is_confirmed' => 1
                ]);
                DB::commit();
                return $this->success($user, 'User successfully confirmed');
            } else {
                return $this->error('OTP not match');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function accountVerif(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->id);

            $user->update([
                'email_verified_at' => now(),
            ]);
            DB::commit();
            return $this->success($user, 'User successfully verified');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
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

            $admin = User::role('admin')->where('id', Auth::user()->id)->first();

            if (!$admin) {
                # code...
                if (Auth::user()->is_confirmed == 0) {
                    return $this->error('Please confirm your email, we sent you OTP', 401);
                }

                if (Auth::user()->email_verified_at == null && !$admin) {
                    return $this->error('Be patient, Admin not yet verified your account', 401);
                }
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
