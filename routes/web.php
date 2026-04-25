<?php

use App\Http\Controllers\ApprovalController;
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
    Route::delete('/comments/{comment}', [RequestCommentController::class, 'destroy'])->name('comments.destroy');

    // Approvals (manager, marcom, admin only)
    Route::middleware(\App\Http\Middleware\EnsureCanApprove::class)->prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/',             [ApprovalController::class, 'index'])->name('index');
        Route::get('/all',          [ApprovalController::class, 'all'])->name('all');
        Route::get('/{request}',    [ApprovalController::class, 'show'])->name('show');
        Route::patch('/{request}',  [ApprovalController::class, 'decide'])->name('decide');
    });

    // User Management (admin only)
    Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});

require __DIR__ . '/auth.php';
