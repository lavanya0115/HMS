<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Visitor;
use App\Models\FollowUp;
use App\Models\Exhibitor;
use App\Models\EventVisitor;
use Illuminate\Http\Request;
use App\Http\Livewire\Insights;
use App\Http\Livewire\LeadsList;
use App\Http\Livewire\HallLayout;
use App\Models\UserLoginActivity;
use App\Http\Livewire\LeadHandler;
use App\Http\Livewire\LeadSummary;
use App\Http\Livewire\MenuHandler;
use App\Http\Livewire\MenuSummary;
use App\Http\Livewire\RoleHandler;
use App\Http\Livewire\SeminarList;
use App\Http\Livewire\VisitorList;
use Illuminate\Support\Facades\DB;
use App\Http\Livewire\FindProducts;
use App\Http\Livewire\StallHandler;
use App\Http\Livewire\StallSummary;
use Illuminate\Support\Facades\Log;
use App\Http\Livewire\EditExhibitor;
use App\Http\Livewire\EventsHandler;
use App\Http\Livewire\EventsSummary;
use App\Http\Livewire\ImportVisitor;
use App\Http\Livewire\ReportHandler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Livewire\MyAppointments;
use App\Http\Livewire\ProductSummary;
use App\Http\Livewire\SeminarHandler;
use App\Http\Livewire\SeminarSummary;
use App\Http\Livewire\VisitorHandler;
use App\Http\Livewire\VisitorProfile;
use App\Http\Livewire\VisitorSummary;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\CampaignReports;
use App\Http\Livewire\CategorySummary;
use App\Http\Livewire\DelegateHandler;
use App\Http\Livewire\DelegateSummary;
use App\Http\Livewire\FollowUpHandler;
use App\Http\Livewire\FollowupSummary;
use App\Http\Livewire\VisitorWishlist;
use App\Http\Livewire\EventFormSummary;
use App\Http\Livewire\EventInformation;
use App\Http\Livewire\ExhibitorHandler;
use App\Http\Livewire\ExhibitorProfile;
use App\Http\Livewire\ExhibitorSummary;
use App\Http\Livewire\MedShortsHandler;
use App\Http\Livewire\MyProductSummary;
use App\Http\Livewire\PotentialHandler;
use App\Http\Livewire\PotentialSummary;
use Illuminate\Support\Facades\Artisan;
use App\Http\Livewire\AnnouncementsList;
use App\Http\Livewire\ActivityLogHandler;
use App\Http\Livewire\AppointmentSummary;
use App\Http\Livewire\ExhibitorDirectory;
use App\Http\Livewire\MappingToExhibitor;
use App\Http\Livewire\PermissionsHandler;
use App\Http\Livewire\ViewVisitorProfile;
use App\Http\Livewire\AnnouncementHandler;
use App\Http\Livewire\AnnouncementSummary;
use App\Http\Livewire\VisitorRegistration;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\VisitorController;
use App\Http\Livewire\EmailTemplateHandler;
use App\Http\Livewire\EmailTemplatesSummary;
use App\Http\Livewire\EventExhibitorProfile;
use App\Http\Controllers\ExhibitorController;
use App\Http\Controllers\MigrationController;
use App\Http\Controllers\OneSignalController;
use App\Http\Livewire\CampaignReportsHandler;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Livewire\Import\Leads as ImportLeads;
use App\Http\Livewire\Profile\UserProfileSettings;
use App\Http\Livewire\Import\Visitors as ImportVisitors;
use App\Http\Livewire\Settings\Employee\EmployeeSummary;
use App\Http\Livewire\Import\Exhibitors as ImportExhibitors;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
 */

Route::get('/', function () {
    return redirect()->route('login');
    // return view('welcome');
});

