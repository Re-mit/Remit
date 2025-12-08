@extends('layouts.app')

@section('title', '내 예약')

@section('content')
<div class="min-h-screen bg-gray-50 pb-20">
    <!-- Mobile-Optimized Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-4 py-4">
            <h1 class="text-xl font-bold text-gray-900">내 예약</h1>
            <p class="text-sm text-gray-600 mt-1">예약 내역 관리</p>
        </div>
    </div>

    <div class="px-4 py-6">
        @if($reservations->isEmpty())
            <!-- Empty State (Mobile Optimized) -->
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">예약 내역 없음</h3>
                <p class="mt-2 text-sm text-gray-500">아직 예약한 내역이 없습니다</p>
                <div class="mt-6">
                    <a href="{{ route('reservation.index') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg font-medium hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-md">
                        예약하러 가기
                    </a>
                </div>
            </div>
        @else
            <!-- Reservations List (Mobile First) -->
            <div class="space-y-4">
                @foreach($reservations as $reservation)
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-4">
                            <!-- Status Badge -->
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $reservation->status === 'confirmed' ? '확정' : '취소됨' }}
                                </span>
                                @if($reservation->status === 'confirmed' && $reservation->start_at->isFuture())
                                    <form method="POST" action="{{ route('reservation.destroy', $reservation->id) }}" 
                                          onsubmit="return confirm('예약을 취소하시겠습니까?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-sm text-red-600 font-medium hover:text-red-700">
                                            취소
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Room Info -->
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ $reservation->room->name }}</h3>

                            <!-- Details List -->
                            <div class="space-y-3">
                                <!-- Date/Time -->
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $reservation->start_at->format('Y년 m월 d일') }}</p>
                                        <p class="text-sm text-gray-600">{{ $reservation->start_at->format('H:i') }} ~ {{ $reservation->end_at->format('H:i') }}</p>
                                    </div>
                                </div>

                                <!-- Key Code -->
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm text-gray-600">열쇠함 비밀번호</p>
                                        <p class="text-lg font-mono font-bold text-indigo-600">{{ $reservation->key_code }}</p>
                                    </div>
                                </div>

                                <!-- Participants (if any) -->
                                @if($reservation->users->count() > 0)
                                    <div class="flex items-start">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm text-gray-600">참여자</p>
                                            <p class="text-sm font-medium text-gray-900">{{ $reservation->users->pluck('name')->join(', ') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
