@extends('layouts.app')

@section('title', '로그인')

@section('content')
<div class="bg-[#EFF5FF] min-h-screen flex flex-col">
    
    <!-- Mobile-First Login Container -->
    <div class="flex-1 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">
            
            <!-- Logo/Title Section -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl mb-4">
                <svg width="34" height="30" viewBox="0 0 34 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_30_92)">
                <path d="M18.75 1.87498C18.75 1.2949 18.4863 0.749981 18.0293 0.398418C17.5723 0.0468558 16.9805 -0.0820504 16.418 0.0585746L5.87695 2.69529C4.62891 3.00584 3.75 4.13084 3.75 5.4199V26.25H1.875C0.837891 26.25 0 27.0879 0 28.125C0 29.1621 0.837891 30 1.875 30H5.625H16.875H18.75V28.125V1.87498ZM15 15C15 16.0371 14.373 16.875 13.5938 16.875C12.8145 16.875 12.1875 16.0371 12.1875 15C12.1875 13.9629 12.8145 13.125 13.5938 13.125C14.373 13.125 15 13.9629 15 15ZM20.625 7.49998H26.25V28.125C26.25 29.1621 27.0879 30 28.125 30H31.875C32.9121 30 33.75 29.1621 33.75 28.125C33.75 27.0879 32.9121 26.25 31.875 26.25H30V16.875V7.49998C30 5.43162 28.3184 3.74998 26.25 3.74998H20.625V7.49998Z" fill="white"/>
                </g>
                <defs>
                <clipPath id="clip0_30_92">
                <path d="M0 0H33.75V30H0V0Z" fill="white"/>
                </clipPath>
                </defs>
                </svg>


                </div>
                <h1 class="text-2xl font-semibold text-gray-900">가천관 622호</h1>
                <p class="mt-2 text-gray-600">예약 시스템</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8">
                <div class="text-center mb-3">
                    <h2 class="text-xl font-semibold text-gray-900">로그인</h2>
                    <p class="mt-4 text-[12px] text-[#1E40AF] bg-[#EFF6FF] px-4 py-4 rounded-md border border-[#BFDBFE] flex items-center gap-2">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_30_95)">
                        <path d="M9 18C11.3869 18 13.6761 17.0518 15.364 15.364C17.0518 13.6761 18 11.3869 18 9C18 6.61305 17.0518 4.32387 15.364 2.63604C13.6761 0.948212 11.3869 0 9 0C6.61305 0 4.32387 0.948212 2.63604 2.63604C0.948212 4.32387 0 6.61305 0 9C0 11.3869 0.948212 13.6761 2.63604 15.364C4.32387 17.0518 6.61305 18 9 18ZM7.59375 11.8125H8.4375V9.5625H7.59375C7.12617 9.5625 6.75 9.18633 6.75 8.71875C6.75 8.25117 7.12617 7.875 7.59375 7.875H9.28125C9.74883 7.875 10.125 8.25117 10.125 8.71875V11.8125H10.4062C10.8738 11.8125 11.25 12.1887 11.25 12.6562C11.25 13.1238 10.8738 13.5 10.4062 13.5H7.59375C7.12617 13.5 6.75 13.1238 6.75 12.6562C6.75 12.1887 7.12617 11.8125 7.59375 11.8125ZM9 4.5C9.29837 4.5 9.58452 4.61853 9.7955 4.8295C10.0065 5.04048 10.125 5.32663 10.125 5.625C10.125 5.92337 10.0065 6.20952 9.7955 6.4205C9.58452 6.63147 9.29837 6.75 9 6.75C8.70163 6.75 8.41548 6.63147 8.2045 6.4205C7.99353 6.20952 7.875 5.92337 7.875 5.625C7.875 5.32663 7.99353 5.04048 8.2045 4.8295C8.41548 4.61853 8.70163 4.5 9 4.5Z" fill="#2563EB"/>
                        </g>
                        <defs>
                        <clipPath id="clip0_30_95">
                        <path d="M0 0H18V18H0V0Z" fill="white"/>
                        </clipPath>
                        </defs>
                        </svg>
                        가천 이메일로만 로그인이 가능합니다.
                    </p>
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

                <!-- Success Message -->
                @if(session('success'))
                    <div class="mt-4 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Error Message -->
                @if(session('error'))
                    <div class="mt-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="ml-3 text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Info Message -->
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
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-6 text-center text-sm text-gray-500">
        <p>&copy; {{ date('Y') }} Remit. All rights reserved.</p>
    </footer>

</div>
@endsection
