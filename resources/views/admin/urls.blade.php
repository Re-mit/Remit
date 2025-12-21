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
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <h2 class="text-lg font-bold text-gray-900">열쇠함 URL</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.urls', ['month' => $prevMonth]) }}"
                       class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 whitespace-nowrap">
                        이전달
                    </a>
                    <div class="text-sm font-bold text-gray-900">
                        {{ $start->format('Y년 m월') }}
                    </div>
                    <a href="{{ route('admin.urls', ['month' => $nextMonth]) }}"
                       class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 whitespace-nowrap">
                        다음달
                    </a>
                </div>
            </div>
            <div class="text-xs text-gray-500 mb-4">
                적용 범위: {{ $start->format('Y-m-d') }} ~ {{ $end->format('Y-m-d') }} ({{ $daysInMonth ?? $start->daysInMonth }}일)
            </div>

            <form method="POST" action="{{ route('admin.urls.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}" />

                <div class="space-y-3">
                    @foreach($blocks as $b)
                        <div class="rounded-xl border p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-semibold text-gray-900">
                                    {{ $b['index'] }}번 ({{ $b['start_date'] }} ~ {{ $b['end_date'] }})
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $b['index'] === 10 ? '말일까지' : '3일' }}
                                </div>
                            </div>
                            <input
                                name="urls[{{ $b['start_date'] }}]"
                                value="{{ old('urls.' . $b['start_date'], $b['url']) }}"
                                class="w-full rounded-lg border px-3 py-2"
                                placeholder="https://example.com/lockbox/..."
                                required
                            />
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="w-full bg-gray-900 text-white py-3 rounded-xl font-semibold">
                    저장하기
                </button>
            </form>
        </div>
    </div>
</div>
@endsection


