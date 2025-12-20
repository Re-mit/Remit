@extends('layouts.app')

@section('title', '관리자')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24">
    <!-- Header -->
    <div class="bg-white sticky top-0 z-20 border-b shadow-sm">
        <div class="flex items-center justify-between px-5 py-4">
            <a href="{{ route('reservation.index') }}" class="text-sm text-gray-600">←</a>
            <h1 class="text-xl font-bold text-gray-900">관리자</h1>
            <div class="w-[26px]"></div>
        </div>
    </div>

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

        <!-- 30일(3일 단위) 열쇠함 URL -->
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">열쇠함 URL (3일 단위 × 10개)</h2>
                <div class="text-xs text-gray-500">
                    {{ $start->format('Y-m-d') }} ~ {{ $end->format('Y-m-d') }}
                </div>
            </div>

            <form method="POST" action="{{ route('admin.keycodes.update') }}" class="space-y-4">
                @csrf

                <div class="space-y-3">
                    @foreach($blocks as $b)
                        <div class="rounded-xl border p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-semibold text-gray-900">
                                    {{ $b['index'] }}번 ({{ $b['start_date'] }} ~ {{ $b['end_date'] }})
                                </div>
                                <div class="text-xs text-gray-500">3일</div>
                            </div>
                            <input
                                name="urls[{{ $b['start_date'] }}]"
                                value="{{ old('urls.' . $b['start_date'], $b['url']) }}"
                                class="w-full rounded-lg border px-3 py-2"
                                placeholder="https://example.com/lockbox/..."
                                required
                            />
                            <div class="mt-2 text-xs text-gray-500">
                                - 해당 구간(3일)의 예약자는 이 URL을 확인하게 됩니다.
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="w-full bg-gray-900 text-white py-3 rounded-xl font-semibold">
                    저장하기
                </button>
            </form>
        </div>

        <!-- 공지사항 발송 -->
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
                    <textarea name="message" rows="5" class="w-full rounded-lg border px-3 py-2" required>{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold">
                    공지 발송
                </button>
            </form>
        </div>
    </div>
</div>
@endsection



