<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExhibitorEventDashboardController;
use App\Http\Controllers\Api\ExhibitorProduct;
use App\Http\Controllers\Api\ExhibitorWhatsappController;
use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\PreviousEventController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\V2\VisitorController as V2VisitorController;
use App\Http\Controllers\Api\VisitorAppController;
use App\Http\Controllers\Api\VisitorAppointmentController;
use App\Http\Controllers\Api\VisitorController;
use App\Http\Controllers\Api\VisitorGomenController;
use App\Http\Controllers\Api\VisitorWhatsappController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
 */

Route::post('/login', [AuthController::class, 'login']);
Route::post('/otp-request', [AuthController::class, 'otpRequest']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::name('api.')
    ->middleware(['auth:sanctum', 'throttle:100,1'])
    ->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::post('/events/{event_id}/exhibitor/registration', [DashboardController::class, 'store']);
        Route::get('/exhibitor/profile', [ProfileController::class, 'show']);
        Route::post('/exhibitor/profile/edit', [ProfileController::class, 'updateProfile']);
        Route::post('/events/{event_id}/products', [ProfileController::class, 'updateEventProducts']);
        Route::post('/exhibitor/products/{product_id}', [ProfileController::class, 'storeProductImage']);
        Route::post('/exhibitor/products/{product_id}/images/{image_id}', [ProfileController::class, 'destroyProductImage']);
        Route::get('/events/{eventId}/appointments', [AppointmentController::class, 'showAppointments']);
        Route::post('/events/{eventId}/appointments/{appointmentId}', [AppointmentController::class, 'statusUpdate']);
        Route::get('/previous-events', [PreviousEventController::class, 'showPreviousEvents']);
        Route::get('/previous-events/{eventId}', [PreviousEventController::class, 'getPreviousEventCompletedAppointments']);
        Route::get('/products/{search}', [MasterController::class, 'showProducts']);
        Route::post('/products/{product_name}', [MasterController::class, 'addProducts']);
        Route::get('/exhibitors/{eventId}/dashboard', [ExhibitorEventDashboardController::class, 'getEventDashboardData']);
        Route::post('/exhibitor/logo', [ProfileController::class, 'updateExhibitorLogo']);
        Route::get('/event-dates', [AppointmentController::class, 'getEventDates']);
        Route::get('/events/{eventId}/appointments/{appointmentId}/ics-file', [AppointmentController::class, 'getICSFile']);
        Route::get('/announcements', [AnnouncementController::class, 'getAnnouncements']);
        Route::post('/change-password', [ProfileController::class, 'changeUserPassword']);
    });
//admin
Route::prefix('/admin')->middleware('auth:sanctum')->group(function () {
    Route::get('/visitors', [AdminController::class, 'getVisitors']);
    Route::get('/exhibitors', [AdminController::class, 'getExhibitors']);
    Route::post('/update-stall-detail', [AdminController::class, 'updateStallDetail']);
    Route::get('/dashboard', [AdminController::class, 'getAdminDashboardData']);
    Route::get('/appointments', [AdminController::class, 'getAppointments']);
    Route::get('/event-details', [AdminController::class, 'getEventDetails']);
    Route::get('/events/{event_id}/visitors/last-seven-days', [AdminController::class, 'getLastSevenDaysVisitorsCount']);
    Route::get('/events/{event_id}/visitors/top-locations', [AdminController::class, 'getTopLocationDetails']);
    Route::get('/events/{event_id}/visitors/registration-typewise-count', [AdminController::class, 'getRegistrationTypewiseCounts']);
    Route::get('/events/{event_id}/visitors/business-typewise-count', [AdminController::class, 'getBusinessTypewiseCounts']);
    Route::get('/events/{event_id}/visitors/knownsource-wise-count', [AdminController::class, 'getKnownSourceWiseCounts']);
    Route::post('/store-announcements', [AdminController::class, 'storeAnnouncement']);
    Route::get('/delegates', [AdminController::class, 'getDelegates']);
    Route::get('/announcements', [AnnouncementController::class, 'showAnnouncement']);
});

