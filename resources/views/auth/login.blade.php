<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remit - 로그인</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    
    <!-- Mobile-First Login Container -->
    <div class="flex-1 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">
            
            <!-- Logo/Title Section -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Remit</h1>
                <p class="mt-2 text-gray-600">스터디룸 예약 시스템</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8">
                <div class="text-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">로그인</h2>
                    <p class="mt-2 text-sm text-gray-600">학교 구글 계정으로 시작하세요</p>
                </div>

                <!-- Google Login Button -->
                <a href="{{ route('auth.google') }}" 
                   class="flex items-center justify-center w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm bg-white hover:bg-gray-50 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Google 계정으로 로그인</span>
                </a>

                <!-- Info Message (if exists) -->
                @if(session('info'))
                    <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="ml-3 text-sm text-blue-700">{{ session('info') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Info Text -->
                <p class="mt-6 text-center text-xs text-gray-500">
                    로그인 시 학교 이메일 계정만 접근할 수 있습니다
                </p>
            </div>

            <!-- Additional Info (Mobile Optimized) -->
            <div class="mt-6 text-center text-sm text-gray-600">
                <p>처음 사용하시나요?</p>
                <p class="mt-1">계정이 자동으로 생성됩니다</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-6 text-center text-sm text-gray-500">
        <p>&copy; {{ date('Y') }} Remit. All rights reserved.</p>
    </footer>

</body>
</html>
