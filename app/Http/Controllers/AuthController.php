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

    public function listUser()
    {
        try {
            $data = User::role('user')->get();
            return $this->success($data, 'Data User');
        } catch (\Throwable $th) {
            //throw $th;
            return $this->error($th->getMessage());
        }
    }

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
            $input['otp_expired_at'] = Carbon::now()->addMinutes(5);
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
            if (!$user) {
                return $this->error('User not found');
            }
            if ($user->otp == $request->otp) {
                $exp = strtotime($user->otp_expired_at);
                $now = strtotime(Carbon::now());
                $diff = $exp - $now;
                if ($diff < 0) {
                    return $this->error('OTP expired');
                }
                $user->update([
                    'is_confirmed' => 1,
                    'otp' => null,
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
                return response()->json(['error' => 'Wrong username or password'], 401);
            }

            $admin = User::role('admin')->where('id', Auth::user()->id)->first();

            if (!$admin) {
                if (Auth::user()->is_confirmed == 0) {
                    return $this->error('Please confirm your email, we sent you OTP', 401);
                }

                if (Auth::user()->email_verified_at == null && !$admin) {
                    return $this->error('Be patient, Admin not yet verified your account', 401);
                }
            }

            // Return a response with a JWT
            return $this->success([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::factory()->getTTL() * 60,
                'user' => Auth::user()
            ], 'User successfully logged in');
        } catch (\Exception $e) {
            // Return an error response if the user couldn't be authenticated
            return $this->error($e->getMessage());
        }
    }

    public function logout()
    {
        try {

            Auth::logout();

            return $this->success(null, 'User successfully signed out');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not sign out user.'], 500);
        }
    }

    public function me()
    {
        try {

            $user = User::with('ships:id,user_id,nama_kapal,status,notes')->where('id', Auth::user()->id)->get();

            return $this->success($user, 'User data successfully retrieved');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not get user.'], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = User::findOrFail(Auth::user()->id);
            $user->update($request->all());

            DB::commit();
            return $this->success($user, 'User successfully updated');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return $this->error($th->getMessage());
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
