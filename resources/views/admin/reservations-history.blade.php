@extends('layouts.app')

@section('title', '관리자 - 예약 내역(최근 1달)')
@section('admin_title', '예약 내역(최근 1달)')

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

        <div class="bg-white rounded-2xl shadow-sm border p-5 space-y-2">
            <h2 class="text-lg font-bold text-gray-900">최근 1달 예약 내역</h2>
            <p class="text-sm text-gray-600">
                기준일: {{ $cutoff->format('Y-m-d') }} 이후 (완료된 예약은 종료시간 기준, 취소된 예약은 취소시간 기준)
            </p>

            <div class="flex gap-2 pt-2">
                <a href="{{ route('admin.reservations.history', ['status' => 'all']) }}"
                   class="px-3 py-2 rounded-xl text-sm font-semibold border {{ $status === 'all' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white border-gray-200 text-gray-700' }}">
                    전체
                </a>
                <a href="{{ route('admin.reservations.history', ['status' => 'confirmed']) }}"
                   class="px-3 py-2 rounded-xl text-sm font-semibold border {{ $status === 'confirmed' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white border-gray-200 text-gray-700' }}">
                    예약완료
                </a>
                <a href="{{ route('admin.reservations.history', ['status' => 'cancelled']) }}"
                   class="px-3 py-2 rounded-xl text-sm font-semibold border {{ $status === 'cancelled' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white border-gray-200 text-gray-700' }}">
                    취소/삭제
                </a>
            </div>
        </div>

        @if($reservations->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border p-8 text-center text-gray-600">
                최근 1달 내 예약 내역이 없습니다.
            </div>
        @else
            <div class="space-y-3">
                @foreach($reservations as $r)
                    @php
                        $rep = $r->getRepresentative();
                        $repLabel = $rep ? ($rep->name . ' (' . $rep->email . ')') : ($r->users->first()?->email ?? '-');
                        $statusText = $r->status === 'confirmed'
                            ? '예약완료'
                            : (($r->cancelled_by ?? null) === 'admin' ? '삭제됨' : '취소됨');
                        $statusClass = $r->status === 'confirmed'
                            ? 'bg-green-50 text-green-700 border-green-200'
                            : 'bg-red-50 text-red-700 border-red-200';
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <div class="text-sm text-gray-600">
                                        {{ $r->start_at->format('Y-m-d') }}
                                    </div>
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold border {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </div>

                                <div class="text-lg font-bold text-gray-900">
                                    {{ ($r->start_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $r->start_at->format('g:i') }}
                                    -
                                    {{ ($r->end_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $r->end_at->format('g:i') }}
                                </div>

                                <div class="mt-2 text-sm text-gray-700">
                                    <span class="font-semibold">{{ $r->room?->name ?? '-' }}</span>
                                    @if($r->seat)
                                        <span class="text-gray-400 mx-2">|</span>
                                        <span class="font-semibold">{{ $r->seat->label }}</span>
                                    @endif
                                    <span class="text-gray-400 mx-2">|</span>
                                    <span class="break-all">{{ $repLabel }}</span>
                                </div>

                                @if($r->status === 'cancelled' && $r->cancel_reason)
                                    <div class="mt-2 text-sm text-gray-600 whitespace-pre-line">
                                        <span class="font-semibold">사유:</span> {{ $r->cancel_reason }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex-shrink-0">
                                <a href="{{ route('reservation.detail', $r->id) }}"
                                   class="inline-flex px-4 py-2.5 rounded-xl bg-gray-50 text-gray-700 text-sm font-semibold border border-gray-200 hover:bg-gray-100 whitespace-nowrap">
                                    상세
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection



