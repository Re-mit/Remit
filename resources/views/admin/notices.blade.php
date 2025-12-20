@extends('layouts.app')

@section('title', '관리자 - 공지 작성')
@section('admin_title', '공지 작성')

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
            <h2 class="text-lg font-bold text-gray-900 mb-4">공지사항 발송</h2>

            <form method="POST" action="{{ route('admin.notices.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">제목</label>
                    <input name="title" value="{{ old('title') }}" class="w-full rounded-lg border px-3 py-2" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">내용</label>
                    <textarea name="message" rows="6" class="w-full rounded-lg border px-3 py-2" required>{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="w-full bg-blue-500 text-white py-3 rounded-xl font-semibold hover:bg-blue-600">
                    공지 발송
                </button>
            </form>
        </div>
    </div>
</div>
@endsection


