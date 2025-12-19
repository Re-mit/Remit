@extends('layouts.app')

@section('title', '회원가입')

@section('content')
<div class="bg-[#EFF5FF] min-h-screen flex flex-col">
    <div class="flex-1 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-semibold text-gray-900">회원가입</h1>
                <p class="mt-2 text-gray-600">가천 이메일로 계정을 생성합니다</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8">
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">이름</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="홍길동"
                        />
                    </div>

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
                            autocomplete="new-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="8자 이상"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">비밀번호 확인</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="비밀번호 확인"
                        />
                    </div>

                    <button
                        type="submit"
                        class="w-full px-4 py-3 rounded-lg bg-blue-500 text-white font-medium hover:bg-blue-600 transition-colors duration-200"
                    >
                        회원가입
                    </button>
                </form>

                <div class="mt-4 text-center text-sm text-gray-600">
                    이미 계정이 있나요?
                    <a href="{{ route('login') }}" class="text-blue-600 hover:underline">로그인</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


