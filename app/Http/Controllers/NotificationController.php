<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display the notification list
     */
    public function index()
    {
        // TODO: 추후 알림 기능 구현
        return view('notification.index');
    }
}
