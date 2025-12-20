@extends('layouts.app')

@section('title', '관리자')
@section('admin_title', '관리자')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24">
    @include('admin._nav')

    <div class="p-5 space-y-6">
        @if(session('success'))
            <div class="bg-green-50 text-green-700 border border-green-200 rounded-xl p-4">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl p-4 space-y-1">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <h2 class="text-lg font-bold text-gray-900 mb-2">관리자 메뉴</h2>
            <p class="text-sm text-gray-600">아래 기능을 선택하세요.</p>

            <div class="mt-4 grid gap-3">
                <a href="{{ route('admin.urls') }}" class="block rounded-xl border p-4 hover:bg-gray-50">
                    <div class="font-semibold text-gray-900">URL 등록</div>
                    <div class="text-sm text-gray-600 mt-1">3일 단위 × 10개 (총 30일)</div>
                </a>
                <a href="{{ route('admin.users') }}" class="block rounded-xl border p-4 hover:bg-gray-50">
                    <div class="font-semibold text-gray-900">사용자 관리</div>
                    <div class="text-sm text-gray-600 mt-1">회원가입 허용 이메일(화이트리스트) 관리</div>
                </a>
                <a href="{{ route('admin.notices') }}" class="block rounded-xl border p-4 hover:bg-gray-50">
                    <div class="font-semibold text-gray-900">공지 작성</div>
                    <div class="text-sm text-gray-600 mt-1">전체 사용자에게 공지 알림 발송</div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection


