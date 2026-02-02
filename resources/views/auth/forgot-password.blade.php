@extends('layouts.app')

@section('title', '비밀번호 재설정')

@section('content')
<div class="bg-[#EFF5FF] min-h-screen flex flex-col">
    <div class="flex-1 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-semibold text-gray-900">비밀번호 재설정</h1>
                <p class="mt-2 text-gray-600">가입하신 이메일로 재설정 링크를 보내드립니다</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8">
                @if(session('status'))
                    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                        <p class="text-sm text-green-700">{{ session('status') }}</p>
                    </div>
                @endif

                @if($errors->has('error'))
                    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <p class="text-sm text-red-700">{{ $errors->first('error') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">이메일</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-300 @else border-gray-300 @enderror"
                            placeholder="example@gachon.ac.kr"
                        />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full px-4 py-3 rounded-lg bg-blue-500 text-white font-medium hover:bg-blue-600 transition-colors duration-200"
                    >
                        재설정 링크 보내기
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







