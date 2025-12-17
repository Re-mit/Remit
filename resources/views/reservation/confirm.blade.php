@extends('layouts.app')

@section('title', '예약 확인')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-4 py-4 flex items-center">
            <a href="{{ route('reservation.index') }}" class="mr-4">
                <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-xl font-bold text-gray-900">예약 확인</h1>
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
        <!-- Success Message -->
        <h2 class="text-2xl font-bold text-gray-900 mb-8">예약이 완료되었어요</h2>

        <!-- Reservation Details Card -->
        <div class="w-full max-w-md bg-white rounded-2xl shadow-sm p-6 mb-6">
            <!-- Date and Status -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-600 mb-1">예약 날짜</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $reservation->start_at->format('Y년 m월 d일') }}</p>
                </div>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold border border-blue-500 text-blue-500">
                    예약완료
                </span>
            </div>

            <!-- Time -->
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-1">예약 시간</p>
                <p class="text-lg font-semibold text-gray-900">
                    {{ ($reservation->start_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $reservation->start_at->format('g:i') }} - {{ ($reservation->end_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $reservation->end_at->format('g:i') }}
                </p>
            </div>

            <!-- Location -->
            <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-lg font-semibold text-gray-900">{{ $reservation->room->name }}</p>
            </div>
        </div>

        <!-- Information Card -->
        <div class="w-full max-w-md bg-blue-50 rounded-2xl p-6 mb-8">
            <div class="flex flex-col items-start">
                <div class="flex items-center gap-2 mb-3">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_69_1370)">
                    <path d="M9 18C11.3869 18 13.6761 17.0518 15.364 15.364C17.0518 13.6761 18 11.3869 18 9C18 6.61305 17.0518 4.32387 15.364 2.63604C13.6761 0.948212 11.3869 0 9 0C6.61305 0 4.32387 0.948212 2.63604 2.63604C0.948212 4.32387 0 6.61305 0 9C0 11.3869 0.948212 13.6761 2.63604 15.364C4.32387 17.0518 6.61305 18 9 18ZM7.59375 11.8125H8.4375V9.5625H7.59375C7.12617 9.5625 6.75 9.18633 6.75 8.71875C6.75 8.25117 7.12617 7.875 7.59375 7.875H9.28125C9.74883 7.875 10.125 8.25117 10.125 8.71875V11.8125H10.4062C10.8738 11.8125 11.25 12.1887 11.25 12.6562C11.25 13.1238 10.8738 13.5 10.4062 13.5H7.59375C7.12617 13.5 6.75 13.1238 6.75 12.6562C6.75 12.1887 7.12617 11.8125 7.59375 11.8125ZM9 4.5C9.29837 4.5 9.58452 4.61853 9.7955 4.8295C10.0065 5.04048 10.125 5.32663 10.125 5.625C10.125 5.92337 10.0065 6.20952 9.7955 6.4205C9.58452 6.63147 9.29837 6.75 9 6.75C8.70163 6.75 8.41548 6.63147 8.2045 6.4205C7.99353 6.20952 7.875 5.92337 7.875 5.625C7.875 5.32663 7.99353 5.04048 8.2045 4.8295C8.41548 4.61853 8.70163 4.5 9 4.5Z" fill="#3B82F6"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_69_1370">
                    <path d="M0 0H18V18H0V0Z" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900">예약 안내</h3>
                </div>
                <div class="flex-1">
                        <ul class="text-sm text-gray-700">• 예약 취소는 1시간 전까지 가능합니다.</li>
                        <ul class="text-sm text-gray-700">• 열쇠함 비밀번호는 마이페이지에서 확인 가능합니다.</li>
                </div>
            </div>
        </div>

        <!-- Close Button -->
        <a href="{{ route('reservation.index') }}" 
           class="w-full max-w-md py-4 bg-white border-2 border-gray-300 rounded-2xl text-center text-lg font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
            닫기
        </a>
    </div>
</div>
@endsection

