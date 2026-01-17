@extends('layouts.app')

@section('title', '예약 확인')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col pb-24">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-4 py-4 flex items-center justify-between">
            <div class="w-[90px] flex items-center">
                <a href="{{ route('reservation.index') }}" class="mr-4">
                <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                </a>
            </div>
            <h1 class="text-xl font-bold text-gray-900">예약 확인</h1>
            <div class="w-[90px] flex items-center justify-end">
                <a href="{{ route('notification.index') }}" class="relative">
                    <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if(isset($unreadCount) && $unreadCount > 0)
                        <span class="absolute -top-0.5 -right-1 w-3 h-3 bg-[#FF8282] rounded-full"></span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="flex-1 flex flex-col items-center justify-center px-4 py-8">
        <!-- Success Icon -->
        <div class="mb-6">
            <svg width="42" height="42" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#clip0_69_1332)">
            <path d="M18 36C22.7739 36 27.3523 34.1036 30.7279 30.7279C34.1036 27.3523 36 22.7739 36 18C36 13.2261 34.1036 8.64773 30.7279 5.27208C27.3523 1.89642 22.7739 0 18 0C13.2261 0 8.64773 1.89642 5.27208 5.27208C1.89642 8.64773 0 13.2261 0 18C0 22.7739 1.89642 27.3523 5.27208 30.7279C8.64773 34.1036 13.2261 36 18 36ZM25.9453 14.6953L16.9453 23.6953C16.2844 24.3563 15.2156 24.3563 14.5617 23.6953L10.0617 19.1953C9.40078 18.5344 9.40078 17.4656 10.0617 16.8117C10.7227 16.1578 11.7914 16.1508 12.4453 16.8117L15.75 20.1164L23.5547 12.3047C24.2156 11.6438 25.2844 11.6438 25.9383 12.3047C26.5922 12.9656 26.5992 14.0344 25.9383 14.6883L25.9453 14.6953Z" fill="#3B82F6"/>
            </g>
            <defs>
            <clipPath id="clip0_69_1332">
            <path d="M0 0H36V36H0V0Z" fill="white"/>
            </clipPath>
            </defs>
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-2">예약이 완료되었어요</h2>
        <p class="text-sm text-gray-600 mb-8">총 {{ $reservations->count() }}건 ({{ $date }})</p>

        <!-- Reservation List -->
        <div class="w-full max-w-md space-y-3 mb-8">
            @foreach($reservations as $reservation)
                <div class="w-full bg-white rounded-2xl shadow-sm p-5 border">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">예약 시간</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ ($reservation->start_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $reservation->start_at->format('g:i') }}
                                -
                                {{ ($reservation->end_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $reservation->end_at->format('g:i') }}
                            </p>
                        </div>
                        <span class="nowrap text-nowrap whitespace-nowrap inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold border border-blue-500 text-blue-500">
                            예약완료
                        </span>
                    </div>
                    <div class="mt-4 flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p class="text-lg font-semibold text-gray-900">{{ $reservation->room->name }}</p>
                    </div>
                    <div class="mt-3">
                        <p class="text-sm text-gray-600 mb-1">좌석</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $reservation->seat?->label ?? '미지정' }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <a href="{{ route('reservation.my') }}"
           class="w-full max-w-md py-4 bg-white border-2 border-gray-300 rounded-2xl text-center text-lg font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
            닫기
        </a>
    </div>
</div>
@endsection


