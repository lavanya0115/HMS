<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Visitor;
use App\Models\Exhibitor;
use App\Models\EventVisitor;
use Illuminate\Http\Request;
use App\Models\EventExhibitor;
use App\Models\UserLoginActivity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;

class LoginController extends FortifyAuthenticatedSessionController
{
    protected function authenticateAdmin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $emailOrMobile = $request->input('email');

        $verifiedUser = false;
        // Attempt to authenticate using  mobile number
        if (Auth::guard('web')->attempt(['mobile_number' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => 1])) {
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
        return redirect()->route('login')->with(['mobile_no' => $emailOrMobile]);
    }

    public function logout(Request $request)
    {
        $isAdmin = Auth::guard('web')->check();
        Log::info('Logout');

        Auth::guard('web')->logout();

        $request->session()->flush();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function register(Request $request){

        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|digits:10|unique:users,mobile_number',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validated->fails()) {
            return redirect()->back()->withErrors($validated)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'password' => Hash::make($request->password),
        ]);

        auth()->login($user);

        return redirect()->route('dashboard')->with('success', 'Registration successful!');
    }
    
}