Route::post('/login', [LoginController::class, 'authenticateAdmin'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/exhibitor/registration/view', [ExhibitorController::class, 'index'])->name('exhibitor.show');
Route::post('/exhibitor/registration/store', [ExhibitorController::class, 'store'])->name('exhibitor.store')->middleware('frameGuard');

Route::get('/visitor/registration/view', [VisitorController::class, 'index'])->name('visitor.show');
Route::post('/visitor/registration/store', [VisitorController::class, 'store'])->name('visitor.store')->middleware('frameGuard');

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

    Route::get('/menu/items/create',MenuHandler::class)->name('menu.items.create');
    Route::get('/menu/items/list',MenuSummary::class)->name('menu.items.list');
    Route::get('/category', CategorySummary::class)->name('category');
});

Route::middleware(['auth:web,visitor,exhibitor'])->group(function () {
    Route::get('/event_information', EventInformation::class)->name('event-informations');
    Route::get('/settings/user-profile', UserProfileSettings::class)->name('user.profile');
    Route::get('/activity-log', ActivityLogHandler::class)->name('activitylog');
    Route::get('/hall-layout', HallLayout::class)->name('hall-layout');
});

Route::middleware(['auth:exhibitor'])->group(function () {
    Route::get('/exhibitor/profile', ExhibitorProfile::class)->name('exhibitor.profile');
    Route::get('/myproducts', MyProductSummary::class)->name('myproducts');
    Route::view('/exhibitor-dashboard', 'dashboards.exhibitor-dashboard')->name('dashboard.exhibitor');
});

Route::middleware(['auth:web'])->group(function () {

    Route::view('/user-dashboard', 'dashboards.user-dashboard')->name('dashboard.user');
    Route::get('/admin-dashboard/{eventId?}', EventFormSummary::class)->name('admin-dashboard')->middleware('can:View Previous Event');
    Route::get('/appointment', AppointmentSummary::class)->name('appointment.summary')->middleware('can:View Appointment');
    Route::get('/insights', Insights::class)->name('insights');
    Route::get('/settings/employees', EmployeeSummary::class)->name('employees.index');
    Route::get('/events', EventsSummary::class)->name('events')->middleware('can:View Event');
    Route::get('/sales_person_mapping', MappingToExhibitor::class)->name('sales-person-mapping')->middleware('can:View Sales Person Mapping');
    Route::get('/import/exhibitors', ImportExhibitors::class)->name('import.exhibitors')->middleware('can:Import Exhibitor Data');
    Route::get('/products', ProductSummary::class)->name('products')->middleware('can:View Product');
    Route::get('/announcement/{announcementId?}', AnnouncementHandler::class)->name('announcement');
    Route::get('/seminar/{seminarId?}', SeminarHandler::class)->name('upsert.seminar');
    Route::get('/announcements', AnnouncementSummary::class)->name('announcements.index')->middleware('can:View Announcement');
    Route::get('/seminars', SeminarSummary::class)->name('seminars')->middleware('can:View Seminar');
    Route::get('/seminar-list', SeminarList::class)->name('listofseminars');
    Route::get('/exhibitor/summary', ExhibitorSummary::class)->name('exhibitor.summary')->middleware('can:View Exhibitor');
    Route::get('/visitors', VisitorSummary::class)->name('visitors.summary')->middleware('can:View Visitor');
    Route::get('/delegates', DelegateSummary::class)->name('delegates.summary')->middleware('can:View Delegate');
    Route::get('/roles', RoleHandler::class)->name('roles')->middleware('can:View Role');
    Route::get('/permissions', PermissionsHandler::class)->name('permissions')->middleware('can:View Permission');
    Route::get('/reports', ReportHandler::class)->name('reports');
    Route::get('/zoho-campaign-reports', CampaignReportsHandler::class)->name('zoho-reports');
    Route::get('/zoho-campaign', CampaignReports::class)->name('zoho-campaign');
    Route::get('/med-shorts', MedShortsHandler::class)->name('med-shorts')->middleware('can:View MedShorts');
    Route::get('/import-tele-calling-Visitors', ImportVisitor::class)->name('telecalling.import-visitors');
    Route::get('/view-profile/{profileId}', ViewVisitorProfile::class)->name('profile-view');
    Route::get('/telecalling/visitors', VisitorList::class)->name('telecalling.visitors');
    Route::post('/make-outbound-call', [VisitorList::class, 'makeOutboundCall']);
    Route::get('/leads-list', LeadsList::class)->name('leads.list');
    Route::get('/email-templates', EmailTemplatesSummary::class)->name('email-templates.summary');
    Route::get('/email-templates/create', EmailTemplateHandler::class)->name('email-templates.create');
    Route::get('/email-templates/{templateId}/edit', EmailTemplateHandler::class)->name('email-templates.edit');
    Route::get('/create-event', EventsHandler::class)->name('create-event');
    Route::get('/stall-list', StallSummary::class)->name('stall-summary');
    Route::get('/new-stall', StallHandler::class)->name('stall-handler');
    Route::get('/potential-list', PotentialSummary::class)->name('potential-summary');
    Route::get('/potential/create', PotentialHandler::class)->name('potential-create');
    Route::get('/leads/summary', LeadSummary::class)->name('leads.summary');
    Route::get('/leads/{leadId?}', LeadHandler::class)->name('upsert.lead');
    Route::get('/import/leads', ImportLeads::class)->name('import.leads');
    Route::get('/followup/summary/{potentialId}', FollowupSummary::class)->name('followup-summary');
    Route::get('/potential/follow-up/{potentialId?}', FollowUpHandler::class)->name('potential-follow-up');
});

