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

        <!-- 이번 달 예약 비밀번호 -->
        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">이번 달 예약 비밀번호</h2>
                <div class="text-xs text-gray-500">
                    {{ $start->format('Y-m-d') }} ~ {{ $end->format('Y-m-d') }}
                </div>
            </div>

            @if($reservations->isEmpty())
                <div class="text-sm text-gray-600">이번 달 예약이 없습니다.</div>
            @else
                <form method="POST" action="{{ route('admin.keycodes.update') }}" class="space-y-4">
                    @csrf

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 border-b">
                                    <th class="py-2 pr-4">일시</th>
                                    <th class="py-2 pr-4">회의실</th>
                                    <th class="py-2 pr-4">사용자</th>
                                    <th class="py-2 pr-4">비밀번호(4자리)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reservations as $r)
                                    @php
                                        $rep = $r->getRepresentative();
                                        $repLabel = $rep ? ($rep->name . ' (' . $rep->email . ')') : ($r->users->first()?->email ?? '-');
                                    @endphp
                                    <tr class="border-b last:border-b-0">
                                        <td class="py-3 pr-4 whitespace-nowrap">
                                            {{ $r->start_at->format('m/d') }}
                                            {{ ($r->start_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $r->start_at->format('g:i') }}
                                            ~
                                            {{ ($r->end_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $r->end_at->format('g:i') }}
                                        </td>
                                        <td class="py-3 pr-4 whitespace-nowrap">{{ $r->room?->name ?? '-' }}</td>
                                        <td class="py-3 pr-4 whitespace-nowrap">{{ $repLabel }}</td>
                                        <td class="py-3 pr-4">
                                            <input
                                                name="keycodes[{{ $r->id }}]"
                                                value="{{ old('keycodes.' . $r->id, $r->key_code) }}"
                                                inputmode="numeric"
                                                maxlength="4"
                                                class="w-24 rounded-lg border px-3 py-2"
                                                placeholder="0000"
                                                required
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="submit" class="w-full bg-gray-900 text-white py-3 rounded-xl font-semibold">
                        저장하기
                    </button>
                </form>
            @endif
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


