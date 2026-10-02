<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DrawController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoundController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Transparency Page (Open for all members & admins)
    Route::get('/transparency/{group?}', [ReportController::class, 'transparency'])->name('transparency');
    Route::get('/calendar', [RoundController::class, 'calendar'])->name('calendar');

    // Member Personal Bills & Payment History
    Route::get('/my-payments', [PaymentController::class, 'memberPayments'])->name('payments.member');
    Route::post('/payments/{payment}/upload-proof', [PaymentController::class, 'uploadProof'])->name('payments.upload_proof');

    // Arisan Round Detail & Lottery Screen (Accessible to all for viewing, drawing restricted to admin)
    Route::get('/rounds/{round}', [RoundController::class, 'show'])->name('rounds.show');
    Route::get('/rounds/{round}/draw', [DrawController::class, 'index'])->name('draws.index');

    // ADMIN ONLY ROUTES
    Route::middleware('admin')->group(function () {
        // Groups Management
        Route::resource('groups', GroupController::class);
        Route::post('/groups/{group}/generate-schedule', [GroupController::class, 'generateSchedule'])->name('groups.generate_schedule');

        // Member Management
        Route::resource('members', MemberController::class);
        Route::post('/members/{member}/toggle-status', [MemberController::class, 'toggleStatus'])->name('members.toggle_status');
        Route::post('/groups/{group}/add-member', [MemberController::class, 'addToGroup'])->name('members.add_to_group');
        Route::delete('/groups/{group}/remove-member/{member}', [MemberController::class, 'removeFromGroup'])->name('members.remove_from_group');
        Route::put('/group-members/{membership}', [MemberController::class, 'updateGroupMembership'])->name('members.update_membership');

        // Round Management
        Route::post('/rounds/{round}/reschedule', [RoundController::class, 'reschedule'])->name('rounds.reschedule');
        Route::post('/rounds/{round}/update-host', [RoundController::class, 'updateHost'])->name('rounds.update_host');

        // Payment & Verification
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
        Route::post('/payments/{payment}/quick-cash', [PaymentController::class, 'quickCashPayment'])->name('payments.quick_cash');

        // Drawing & Disbursement
        Route::post('/rounds/{round}/draw/process', [DrawController::class, 'process'])->name('draws.process');
        Route::post('/rounds/{round}/disburse', [DrawController::class, 'disburse'])->name('draws.disburse');

        // Notifications & WhatsApp Blaster
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/send-bulk', [NotificationController::class, 'sendBulkReminder'])->name('notifications.send_bulk');
        Route::get('/payments/{payment}/send-reminder/{type?}', [NotificationController::class, 'sendPaymentReminder'])->name('notifications.send_reminder');
        Route::get('/payments/{payment}/send-late-notice', [NotificationController::class, 'sendLateNotice'])->name('notifications.send_late');

        // Reports & Export
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/print/{group}', [ReportController::class, 'printReport'])->name('reports.print');
        Route::get('/reports/export-csv/{group}', [ReportController::class, 'exportCsv'])->name('reports.export_csv');

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs.index');
    });
});
