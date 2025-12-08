<?php

namespace App\Http\Controllers;

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
}
