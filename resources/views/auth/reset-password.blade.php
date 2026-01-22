@extends('layouts.app')

@section('title', '새 비밀번호 설정')

@section('content')
<div class="bg-[#EFF5FF] min-h-screen flex flex-col">
    <div class="flex-1 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-semibold text-gray-900">새 비밀번호 설정</h1>
                <p class="mt-2 text-gray-600">새 비밀번호를 입력해주세요</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8">
                @if($errors->has('error'))
                    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <p class="text-sm text-red-700">{{ $errors->first('error') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-3">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">이메일</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $email) }}"
                            required
                            autocomplete="email"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-300 @else border-gray-300 @enderror"
                            placeholder="example@gachon.ac.kr"
                        />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">새 비밀번호</label>
                        <input
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-300 @else border-gray-300 @enderror"
                            placeholder="8~64자"
                        />
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">새 비밀번호 확인</label>
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
                        비밀번호 재설정
                    </button>
                </form>

                <div class="mt-4 text-center text-sm text-gray-600">
                    <a href="{{ route('login') }}" class="text-blue-600 hover:underline">로그인으로 돌아가기</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



