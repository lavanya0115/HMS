<?php

use App\Models\Announcement;
use App\Models\Appointment;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventExhibitor;
use App\Models\EventVisitor;
use App\Models\Exhibitor;
use App\Models\Option;
use App\Models\Visitor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

if (!function_exists('getAuthData')) {
    function getAuthData()
    {
        $auth = Auth::user();
        return $auth;
    }
}
