<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemDashboardController;
use App\Http\Controllers\AdminUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;

Route::get('/', fn () => redirect()->route('login'));

// Auth routes should be accessible to guests
require __DIR__.'/auth.php';

// ============================================
// 🆕 WEB-CRON ROUTE (Scheduled Tasks & Queue)
// ============================================
Route::get('/__internal/schedule', function (Request $request) {
    $given = (string) $request->query('key', '');
    $expected = (string) env('CRON_KEY');

    // Security check: abort if key doesn't match
    abort_unless($expected && hash_equals($expected, $given), 403, 'Unauthorized');

    // 1) Trigger all scheduled tasks (including 08:00 email if due)
    Artisan::call('schedule:run');

    // 2) Process queue once (ensure emails are actually sent)
    Artisan::call('queue:work', [
        '--once'            => true,
        '--stop-when-empty' => true,
        '--quiet'           => true,
    ]);

    return Response::json([
        'ok' => true,
        'timestamp' => now()->toDateTimeString(),
        'message' => 'Schedule and queue processed successfully'
    ]);
});

// ============================================
// AUTHENTICATED ROUTES
// ============================================
Route::middleware(['auth','verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard', [ItemDashboardController::class, 'index'])->name('dashboard');

    // Dashboard Items Routes
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::prefix('items')->name('items.')->group(function () {
            Route::get('/',       [ItemDashboardController::class, 'index'])->name('index');
            Route::get('/list',   [ItemDashboardController::class, 'list'])->name('list');
            Route::get('/events', [ItemDashboardController::class, 'events'])->name('events');
            Route::post('/',      [ItemDashboardController::class, 'store'])->name('store');
            Route::patch('/{id}', [ItemDashboardController::class, 'update'])->whereNumber('id')->name('update');
            Route::patch('/{id}/status', [ItemDashboardController::class, 'updateStatus'])->whereNumber('id')->name('status');
            Route::get('/{id}',   [ItemDashboardController::class, 'show'])->whereNumber('id')->name('show');

            // Edit Payload (owner/admin only via policy)
            Route::get('/{id}/edit-payload', [ItemDashboardController::class, 'editPayload'])
                ->whereNumber('id')
                ->name('editPayload');

            // Admin only routes
            Route::middleware('role:admin')->group(function () {
                Route::get('/export', [ItemDashboardController::class, 'export'])->name('export');
                Route::delete('/{id}', [ItemDashboardController::class, 'destroy'])->whereNumber('id')->name('destroy');
            });
        });
    });

    // ============================================
    // ADMIN ROUTES (Users Management)
    // ============================================
    Route::middleware(['role:admin'])->group(function () {
        // User management
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/export', [AdminUserController::class, 'export'])->name('admin.users.export');
    });

    // Logout route
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});
