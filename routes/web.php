<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CalendarEventApprovalController;
use App\Http\Controllers\MarketingRequestController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequestCommentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Lightweight polling endpoint — returns counts for new-request notifications
    Route::get('/poll-counts', function () {
        $user = auth()->user();
        return response()->json([
            'pending_approvals' => \App\Models\MarketingRequest::whereIn('status', ['submitted', 'under_review'])->count(),
            'production_active' => \App\Models\MarketingRequest::where('status', 'approved')
                ->whereIn('production_status', ['pending', 'on_process', 'revision'])
                ->count(),
            'my_requests'       => \App\Models\MarketingRequest::where('user_id', $user->id)->count(),
        ]);
    })->name('poll.counts');

    // Marketing Requests (all authenticated users)
    Route::resource('requests', MarketingRequestController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
    Route::get('/requests/{request}/print', [MarketingRequestController::class, 'print'])->name('requests.print');

    // Production status management (admin/marcom) + user tracking
    Route::get('/production',                                       [ProductionController::class, 'index'])->name('production.index');
    Route::get('/production/completed',                             [ProductionController::class, 'completed'])->name('production.completed');
    Route::patch('/requests/{marketingRequest}/production', [ProductionController::class, 'update'])->name('production.update');
    Route::get('/requests/{marketingRequest}/track',        [ProductionController::class, 'track'])->name('requests.track');

    // Comments
    Route::post('/requests/{marketingRequest}/comments', [RequestCommentController::class, 'store'])->name('comments.store');

    // Notifications — mark all as read
    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.readAll');

    // Notifications — mark single as read and redirect to request
    Route::get('/notifications/{id}/read', function (string $id) {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        $requestId = $notification->data['marketing_request_id'] ?? null;
        if ($requestId) {
            return redirect()->route('requests.show', $requestId);
        }
        return redirect()->route('dashboard');
    })->name('notifications.read');

    // Approvals (manager, marcom, admin only)
    Route::middleware(\App\Http\Middleware\EnsureCanApprove::class)->prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/',             [ApprovalController::class, 'index'])->name('index');
        Route::get('/all',          [ApprovalController::class, 'all'])->name('all');
        Route::get('/{request}',    [ApprovalController::class, 'show'])->name('show');
        Route::patch('/{request}',  [ApprovalController::class, 'decide'])->name('decide');
    });

    // Mention autocomplete — returns name + username for all users (accessible to all authenticated users)
    Route::get('/users/suggestions', function () {
        return \App\Models\User::select('id', 'name', 'username')->orderBy('name')->get();
    })->name('users.suggestions');

    // User Management (admin only)
    Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // Calendar (Marcom / Admin)
    Route::get('/calendar',                            [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events',                     [CalendarController::class, 'events'])->name('calendar.events');
    Route::post('/calendar',                           [CalendarController::class, 'store'])->name('calendar.store');
    Route::get('/calendar/pending',                    [CalendarController::class, 'pending'])->name('calendar.pending');
    Route::get('/calendar/{calendarEvent}/ical',       [CalendarController::class, 'ical'])->name('calendar.ical');
    Route::patch('/calendar/{calendarEvent}',          [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{calendarEvent}',         [CalendarController::class, 'destroy'])->name('calendar.destroy');

    // Calendar event approvals (Manager, GM, Director)
    Route::get('/calendar-approvals',                              [CalendarEventApprovalController::class, 'index'])->name('calendar.approvals');
    Route::post('/calendar-approvals/{calendarEvent}/decide',      [CalendarEventApprovalController::class, 'decide'])->name('calendar.approvals.decide');
});

require __DIR__ . '/auth.php';
