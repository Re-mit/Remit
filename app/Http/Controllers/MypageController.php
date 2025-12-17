<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MypageController extends Controller
{
    /**
     * Display mypage
     */
    public function index()
    {
        return view('mypage.index');
    }

    /**
     * Display key code page
     */
    public function keycode()
    {
        $user = Auth::user();
        $reservations = $user->reservations()
            ->with(['room'])
            ->where('status', 'confirmed')
            ->orderBy('start_at', 'desc')
            ->get();

        return view('mypage.keycode', compact('reservations'));
    }
}
