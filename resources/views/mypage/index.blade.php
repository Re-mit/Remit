@extends('layouts.app')

@section('title', '마이페이지')

@section('content')
<div class="min-h-screen w-full max-w-[430px] mx-auto bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-4 py-4 flex items-center justify-between">
            <div class="w-[90px]"></div>
            <h1 class="text-xl font-bold text-gray-900">마이페이지</h1>
            <div class="w-[90px] flex items-center justify-end gap-2">
                @php
                    $user = \Illuminate\Support\Facades\Auth::user();
                    $envAdminEmail = config('admin.email');
                    $isAdmin = $user
                        && (
                            ($user->role ?? null) === 'admin'
                            || ($envAdminEmail && \App\Models\AllowedEmail::normalize($envAdminEmail) === \App\Models\AllowedEmail::normalize($user->email)) 
                        );
                @endphp
                @if($isAdmin)
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-gray-700">관리자</a>
                @endif
                <a href="{{ route('notification.index') }}" class="relative">
            <img src="{{ asset('images/notification-bell.png') }}" alt="알림" width="26" height="26" class="w-[26px] h-[26px]">
            <!-- Notification badge -->
            <!-- Notification badge -->
            @if(isset($unreadCount) && $unreadCount > 0)
            <span class="absolute -top-0.5 -right-1 w-3 h-3 bg-[#FF8282] rounded-full"></span>
            @endif
            </a>
            </div>
        </div>
    </div>

    <div class="px-4 py-6 space-y-6">
        @if(session('success'))
            <div class="bg-green-50 text-green-700 border border-green-200 rounded-xl p-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl p-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- 프로필 카드 -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="w-16 h-16 rounded-full">
                        <img src="{{ asset('images/profile-default.png') }}" alt="프로필" class="w-full h-full object-cover rounded-full">

                    </div>
                    <div class="ml-4 flex-1">
                        <h2 class="text-lg font-semibold text-gray-900">{{ Auth::user()->name ?? '사용자' }} 님</h2>
                        <p class="text-sm text-gray-600">{{ Auth::user()->email ?? '' }}</p>
                        @php
                            $penalties = (int) (Auth::user()->warning ?? 0);
                            $p = max(0, min(2, $penalties)); // 표시용(0~2)
                        @endphp
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-sm text-gray-600">패널티</span>
                            <div class="flex items-center gap-2" aria-label="패널티 표시(2개)">
                                <span class="inline-block rounded-full border {{ $p >= 1 ? 'bg-red-500 border-red/50' : 'bg-gray-800 border-black/50' }}" style="width:12px;height:12px"></span>
                                <span class="inline-block rounded-full border {{ $p >= 2 ? 'bg-red-500 border-red/50' : 'bg-gray-800 border-black/50' }}" style="width:12px;height:12px"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="mt-2 rounded-lg border bg-gray-50 p-3 text-xs text-gray-700">
                        <div class="font-semibold text-gray-900">패널티 기준 안내</div>
                        <ul class="mt-2 list-disc pl-5 space-y-1">
                            <li>기물파손 시: 패널티 1회</li>
                            <li>이용 후 뒷정리 미흡 시: 패널티 1회</li>
                            <li>음주 및 금지 행동 시: 패널티 2회</li>
                            <li>url 악용 시: 패널티 2회</li>
                        </ul>
                        <div class="mt-2 font-semibold text-red-600">패널티 2회 이상 시 자동 정지 처리됩니다.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 메뉴 버튼들 -->
        <div class="space-y-4">
            <!-- 열쇠함 비밀번호 버튼 -->
            <a href="{{ route('mypage.keycode') }}" class="block bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center">
                        <img src="{{ asset('images/key.png') }}" alt="열쇠함" width="25" height="23" class="w-[25px] h-[23px]">
                        <span class="ml-3 text-gray-900 font-medium">열쇠함 비밀번호</span>
                    </div>
                    <x-icons.chevron-right class="w-5 h-5 text-gray-400" />
                </div>
            </a>

            <!-- 문의하기 버튼 -->
            <a href="{{ route('mypage.inquiry.create') }}" class="block bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center">
                        <x-icons.chat class="w-6 h-6 text-blue-500" />
                        <span class="ml-3 text-gray-900 font-medium">문의하기</span>
                    </div>
                    <x-icons.chevron-right class="w-5 h-5 text-gray-400" />
                </div>
            </a>

            <!-- 서비스 개선 버튼 -->
            <a href="{{ route('mypage.error-report.create') }}" class="block bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center">
                        <x-icons.alert class="w-6 h-6 text-red-500" />
                        <span class="ml-3 text-gray-900 font-medium">서비스 개선하기</span>
                    </div>
                    <x-icons.chevron-right class="w-5 h-5 text-gray-400" />
                </div>
            </a>

            <!-- 공지사항 버튼 -->
            <a href="{{ route('notification.index') }}" class="block bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center">
                        <x-icons.bell class="w-[23px] h-5 text-blue-500" />
                        <span class="ml-3 text-gray-900 font-medium">공지사항</span>
                    </div>
                    <x-icons.chevron-right class="w-5 h-5 text-gray-400" />
                </div>
            </a>
        </div>

        <!-- 로그아웃 버튼 -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full bg-red-500 text-white py-4 px-4 rounded-xl font-semibold hover:bg-red-600 transition-all duration-200 flex items-center justify-center">
                <x-icons.logout class="w-5 h-5 mr-2" />
                로그아웃
            </button>
        </form>

        <!-- 회원탈퇴 버튼 (Hard delete) -->
        <form method="POST" action="{{ route('mypage.destroy') }}"
              onsubmit="return confirm('정말 회원탈퇴 하시겠습니까?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="w-full bg-gray-900 text-white py-4 px-4 rounded-xl font-semibold hover:bg-gray-800 transition-all duration-200 flex items-center justify-center">
                회원탈퇴
            </button>
        </form>
    </div>
</div>
@endsection
