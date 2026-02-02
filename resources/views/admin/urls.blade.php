@extends('layouts.app')

@section('title', '관리자 - URL 등록')
@section('admin_title', 'URL 등록')

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
            <div class="mb-2">
                <h2 class="text-lg font-bold text-gray-900">열쇠함 URL</h2>
            </div>

            <form method="POST" action="{{ route('admin.urls.update') }}" class="space-y-4">
                @csrf
                <div class="rounded-xl border p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">고정 URL</label>
                    <input
                        name="url"
                        value="{{ old('url', $lockboxUrl ?? '') }}"
                        class="w-full rounded-lg border px-3 py-2"
                        placeholder="https://example.com/lockbox/..."
                        required
                    />
                    <div class="mt-2 text-xs text-gray-500">
                        - 예약자는 예약 시간 10분 전부터(종료 전까지) 이 URL을 확인하게 됩니다.
                    </div>
                </div>

                <button type="submit" class="w-full bg-gray-900 text-white py-3 rounded-xl font-semibold">
                    저장하기
                </button>
            </form>
        </div>
    </div>
</div>
@endsection


