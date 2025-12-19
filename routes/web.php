<?php


use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\AdminController;

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
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register/send-code', [AuthController::class, 'sendRegisterCode'])->name('register.send_code');
    Route::post('/register/verify-code', [AuthController::class, 'verifyRegisterCode'])->name('register.verify_code');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


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
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notification.read');
    
    // 마이페이지
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index');
    Route::get('/mypage/keycode', [MypageController::class, 'keycode'])->name('mypage.keycode');

    // 관리자
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/keycodes', [AdminController::class, 'updateKeycodes'])->name('admin.keycodes.update');
    Route::post('/admin/notices', [AdminController::class, 'storeNotice'])->name('admin.notices.store');
});

