<?php


use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MypageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Home -> Redirect to Login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// Protected Routes (Auth Required)
Route::middleware(['auth'])->group(function () {
    // 예약하기
    Route::get('/reservation', [ReservationController::class, 'index'])->name('reservation.index');
    Route::post('/reservation', [ReservationController::class, 'store'])->name('reservation.store');
    Route::get('/reservation/{id}/confirm', [ReservationController::class, 'confirm'])->name('reservation.confirm');
    Route::get('/reservation/{id}/detail', [ReservationController::class, 'detail'])->name('reservation.detail');
    Route::delete('/reservation/{id}', [ReservationController::class, 'destroy'])->name('reservation.destroy');
    
    // 예약조회 (내 예약)
    Route::get('/reservation/my', [ReservationController::class, 'my'])->name('reservation.my');
    
    // 알림
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notification.index');
    
    // 마이페이지
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index');
});


// Admin Routes (to be implemented)
// Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
//     Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
// });

