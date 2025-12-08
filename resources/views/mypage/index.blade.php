@extends('layouts.app')

@section('title', '마이페이지')

@section('content')
<div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-4 py-4">
            <h1 class="text-xl font-bold text-gray-900">마이페이지</h1>
        </div>
    </div>

    <div class="px-4 py-6 space-y-6">
        <!-- 프로필 카드 -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center">
                        <span class="text-2xl font-bold text-white">
                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                        </span>
                    </div>
                    <div class="ml-4 flex-1">
                        <h2 class="text-lg font-semibold text-gray-900">{{ Auth::user()->name ?? '사용자' }}</h2>
                        <p class="text-sm text-gray-600">{{ Auth::user()->email ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 메뉴 목록 -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-100">
                <!-- 내 예약 확인 -->
                <a href="{{ route('reservation.my') }}" class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="ml-3 text-gray-900">내 예약 확인</span>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <!-- 예약 규칙 -->
                <div class="p-4">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="ml-3 text-gray-900">예약 규칙</span>
                    </div>
                    <div class="mt-2 ml-9 text-sm text-gray-600">
                        <p>• 최대 4시간 예약 가능</p>
                        <p>• 1주일 이내 예약만 허용</p>
                        <p>• 동시간대 중복 예약 불가</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 로그아웃 버튼 -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full bg-white border border-red-300 text-red-600 py-3 px-4 rounded-xl font-medium hover:bg-red-50 transition-all duration-200">
                로그아웃
            </button>
        </form>
    </div>
</div>
@endsection