Route::middleware(['auth:visitor'])->group(function () {

    Route::view('/visitor-dashboard', 'dashboards.visitor-dashboard')->name('dashboard.visitor');
    Route::get('/find-products', FindProducts::class)->name('visitor.find-products');
    Route::get('/visitor/profile', VisitorProfile::class)->name('visitor.profile');
    Route::get('/wishlists', VisitorWishlist::class)->name('visitor.wishlists');
    Route::get('/exhibitor/directory', ExhibitorDirectory::class)->name('exhibitor.directory');
    Route::get('/events/{eventId}/exhibitors/{exhibitorId}/profile', EventExhibitorProfile::class)->name('eventexhibitor.profile');
});

Route::get('/visitor-registration', VisitorRegistration::class)->name('visitor-registration');
Route::get('/exhibitor/registration', ExhibitorHandler::class)->name('exhibitor.registration');

Route::middleware(['auth:visitor,exhibitor'])->group(function () {
    Route::get('/myappointments', MyAppointments::class)->name('myappointments');
    Route::view('/show-announcement', 'livewire.show-announcement')->name('show.announcement');
    Route::get('/announcements/list', AnnouncementsList::class)->name('announcements.list');
});

Route::middleware(['auth:visitor,web'])->group(function () {
    Route::get('/delegate-registration', DelegateHandler::class)->name('delegate-registration')->middleware('can:Create Delegate');
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

Route::get('check-otp', function () {
    $result = sendLoginOtp('9787480936', '369369');
    dd($result);
});

Route::get('check-current-event', function () {
    $result = getCurrentEvent();
    return json_encode($result, JSON_PRETTY_PRINT);
});

Route::get('/set-default-password', function () {

    if (app()->environment('production')) {
        echo "Not allowed in production";
        return;
    }

    User::where('id', '>', 0)->update([
        'password' => Hash::make(config('app.default_user_password')),
    ]);

    Exhibitor::where('id', '>', 0)->update([
        'password' => Hash::make(config('app.default_user_password')),
    ]);

    Visitor::where('id', '>', 0)->update([
        'password' => Hash::make(config('app.default_user_password')),
    ]);

    echo "Done, set default password for all users";
});

Route::get('/send-exhibitor-welcome-notification', function () {
    $exhibitors = Exhibitor::where('id', '>', 0)->get();
    foreach ($exhibitors as $exhibitor) {
        $result = sendWelcomeMessageThroughWhatsappBot($exhibitor->mobile_number, 'exhibitor');
        Log::info("Result");
        Log::info($result);
    }
});

Route::get('import/visitors', [ImportVisitors::class, 'import']);
Route::get('remove-exhibitors', [MigrationController::class, 'removeExhibitors']);

// Send Notifications
Route::get('/send-remainders-to-all-users', [NotificationController::class, 'sendRemainderNotificationsToAllUsers']);
Route::get('/send-greetings-to-participated-visitors', [NotificationController::class, 'sendGreetingsNotificationsToParticipatedVisitors']);
Route::get('/send-application-promotion-notification-to-all-exhibitors', [NotificationController::class, 'sendApplicationPromotionNotificationToAllExhibitors']);
Route::get('/send-feedback-notification-to-all-visited-visitors', [NotificationController::class, 'sendFeedbackNotificationToAllVisitedVisitors']);
Route::get('/send-notification-to-all-visitors-for-hyderabad-24', [NotificationController::class, 'sendNotificationToAllVisitorsForHyderabad24']);
Route::get('/send-minimal-notification-to-all-visitors', [NotificationController::class, 'sendMinimalNotificationToAllVisitors']);
// Below routes are for migration purpose only
Route::get('/send-visitor-welcome-notifications-through-bot', [MigrationController::class, 'sendVisitorsWelcomeNotification']);
Route::get('/update-event-products-in-string-format', [MigrationController::class, 'updateEventProductsInStringFormat']);
Route::get('/update-designation-field-in-1ot-visitors', [MigrationController::class, 'updateDesignationFieldIn10TVisitorsTable']);
Route::get('/registering-visitors-who-makes-appointments-without-register', [MigrationController::class, 'registeringVisitorsWhoMakesAppointmentsWithoutRegister']);
Route::get('/update-exhibitor-website-url', [MigrationController::class, 'updateExhibitorWebsiteUrl']);
Route::get('/import-visitors', [MigrationController::class, 'importVisitorsFromGivenFile']);
Route::get('/import-10t-data', [MigrationController::class, 'import10TData']);
Route::get('/delete-not-exist-exhibitor-products', [MigrationController::class, 'deleteNotExistExhibitorProducts']);
Route::get('/set-visited-at-values', [MigrationController::class, 'setVisitedAtValues']);
Route::get('/update-registration-type-in-event-visitors', [MigrationController::class, 'migrateRegistrationTypes']);
Route::get('/update-known-source-in-event-visitors', [MigrationController::class, 'migrateKnownSource']);
Route::get('/set-registration-type-for-current-event', [MigrationController::class, 'setRegistrationTypeForCurrentEvent']);
Route::get('/export-10t-records', [MigrationController::class, 'export10TRecords']);
Route::get('/update-known-source-in-event-visitors', [MigrationController::class, 'migrateKnownSource']);
Route::get('/update-delegate-payment-status', [MigrationController::class, 'updateDelegatePaymentStatus']);
Route::get('send-notificaiton', function () {
    $visitor = Visitor::where('mobile_number', 9787480936)->first();
    $data = sendWhatsappNotificationAfterRegister($visitor, getCurrentEvent());
    sendVisitorAppNotification(9787480936);
    // SendVisitorAppPromotionNotificationJob::dispatch($visitor->mobile_number);
    echo "Notification sent";
});

Route::get('/send-event-info', [GeneralController::class, 'sendEventData']);

Route::get('/update-role-for-existing-users', [MigrationController::class, 'updateRoleForExistingUsers']);
Route::get('/create-new-permissions', [MigrationController::class, 'createNewPermissions']);
Route::get('/delete-permissions', [MigrationController::class, 'deletePermissions']);

Route::get('/create-role-and-assign-permissions', [MigrationController::class, 'createRoleAndAssignPermissions']);
// Import missing 10T visitors
Route::get('/insert-missing-10t-visitors', [ImportVisitors::class, 'insertMissing10TVisitors']);
Route::get('/login-logs', function (Request $request) {
    $dateFilterType = $request->get('date_filter_type', 'all');

    $visitorLogs = UserLoginActivity::where('userable_type', 'App\Models\Visitor')
        ->select('userable_id', DB::raw('count(*) as login_count'))
        ->when("last_7_days" === $dateFilterType, function ($query) {
            return $query->where('created_at', '>=', now()->subDays(7));
        })
        ->when("last_15_days" === $dateFilterType, function ($query) {
            return $query->where('created_at', '>=', now()->subDays(15));
        })
        ->when("last_30_days" === $dateFilterType, function ($query) {
            return $query->where('created_at', '>=', now()->subDays(30));
        })
        ->groupBy('userable_id')
        ->get();

    $exhibitorLogs = UserLoginActivity::where('userable_type', 'App\Models\Exhibitor')
        ->select('userable_id', DB::raw('count(*) as login_count'))
        ->when("last_7_days" === $dateFilterType, function ($query) {
            return $query->where('created_at', '>=', now()->subDays(7));
        })
        ->when("last_15_days" === $dateFilterType, function ($query) {
            return $query->where('created_at', '>=', now()->subDays(15));
        })
        ->when("last_30_days" === $dateFilterType, function ($query) {
            return $query->where('created_at', '>=', now()->subDays(30));
        })
        ->groupBy('userable_id')
        ->get();

    $totalVisitorLoginsCount = count($visitorLogs);
    $totalExhibitorLoginsCount = count($exhibitorLogs);

    $label = 'All';
    if ("last_7_days" === $dateFilterType) {
        $label = 'Last 7 Days';
    } elseif ("last_15_days" === $dateFilterType) {
        $label = 'Last 15 Days';
    } elseif ("last_30_days" === $dateFilterType) {
        $label = 'Last 30 Days';
    }

    echo "<h1>Login Logs - $label</h1>";
    $html = '<p><strong>Visitors -- (' . $totalVisitorLoginsCount . ')</strong></p>';

    $html .= '<ul>';
    foreach ($visitorLogs as $visitorLog) {
        $visitor = Visitor::find($visitorLog->userable_id);
        if (!$visitor) {
            continue;
        }
        $html .= '<li>' . ($visitor->name ?? '') . ' - ' . ($visitorLog->login_count) . ' Times</li>';
    }
    $html .= '</ul>';
    $html .= '<p><strong>Exhibitors -- (' . $totalExhibitorLoginsCount . ')</strong></p>';

    $html .= '<ul>';
    foreach ($exhibitorLogs as $exhibitorLog) {
        $exibitor = Exhibitor::find($exhibitorLog->userable_id);
        if (!$exibitor || !$exibitor->name) {
            continue;
        }
        $html .= '<li>' . ($exibitor->name ?? '') . ' - ' . ($exhibitorLog->login_count) . ' Times</li>';
    }

    $html .= '</ul>';

    echo $html;
});
Route::get('list-deleted-exhibitors-contact-record', [MigrationController::class, 'listDeletedExhibitorsContact']);
Route::get('/1-signal', function () {
    $oneSignal = new OneSignalController();

    $oneSignal->sendNotification(
        'Appointment Completed',
        "Test Message",
        "9361814854"
    );

    return "message triggered...";
});

Route::get('qrcode', function () {
    $visitor = Visitor::where('mobile_number', '9787480936')->first();
    $qrPath = generateQrForVisitor($visitor->mobile_number);
    return $qrPath;
});

Route::get('get_visitor_count/{date}', function ($date) {
    $visitors = EventVisitor::where(function ($query) {
        $query->where('registration_type', 'whatsapp')
            ->orWhere('known_source', 'WhatsApp');
    })
        ->where('event_id', 18)
        ->whereBetween('created_at', [
            Carbon::parse($date)->startOfDay(),
            Carbon::now(),
        ])
        ->count();

    return $visitors;
});
