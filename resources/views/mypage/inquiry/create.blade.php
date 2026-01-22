@extends('layouts.app')

@section('title', '문의하기')

@section('content')
<div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-4 py-4 flex items-center">
            <a href="{{ route('mypage.index') }}" class="mr-4">
                <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-xl font-bold text-gray-900">문의하기</h1>
        </div>
    </div>

    <div class="px-4 py-6 space-y-4">
        @if(session('success'))
            <div class="bg-green-50 text-green-700 border border-green-200 rounded-xl p-4">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->has('error'))
            <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl p-4">
                {{ $errors->first('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <form method="POST" action="{{ route('mypage.inquiry.store') }}" class="p-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">제목</label>
                    <input type="text"
                           name="title"
                           value="{{ old('title') }}"
                           maxlength="100"
                           required
                           class="w-full rounded-xl border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 @error('title') border-red-300 focus:ring-red-200 @else border-gray-300 focus:border-blue-300 @enderror"
                           placeholder="문의 제목을 입력해주세요. (최대 100자)">
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">내용</label>
                    <textarea name="content"
                              rows="8"
                              maxlength="5000"
                              required
                              class="w-full rounded-xl border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 @error('content') border-red-300 focus:ring-red-200 @else border-gray-300 focus:border-blue-300 @enderror"
                              placeholder="문의 내용을 입력해주세요. (최대 5000자)">{{ old('content') }}</textarea>
                    @error('content')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full px-4 py-3 rounded-xl bg-blue-500 text-white font-semibold hover:bg-blue-600 transition-colors">
                    전송하기
                </button>
            </form>
        </div>

        <div class="bg-blue-50 rounded-xl p-5">
            <p class="text-sm text-gray-700">
                문의 내용은 관리자에게 전달됩니다. (작성자 정보: {{ auth()->user()?->name }} / {{ auth()->user()?->email }})
            </p>
        </div>
    </div>
</div>
@endsection



