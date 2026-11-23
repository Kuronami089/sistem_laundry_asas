<?php

use App\Http\Controllers\LaundryItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Halaman utama pemesanan laundry bagi pelanggan (wajib login)
Route::get('/', [OrderController::class, 'index'])
    ->middleware('auth')
    ->name('customer.index');

// Proses pemesanan laundry langsung
Route::post('/checkout', [OrderController::class, 'store'])
    ->middleware('auth')
    ->name('customer.checkout');

// Halaman Admin: Rekap Pesanan & CRUD Layanan Laundry (cek role admin)
Route::group(['middleware' => ['auth', function ($request, $next) {
    if (Auth::user()->role !== 'admin') {
        if ($request->routeIs('dashboard')) {
            return redirect()->route('customer.index');
        }
        return redirect()->route('customer.index')
            ->with('error', 'Anda tidak memiliki akses ke halaman admin!');
    }

    return $next($request);
}]], function () {
    Route::get('/dashboard', [OrderController::class, 'adminDashboard'])->name('dashboard');
    Route::get('/admin/dashboard', [OrderController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::patch('/admin/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.updateStatus');
    Route::get('/admin/orders/{id}/struk', [OrderController::class, 'viewStrukBackend'])->name('admin.orders.viewStruk');
    Route::resource('/admin/laundry-items', LaundryItemController::class)->names('laundry-items');
});

// Profile bawaan Breeze
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
