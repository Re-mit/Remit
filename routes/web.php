<?php


use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\Mypage\InquiryController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Auth;

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
    // 로그인 상태에서 '/' 접근 시 login으로 보내면 guest 미들웨어와 충돌하여 무한 리다이렉트가 날 수 있음
    if (Auth::check()) {
        return redirect()->route('reservation.index');
    }

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

    // Password Reset (표준 Password Broker)
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


// Protected Routes (Auth Required)
Route::middleware(['auth', 'not_suspended'])->group(function () {
    // guest 미들웨어(로그인 상태에서 /login 접근) 기본 리다이렉트 목적지 제공
    Route::get('/home', function () {
        return redirect()->route('reservation.index');
    })->name('home');

    // 예약하기
    Route::get('/reservation', [ReservationController::class, 'index'])->name('reservation.index');
    Route::post('/reservation', [ReservationController::class, 'store'])->name('reservation.store');
    Route::post('/reservation/available-seats', [ReservationController::class, 'availableSeats'])->name('reservation.available_seats');
    Route::get('/reservation/{id}/confirm', [ReservationController::class, 'confirm'])->name('reservation.confirm');
    Route::get('/reservation/confirm-multi', [ReservationController::class, 'confirmMulti'])->name('reservation.confirm_multi');
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
    Route::get('/mypage/inquiry', [InquiryController::class, 'create'])->name('mypage.inquiry.create');
    Route::post('/mypage/inquiry', [InquiryController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('mypage.inquiry.store');
    Route::delete('/mypage', [MypageController::class, 'destroy'])->name('mypage.destroy');

    // 관리자
    Route::prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        Route::get('/urls', [AdminController::class, 'urls'])->name('admin.urls');
        Route::post('/urls', [AdminController::class, 'updateLockboxUrls'])->name('admin.urls.update');

        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/users', [AdminController::class, 'storeAllowedEmail'])->name('admin.users.store');
        Route::delete('/users/{id}', [AdminController::class, 'destroyAllowedEmail'])->name('admin.users.destroy');
        Route::post('/users/{id}/penalty', [AdminController::class, 'addPenalty'])->name('admin.users.penalty');
        Route::post('/users/{id}/penalty/reset', [AdminController::class, 'resetPenalty'])->name('admin.users.penalty.reset');
        Route::post('/users/{id}/unsuspend', [AdminController::class, 'unsuspendUser'])->name('admin.users.unsuspend');
        Route::post('/admins', [AdminController::class, 'storeAdmin'])->name('admin.admins.store');
        Route::delete('/admins/{id}', [AdminController::class, 'destroyAdmin'])->name('admin.admins.destroy');

        Route::get('/notices', [AdminController::class, 'notices'])->name('admin.notices');
        Route::post('/notices', [AdminController::class, 'storeNotice'])->name('admin.notices.store');
        Route::delete('/notices/{id}', [AdminController::class, 'destroyNotice'])->name('admin.notices.destroy');

        Route::get('/reservations', [AdminController::class, 'reservations'])->name('admin.reservations');
        Route::get('/reservations/history', [AdminController::class, 'reservationsHistory'])->name('admin.reservations.history');
        Route::delete('/reservations/{id}', [AdminController::class, 'destroyReservation'])->name('admin.reservations.destroy');
    });
});

