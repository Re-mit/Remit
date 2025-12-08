@extends('layouts.app')

@section('title', '알림')

@section('content')
<div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-4 py-4">
            <h1 class="text-xl font-bold text-gray-900">알림</h1>
        </div>
    </div>

    <div class="px-4 py-6">
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
    </div>
</div>
@endsection
