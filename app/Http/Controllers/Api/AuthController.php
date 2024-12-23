<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exhibitor;
use App\Models\User;
use App\Models\UserLoginActivity;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $currentEvent = getCurrentEvent();
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|digits:10',
            'password' => 'required',
            'is_otp_login' => 'required|in:yes,no',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ]);
        }

        $credentials = $request->only('mobile_number', 'password');

        if ($request->type == 'visitor') {
            if ($request->is_otp_login == 'yes') {
                $validatedVisitorOtp = Visitor::where('mobile_number', $request->mobile_number)
                    ->where('otp', $request->password)
                    ->where('otp_expired_at', '>', now())
                    ->first();
                if (!$validatedVisitorOtp) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'OTP/Mobile number is incorrect or expired',
                    ]);
                }
                $validatedVisitorOtp->otp = null;
                $validatedVisitorOtp->otp_expired_at = null;
                $validatedVisitorOtp->save();

                auth()->guard('visitor')->login($validatedVisitorOtp);
            } else {
                if (!auth()->guard('visitor')->attempt(['mobile_number' => $request->mobile_number, 'password' => $credentials['password']])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid credentials',
                    ]);
                }
            }

            $user = auth()->guard('visitor')->user();
            UserLoginActivity::create([
                'userable_id' => $user->id,
                'userable_type' => Visitor::class,
                'last_login_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $token = $user->createToken('visitor_auth_token')->plainTextToken;

            $currentEvent = getCurrentEvent();
            $isVisitorExists = $user->eventVisitors()->where('event_id', $currentEvent->id)->first();
            $isRegistered = $isVisitorExists ? 'Registered' : 'Register';

            return response()->json([
                'status' => 'success',
                'message' => 'Logged in successfully',
                'current_event' => $isRegistered,
                'current_event_id' => $currentEvent->id,
                'token_type' => 'bearer',
                'token' => $token,
                'data' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'mobile_number' => $user->mobile_number,
                    'email' => $user->email,
                    'visitor_id' => $user->id,
                    'category' => $user->category?->name ?? '',
                    'user_type' => 'visitor',
                ],
            ]);
        } elseif ($request->type == 'admin') {
            $user = User::where('mobile_number', $request->mobile_number)->first();
            if ($user) {
                if ($request->is_otp_login == 'yes') {
                    $validatedUserOtp = User::where('mobile_number', $request->mobile_number)
                        ->where('otp', $request->password)
                        ->where('otp_expired_at', '>', now())
                        ->first();
                    if (!$validatedUserOtp) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'OTP/Mobile number is incorrect or expired',
                        ]);
                    }
                    $validatedUserOtp->otp = null;
                    $validatedUserOtp->otp_expired_at = null;
                    $validatedUserOtp->save();

                    auth()->guard('user')->login($validatedUserOtp);
                } else {
                    if (!auth()->guard('user')->attempt($credentials)) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Invalid credentials',
                        ]);

                    }
                }

                UserLoginActivity::create([
                    'userable_id' => $user->id,
                    'userable_type' => User::class,
                    'last_login_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                $role = $user->roles->first()->name;
                $user = auth()->guard('user')->user();
                $token = $user->createToken('users')->plainTextToken;
                return response()->json([
                    'status' => 'success',
                    'message' => 'User or Admin logged in successfully',
                    'token_type' => 'bearer',
                    'token' => $token,
                    'data' => [
                        'name' => $user->name,
                        'username' => $user->username ?? '',
                        'mobile_number' => $user->mobile_number,
                        'email' => $user->email,
                        'user_id' => $user->id,
                        'current_event_id' => $currentEvent->id,
                        'role' => $role,
                        'user_type' => 'admin',
                    ],
                ]);
            }
        } else {
            if ($request->is_otp_login == 'yes') {
                $exhibitor = Exhibitor::where(function ($query) use ($request) {
                    $query->where('mobile_number', $request->mobile_number)
                        ->orWhereHas('contact_persons', function ($query) use ($request) {
                            $query->where('contact_number', $request->mobile_number);
                        });
                })
                    ->where('otp', $request->password)
                    ->where('otp_expired_at', '>', now())
                    ->first();
                if (!$exhibitor) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'OTP/Mobile number is incorrect or expired',
                    ]);
                }
                // let authenticate the exhibitor
                $exhibitor->otp = null;
                $exhibitor->otp_expired_at = null;
                $exhibitor->save();
                auth()->guard('exhibitor')->login($exhibitor);
            } else {
                $loginWithContactPerson = Exhibitor::where(function ($query) use ($request) {
                    $query->where('mobile_number', $request->mobile_number)
                        ->orWhereHas('contact_persons', function ($query) use ($request) {
                            $query->where('contact_number', $request->mobile_number);
                        });
                })->first();

                $canContactPersonLogin = false;
                if ($loginWithContactPerson) {
                    $canContactPersonLogin = Hash::check($credentials['password'], $loginWithContactPerson->password);
                }

                if (!auth()->guard('exhibitor')->attempt($credentials) && !$canContactPersonLogin) {
                    // Check if user exists in visitor table
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Mobile number or password is incorrect',
                    ]);
                }

                // Manually login the user
                if ($canContactPersonLogin) {
                    auth()->guard('exhibitor')->login($loginWithContactPerson);
                }
            }

            $user = auth()->guard('exhibitor')->user();
            UserLoginActivity::create([
                'userable_id' => $user->id,
                'userable_type' => Exhibitor::class,
                'last_login_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $token = $user->createToken('exhibitor_auth_token')->plainTextToken;

            $currentEvent = getCurrentEvent();
            $isExhibitorExists = $user->eventExhibitors()->where('event_id', $currentEvent->id)->first();
            $facialName = $isExhibitorExists ? $isExhibitorExists->board_name : '';
            $isRegistered = $isExhibitorExists ? 'Registered' : 'Register';

            return response()->json([
                'status' => 'success',
                'message' => 'Logged in successfully',
                'current_event' => $isRegistered,
                'current_event_id' => $currentEvent->id,
                'token_type' => 'bearer',
                'token' => $token,
                'data' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'mobile_number' => $user->mobile_number,
                    'email' => $user->email,
                    'exhibitor_id' => $user->id,
                    'category' => $user->category?->name ?? '',
                    'facial_name' => $facialName,
                    'user_type' => 'exhibitor',
                ],
            ]);
        }
    }

    public function otpRequest(Request $request)
    {
        $mobileNumber = $request->mobile_number ?? '';
        $loginType = $request->type ?? '';
        if (empty($mobileNumber)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mobile number is missing',
            ]);
        }

        $userData = null;
        if ($loginType == 'visitor') {
            $userData = Visitor::where('mobile_number', $mobileNumber)->first();
        } elseif ($loginType == 'admin') {
            $user = User::where('mobile_number', $mobileNumber)->first();
            $loginType = 'exhibitor';
            if ($user) {
                $userData = $user;
            }
        } else {
            $userData = Exhibitor::where('mobile_number', $mobileNumber)
                ->orWhereHas('contact_persons', function ($query) use ($mobileNumber) {
                    $query->where('contact_number', $mobileNumber);
                })->first();
        }

        if (empty($userData)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mobile number is not registered. Please give a registered mobile number',
            ]);
        }

        $otp = rand(100000, 999999);
        $sendOtp = sendLoginOtp($mobileNumber, $otp, $loginType);

        if ($sendOtp['status'] == 'success') {

            $userData->otp = $otp;
            $userData->otp_expired_at = now()->addMinutes(10);
            $userData->save();

            return response()->json([
                'status' => 'success',
                'message' => 'OTP sent successfully, It will expire in 10 minutes.',
                'otp' => $otp,
                'otp_expired_at' => $userData->otp_expired_at,
                'otp_expired_at_formatted' => $userData->otp_expired_at->format('d-m-Y h:i:s A'),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong, please try again later',
            'error_message' => $sendOtp['message'] ?? '',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }
}