// Integrating the whatsapp flow for the visitors
Route::prefix('/whatsapp')->group(function () {

    Route::get('/visitors/{mobileNumber}/appointments', [VisitorWhatsappController::class, 'getAppointmentsByMobilenumber']);
    Route::post('/visitors/appointments/{appointmentId}/cancel', [VisitorWhatsappController::class, 'cancelAppointment']);
    Route::post('/visitors/appointments/{appointmentId}/reschedule', [VisitorWhatsappController::class, 'rescheduleAppointment']);
    Route::get('/search-exhibitors/{search}', [VisitorWhatsappController::class, 'searchExhibitors']);
    Route::get('/search-products/{search}', [VisitorWhatsappController::class, 'searchProducts']);
    Route::get('/get-exhibitors-by-product', [VisitorWhatsappController::class, 'getExhibitorsByProduct']);
    Route::post('/make-appointment', [VisitorWhatsappController::class, 'makeAppointment']);
    Route::get('/search-exhibitors-by-product', [VisitorWhatsappController::class, 'searchExhibitorsByProduct']);

    Route::get('/exhibitor-products/{eventId}/{search}', [ExhibitorProduct::class, 'getExhibitorProduct']);
    Route::post('/visitors/make-appointment', [VisitorAppointmentController::class, 'makeAppointment']);
    Route::get('/search-products/{eventId}/{search}', [VisitorWhatsappController::class, 'searchProducts']);

    // Route::get('/exhibitor/appointments/pending', [ExhibitorWhatsappController::class, 'getPendingAppointmentsByMobile']);
    Route::get('/exhibitor/appointments/confirmed', [ExhibitorWhatsappController::class, 'getconfirmedAppointmentsByMobile']);
    Route::get('/exhibitor/appointments/completed', [ExhibitorWhatsappController::class, 'getcompletedAppointmentsByMobile']);
    Route::post('/exhibitor/appointments/{appointmentId}/confirm', [ExhibitorWhatsappController::class, 'confirmAppointment']);
    Route::post('/exhibitor/appointments/{appointmentId}/cancelled', [ExhibitorWhatsappController::class, 'cancelAppointment']);
    Route::post('/exhibitor/appointments/{appointmentId}/reschedule', [ExhibitorWhatsappController::class, 'rescheduleAppointment']);

    Route::get('/exhibitor/completed-appointments', [ExhibitorWhatsappController::class, 'getcompletedAppointments']);

    Route::any('/exhibitor/appointments/confirm', [ExhibitorWhatsappController::class, 'confirmWhatsappAppointment']);
    Route::any('/exhibitor/appointments/cancelled', [ExhibitorWhatsappController::class, 'cancelWhatsappAppointment']);
    Route::get('/check-user-exists', [VisitorWhatsappController::class, 'checkUserExits']);
    Route::post('/visitor-event-register', [VisitorWhatsappController::class, 'registerVisitor']);
    Route::post('/update-visitor-info', [VisitorWhatsappController::class, 'updateVisitor']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('/gomen')->group(function () {
    Route::get('/visitors', [VisitorGomenController::class, 'show']);
    Route::post('/visitors/{visitor_id}/update', [VisitorGomenController::class, 'updateVisitor']);
    Route::get('/masters', [VisitorGomenController::class, 'getMasterData']);
    Route::get('/dashboard', [VisitorGomenController::class, 'getDashboardData']);
    Route::get('/visitors/{visitor_id}', [VisitorGomenController::class, 'getVisitorData']);
});

Route::post('/exhibitor-registration', [ExhibitorEventDashboardController::class, 'store']);
Route::get('/master-data', [MasterController::class, 'getMasterData']);
Route::get('/events/{event_id}/exhibitors', [VisitorAppController::class, 'index']);
Route::get('/event-details', [VisitorAppController::class, 'getEventDetails']);
Route::get('/visitor/announcements', [AnnouncementController::class, 'getVisitorsAnnouncements']);
Route::get('/med-shorts', [VisitorAppController::class, 'getMedshorts']);

Route::prefix('/visitor')->middleware('auth:sanctum')->group(function () {
    Route::post('/wishlist', [VisitorAppController::class, 'wishList']);
    Route::get('/appointments', [VisitorAppController::class, 'getAppointments']);
    Route::post('/update-appointment-status', [VisitorAppController::class, 'updateAppointmentStatus']);
    Route::get('/show-visitor-data', [VisitorAppController::class, 'showVisitorData']);
    Route::post('/update-visitor-data', [VisitorAppController::class, 'updateVisitorData']);
    Route::post('/update-logo', [VisitorAppController::class, 'updateLogo']);
    Route::post('/update-event-products', [VisitorAppController::class, 'updateEventProducts']);
    Route::post('/seminar-registration', [VisitorAppController::class, 'registerForSeminar']);
    Route::get('/get-wishlist', [VisitorAppController::class, 'getWishList']);
    Route::get('/dashboard', [DashboardController::class, 'getVisitorDashboardData']);
    Route::post('/event-registration', [DashboardController::class, 'visitorEventRegistration']);
    Route::get('/event-dashboard', [DashboardController::class, 'getEventDashboardData']);
    Route::get('/exhibitor-list/{exhibitor_id}', [VisitorAppController::class, 'getExhibitorDetails']);
});

Route::post('/visitors', [VisitorController::class, 'store']);
Route::post('/v2/visitors', [V2VisitorController::class, 'store']);
Route::post('/update-visitor-check-in-status', [VisitorController::class, 'updateVisitorCheckedInStatus']);
