@extends('layouts.app')

@section('title', '알림')

@section('content')
<div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-4 py-4 flex items-center">
            <a href="{{ route('mypage.index') }}" class="mr-4">
                <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-xl font-bold text-gray-900">알림</h1>
        </div>
    </div>

    <div class="px-4 py-6">
        @if($notifications->isEmpty())
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">알림이 없습니다</h3>
                <p class="mt-2 text-sm text-gray-500">새로운 알림이 있으면 여기에 표시됩니다</p>
            </div>
        @else
            @foreach($notifications as $dateGroup => $dateNotifications)
                <div class="mb-8">
                    <h2 class="text-sm font-bold text-gray-900 mb-3">{{ $dateGroup }}</h2>
                    <div class="space-y-3">
                        @foreach($dateNotifications as $notification)
                            <div class="bg-white rounded-xl shadow-sm p-4">
                                <div class="flex items-start">
                                    <div class="relative flex-shrink-0 mr-3">
                                        @if($notification->type === 'reservation_reminder')
                                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        @else
                                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        @endif
                                        @if(!$notification->read_at)
                                            <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between gap-3 mb-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                                            <p class="text-[11px] text-gray-500 whitespace-nowrap">
                                                {{ $notification->created_at?->timezone('Asia/Seoul')->format('Y년 m월 d일 H:i') }}
                                            </p>
                                        </div>
                                        <p class="text-xs text-gray-600">{!! nl2br(e($notification->message)) !!}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
