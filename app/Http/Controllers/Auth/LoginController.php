<?php

namespace App\Http\Controllers\Auth;

use App\Models\EventExhibitor;
use App\Models\EventVisitor;
use App\Models\Exhibitor;
use App\Models\User;
use App\Models\UserLoginActivity;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;

class LoginController extends FortifyAuthenticatedSessionController
{
    protected function authenticateAdmin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $emailOrMobile = $request->input('email');
       

        $verifiedUser = false;
        // Attempt to authenticate using  mobile number
        if (Auth::guard('web')->attempt(['mobile_number' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => 1]) ) {
            $verifiedUser = true;
        }

        // Attempt to authenticate using email  or mobile number
        if (Auth::guard('web')->attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => 1])) {
            $verifiedUser = true;
        }
        if ($verifiedUser) {
            // UserLoginActivity::create([
            //     'userable_id' => auth()->guard('web')->user()->id,
            //     'userable_type' => User::class,
            //     'last_login_at' => now(),
            //     'ip_address' => $request->ip(),
            //     'user_agent' => $request->userAgent(),
            // ]);
            return redirect()->intended('/dashboard');
        }
        return redirect()->route('admin-login-form')->with(['mobile_no' => $emailOrMobile]);
    }

    public function logout(Request $request)
    {
        $isAdmin = Auth::guard('web')->check();
        Log::info('Logout');

        if (Auth::guard('exhibitor')->check()) {
            $lastUserLoginActivity = UserLoginActivity::where('userable_id', auth()->guard('exhibitor')->user()->id)
                ->where('userable_type', 'App\Models\Exhibitor')
                ->where('last_logout_at', null)
                ->orderBy('id', 'desc')
                ->first();

            if ($lastUserLoginActivity) {
                $lastUserLoginActivity->last_logout_at = now();
                $lastUserLoginActivity->save();
            }
        } elseif (Auth::guard('visitor')->check()) {
            $lastUserLoginActivity = UserLoginActivity::where('userable_id', auth()->guard('visitor')->user()->id)
                ->where('userable_type', 'App\Models\Visitor')
                ->where('last_logout_at', null)
                ->orderBy('id', 'desc')
                ->first();

            if ($lastUserLoginActivity) {
                $lastUserLoginActivity->last_logout_at = now();
                $lastUserLoginActivity->save();
            }
        } elseif (Auth::guard('web')->check()) {
            $lastUserLoginActivity = UserLoginActivity::where('userable_id', auth()->guard('web')->user()->id)
                ->where('userable_type', 'App\Models\User')
                ->where('last_logout_at', null)
                ->orderBy('id', 'desc')
                ->first();

            if ($lastUserLoginActivity) {
                $lastUserLoginActivity->last_logout_at = now();
                $lastUserLoginActivity->save();
            }
        }

        Auth::guard('web')->logout();
        Auth::guard('exhibitor')->logout();
        Auth::guard('visitor')->logout();

        $request->session()->flush();
        $request->session()->regenerateToken();

        if ($isAdmin) {
            return redirect()->route('login');
        } else {
            return redirect()->route('login');
        }
    }
}
