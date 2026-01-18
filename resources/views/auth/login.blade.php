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
                <h1 class="text-2xl font-semibold text-gray-900">금융수학과 스터디룸</h1>
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

                <!-- Local Login Form -->
                <form method="POST" action="{{ route('login.attempt') }}" class="mt-4 space-y-3">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">이메일</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="example@gachon.ac.kr"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">비밀번호</label>
                        <input
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="비밀번호"
                        />
                    </div>

                    <div class="text-right">
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">
                            비밀번호를 잊으셨나요?
                        </a>
                    </div>

                    <button
                        type="submit"
                        class="w-full px-4 py-3 rounded-lg bg-blue-500 text-white font-medium hover:bg-blue-600 transition-colors duration-200"
                    >
                        로그인
                    </button>
                </form>

                <div class="mt-4 text-center text-sm text-gray-600">
                    아직 계정이 없나요?
                    <a href="{{ route('register') }}" class="text-blue-600 hover:underline">회원가입</a>
                </div>

                @if(config('help.video_url'))
                    <div class="mt-3 text-center text-sm">
                        <a href="{{ config('help.video_url') }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-blue-500 hover:text-white transition-colors">
                            사용법 보기
                        </a>
                    </div>
                @endif

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

                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="mt-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <p class="text-sm text-red-700">{{ $errors->first() }}</p>
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
