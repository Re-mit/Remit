@extends('layouts.app')

@section('title', '예약 조회')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col pb-24">
    <div class="bg-white border-b border-gray-200">
        <div class="px-4 py-4 flex items-center justify-between">
            <div class="w-[90px] flex items-center">
                <a href="{{ route('reservation.my') }}" class="mr-4">
                <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                </a>
            </div>
            <h1 class="text-xl font-bold text-gray-900">예약 조회</h1>
            <div class="w-[90px]"></div>
        </div>
    </div>

    <div class="flex-1 flex flex-col items-center justify-center px-4 py-8">
        <div class="mb-6">
            <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#clip0_60_1224)">
            <path d="M18 36C22.7739 36 27.3523 34.1036 30.7279 30.7279C34.1036 27.3523 36 22.7739 36 18C36 13.2261 34.1036 8.64773 30.7279 5.27208C27.3523 1.89642 22.7739 0 18 0C13.2261 0 8.64773 1.89642 5.27208 5.27208C1.89642 8.64773 0 13.2261 0 18C0 22.7739 1.89642 27.3523 5.27208 30.7279C8.64773 34.1036 13.2261 36 18 36ZM25.9453 14.6953L16.9453 23.6953C16.2844 24.3563 15.2156 24.3563 14.5617 23.6953L10.0617 19.1953C9.40078 18.5344 9.40078 17.4656 10.0617 16.8117C10.7227 16.1578 11.7914 16.1508 12.4453 16.8117L15.75 20.1164L23.5547 12.3047C24.2156 11.6438 25.2844 11.6438 25.9383 12.3047C26.5922 12.9656 26.5992 14.0344 25.9383 14.6883L25.9453 14.6953Z" fill="#9CA3AF"/>
            </g>
            <defs>
            <clipPath id="clip0_60_1224">
            <path d="M0 0H36V36H0V0Z" fill="white"/>
            </clipPath>
            </defs>
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-8">예약 내역 정보</h2>

        <div class="w-full max-w-md bg-white rounded-2xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-600 mb-1">예약 날짜</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $reservation->start_at->format('Y년 m월 d일') }}</p>
                </div>
                <span class="nowrap text-nowrap whitespace-nowrap inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold {{ $reservation->status === 'confirmed' ? 'border border-blue-500 text-blue-500' : 'border border-gray-400 text-gray-500' }}">
                    {{ $reservation->status === 'confirmed' ? '예약완료' : '취소됨' }}
                </span>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-1">예약 시간</p>
                <p class="text-lg font-semibold text-gray-900">
                    {{ ($reservation->start_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $reservation->start_at->format('g:i') }} - {{ ($reservation->end_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $reservation->end_at->format('g:i') }}
                </p>
            </div>

            <div class="flex items-center mb-6">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-lg font-semibold text-gray-900">{{ optional($reservation->room)->name }}</p>
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-1">좌석</p>
                {{-- PHP 7.4 대응: optional() 사용 --}}
                <p class="text-lg font-semibold text-gray-900">{{ optional($reservation->seat)->label ?? '미지정' }}</p>
            </div>

            {{-- PHP 7.4 대응: optional() 사용 및 시간대 체크 --}}
            @if($reservation->status === 'confirmed' && optional($reservation->start_at)->timezone('Asia/Seoul')->isFuture())
                <form method="POST" action="{{ route('reservation.destroy', $reservation->id) }}" 
                      onsubmit="return confirmCancel()" 
                      x-data="{ showModal: false }"
                      x-init="$watch('showModal', value => { if (value) document.body.style.overflow = 'hidden'; else document.body.style.overflow = ''; })">
                    @csrf
                    @method('DELETE')
                    <button type="button" 
                            @click="showModal = true"
                            class="w-full py-4 bg-red-500 text-white rounded-2xl font-semibold text-lg hover:bg-red-600 transition-colors shadow-sm">
                        예약 취소
                    </button>

                    <div x-show="showModal"
                         x-cloak
                         style="display: none;"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
                         @click.self="showModal = false">
                        <div class="bg-white rounded-2xl p-6 max-w-sm w-full"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">예약 취소</h3>
                            <p class="text-gray-700 mb-2">예약을 취소하시겠습니까?</p>
                            <p class="text-sm text-gray-500 mb-6">이 작업은 되돌릴 수 없습니다.</p>
                            <div class="flex gap-3">
                                <button type="button" 
                                        @click="showModal = false"
                                        class="flex-1 py-3 bg-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                                    취소
                                </button>
                                <button type="submit" 
                                        class="flex-1 py-3 bg-red-500 text-white rounded-xl font-semibold hover:bg-red-600 transition-colors">
                                    삭제
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>

        <div class="w-full max-w-md bg-blue-50 rounded-2xl p-6 mb-8">
            <div class="flex flex-col items-start">
                <div class="flex items-center gap-2 mb-3">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18C11.3869 18 13.6761 17.0518 15.364 15.364C17.0518 13.6761 18 11.3869 18 9C18 6.61305 17.0518 4.32387 15.364 2.63604C13.6761 0.948212 11.3869 0 9 0C6.61305 0 4.32387 0.948212 2.63604 2.63604C0.948212 4.32387 0 6.61305 0 9C0 11.3869 0.948212 13.6761 2.63604 15.364C4.32387 17.0518 6.61305 18 9 18Z" fill="#3B82F6"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900">예약 안내</h3>
                </div>
                <ul class="text-sm text-gray-700 list-disc pl-5 space-y-1">
                    <li>예약 취소는 1시간 전까지 가능합니다.</li>
                    <li>열쇠함 비밀번호는 마이페이지에서 확인 가능합니다.</li>
                </ul>
            </div>
        </div>

        <a href="{{ route('reservation.index') }}" 
           class="w-full max-w-md py-4 bg-white border-2 border-gray-300 rounded-2xl text-center text-lg font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
            닫기
        </a>
    </div>
</div>

<script>
function confirmCancel() {
    return true; // Alpine.js 모달에서 이미 확인을 거쳤으므로 true 반환
}
</script>
@endsection