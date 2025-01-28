<?php

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Livewire\MenuCard;
use App\Models\UserLoginActivity;
use App\Http\Livewire\MenuHandler;
use App\Http\Livewire\MenuSummary;
use App\Http\Livewire\VideoSummary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\CategorySummary;
use Illuminate\Support\Facades\Artisan;
use App\Http\Livewire\ActivityLogHandler;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Livewire\Profile\UserProfileSettings;
use App\Http\Livewire\Settings\Employee\EmployeeSummary;


Route::get('/', function () {
    return redirect()->route('login');
    // return view('welcome');
});

Route::post('/login', [LoginController::class, 'authenticateAdmin'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


Route::middleware([
    'web',
])->group(function () {

    // Route::get('/dashboard', AdminDashboard::class)->name('dashboard');

    Route::get('/dashboard', function () {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard.user');
        }
        return "Not Allowed";
    })->name('dashboard');
    Route::get('/settings/employees', EmployeeSummary::class)->name('employees.index');
    Route::get('/menu/items/create/{menuId?}', MenuHandler::class)->name('menu.items.create');
    Route::get('/menu/items/list', MenuSummary::class)->name('menu.items.list');
    Route::get('/category', CategorySummary::class)->name('category');
    Route::get('/video', VideoSummary::class)->name('video');
    Route::get('menu/card', MenuCard::class)->name('menu.card');
});

Route::middleware(['auth:web,visitor,exhibitor'])->group(function () {
    Route::get('/settings/user-profile', UserProfileSettings::class)->name('user.profile');
    Route::get('/activity-log', ActivityLogHandler::class)->name('activitylog');
});
Route::middleware(['auth:web'])->group(function () {
    Route::view('/user-dashboard', 'dashboards.user-dashboard')->name('dashboard.user');
});

Route::get('cls', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    return "Cache is cleared";
});

Route::get('symlink', function () {
    Artisan::call('storage:link');
    return "Sym link created";
});

Route::get('migrate-tables', function () {
    Artisan::call('migrate', ['--force' => true]);
    return "Tables migrated";
});

Route::get('/set-default-password', function () {

    if (app()->environment('production')) {
        echo "Not allowed in production";
        return;
    }
    User::where('id', '>', 0)->update([
        'password' => Hash::make(config('app.default_user_password')),
    ]);
    echo "Done, set default password for all users";
});

