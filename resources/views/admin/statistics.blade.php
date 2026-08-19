@extends('layouts.app')

@section('title', '사용 통계')
@section('admin_title', '사용 통계')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24">
    @include('admin._nav')

    <div class="p-5 space-y-6">
        <section class="bg-white rounded-2xl shadow-sm border p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">회원·예약 현황</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $startAt->format('Y.m.d') }} – {{ $endAt->format('Y.m.d') }}
                    </p>
                </div>
                <span class="text-xs text-gray-500 bg-gray-100 rounded-full px-3 py-1 whitespace-nowrap">예약 시작일 기준</span>
            </div>

            <div class="grid gap-2 mt-4" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                @foreach([7, 30] as $days)
                    <a href="{{ route('admin.statistics', ['period' => $days]) }}"
                       class="text-center rounded-xl border px-3 py-2 text-sm font-semibold {{ $period === $days ? 'bg-blue-500 border-blue-500 text-white' : 'bg-white border-gray-200 text-gray-700' }}">
                        {{ $days }}일
                    </a>
                @endforeach
            </div>
        </section>

        <section class="grid gap-3" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
            <div class="bg-white rounded-2xl shadow-sm border p-4">
                <div class="text-xs text-gray-500">전체 일반 회원</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['totalMembers']) }}명</div>
                <div class="text-xs text-blue-600 mt-2">기간 신규 +{{ number_format($stats['newMembers']) }}명</div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border p-4">
                <div class="text-xs text-gray-500">기간 예약 이용자</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['activeReservationUsers']) }}명</div>
                <div class="text-xs text-blue-600 mt-2">전체 회원의 {{ $stats['reservationUserRate'] }}%</div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border p-4">
                <div class="text-xs text-gray-500">전체 예약</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['totalReservations']) }}건</div>
                <div class="text-xs text-green-600 mt-2">유효 {{ number_format($stats['confirmedReservations']) }}건</div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border p-4">
                <div class="text-xs text-gray-500">예약 취소율</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['cancellationRate'] }}%</div>
                <div class="text-xs text-red-600 mt-2">취소 {{ number_format($stats['cancelledReservations']) }}건</div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border p-4">
                <div class="text-xs text-gray-500">재이용자</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['repeatUsers']) }}명</div>
                <div class="text-xs text-indigo-600 mt-2">예약 이용자의 {{ $stats['repeatUserRate'] }}%</div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border p-4">
                <div class="text-xs text-gray-500">예약당 참여자</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['averageParticipants'] }}명</div>
                <div class="text-xs text-gray-500 mt-2">등록 회원 기준 평균</div>
            </div>
        </section>

        <section class="bg-white rounded-2xl shadow-sm border p-5">
            <div class="flex items-center justify-between gap-3 mb-5">
                <div>
                    <h2 class="font-bold text-gray-900">예약 추이</h2>
                    <p class="text-xs text-gray-500 mt-1">{{ $period === 7 ? '일별' : '주별' }} 유효·취소 예약</p>
                </div>
                <div class="flex items-center gap-3 text-xs text-gray-500">
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span>유효</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span>취소</span>
                </div>
            </div>

            @php $trendMax = max(1, collect($trend)->max('total')); @endphp
            <div class="space-y-3">
                @foreach($trend as $item)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-500">{{ $item['label'] }}</span>
                            <span class="font-semibold text-gray-700">{{ number_format($item['total']) }}건</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100 overflow-hidden flex">
                            @if($item['confirmed'] > 0)
                                <div class="h-full bg-blue-500" style="width: {{ ($item['confirmed'] / $trendMax) * 100 }}%"></div>
                            @endif
                            @if($item['cancelled'] > 0)
                                <div class="h-full bg-red-500" style="width: {{ ($item['cancelled'] / $trendMax) * 100 }}%"></div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="bg-white rounded-2xl shadow-sm border p-5">
            <h2 class="font-bold text-gray-900">예약이 많은 시간대</h2>
            <p class="text-xs text-gray-500 mt-1 mb-5">유효 예약 시작 시간 기준 상위 5개</p>

            @if(empty($peakHours))
                <div class="rounded-xl bg-gray-50 p-5 text-center text-sm text-gray-500">선택 기간의 유효 예약이 없습니다.</div>
            @else
                @php $peakHourMax = max(1, collect($peakHours)->max('count')); @endphp
                <div class="space-y-4">
                    @foreach($peakHours as $item)
                        <div>
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="text-gray-700">{{ $item['label'] }}</span>
                                <span class="font-semibold text-gray-900">{{ number_format($item['count']) }}건</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full bg-blue-500" style="width: {{ ($item['count'] / $peakHourMax) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="bg-white rounded-2xl shadow-sm border p-5">
            <h2 class="font-bold text-gray-900">공간별 예약</h2>
            <p class="text-xs text-gray-500 mt-1 mb-5">유효 예약 건수 기준 상위 5개</p>

            @if(empty($topRooms))
                <div class="rounded-xl bg-gray-50 p-5 text-center text-sm text-gray-500">선택 기간의 유효 예약이 없습니다.</div>
            @else
                @php $roomMax = max(1, collect($topRooms)->max('reservations')); @endphp
                <div class="space-y-4">
                    @foreach($topRooms as $room)
                        <div>
                            <div class="flex items-center justify-between gap-3 text-sm mb-2">
                                <span class="font-medium text-gray-800 min-w-0 break-words">{{ $room['name'] }}</span>
                                <span class="text-gray-600 whitespace-nowrap">{{ number_format($room['reservations']) }}건 · {{ number_format($room['participants']) }}명</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full bg-green-500" style="width: {{ ($room['reservations'] / $roomMax) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="bg-white rounded-2xl shadow-sm border p-5">
            <h2 class="font-bold text-gray-900">취소 구분</h2>
            <div class="grid gap-3 mt-4" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                <div class="rounded-xl bg-red-50 border border-red-200 p-4">
                    <div class="text-xs text-red-600">사용자 취소</div>
                    <div class="text-xl font-bold text-red-700 mt-1">{{ number_format($stats['userCancelled']) }}건</div>
                </div>
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-4">
                    <div class="text-xs text-gray-500">관리자 취소</div>
                    <div class="text-xl font-bold text-gray-800 mt-1">{{ number_format($stats['adminCancelled']) }}건</div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-blue-50 border border-blue-200 p-5 text-sm text-blue-700">
            <div class="font-semibold">통계 기준 안내</div>
            <ul class="list-disc pl-5 mt-2 space-y-1 text-xs leading-relaxed">
                <li>예약 이용자는 유효 예약에 연결된 등록 회원을 중복 제거해 계산합니다.</li>
                <li>재이용자는 선택 기간에 유효 예약이 2건 이상인 회원입니다.</li>
                <li>실제 입실 여부가 아니라 예약 데이터를 기준으로 한 통계입니다.</li>
                <li>예약 원본 보관 정책에 맞춰 최근 30일까지 조회할 수 있습니다.</li>
            </ul>
        </section>
    </div>
</div>
@endsection
