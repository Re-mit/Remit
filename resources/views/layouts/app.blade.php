<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remit - @yield('title', '예약 시스템')</title>

    <!-- Compiled CSS/JS (Webpack / Laravel Mix) -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script defer src="{{ mix('js/app.js') }}"></script>

    <!-- Google Fonts (Example: Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 text-gray-800 antialiased">

    @php
        $showBottomNav = \Illuminate\Support\Facades\Auth::check();
    @endphp

    <!-- App Container (Mobile-first max width) -->
    <div class="mx-auto w-full max-w-[430px] min-h-screen bg-gray-50 relative shadow-sm">
        <!-- Page Content -->
        <main class="{{ $showBottomNav ? 'pb-16' : '' }}">
            @yield('content')
        </main>
    </div>

    <!-- Bottom Navigation (Mobile) -->
    @if($showBottomNav)
    <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[430px] bg-white border-t border-gray-200 z-50">
        <div class="flex justify-around items-center h-16 w-full">
            <!-- 예약하기 -->
            <a href="{{ route('reservation.index') }}" 
               class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('reservation.index') ? 'text-indigo-600' : 'text-gray-600' }}">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-xs mt-1">예약</span>
            </a>

            <!-- 예약조회 -->
            <a href="{{ route('reservation.my') }}" 
               class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('reservation.my') ? 'text-indigo-600' : 'text-gray-600' }}">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span class="text-xs mt-1">예약조회</span>
            </a>



            <!-- 마이페이지 -->
            <a href="{{ route('mypage.index') }}" 
               class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('mypage.index') ? 'text-indigo-600' : 'text-gray-600' }}">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-xs mt-1">마이페이지</span>
            </a>
        </div>
    </nav>
    @endif

    @stack('scripts')
</body>
</html>
