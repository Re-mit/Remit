@extends('layouts.app')

@section('title', '열쇠함 비밀번호')

@section('content')
<div class="min-h-screen bg-gray-50 pb-20" x-data="keycodeApp()">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-4 py-4 flex items-center">
            <a href="{{ route('mypage.index') }}" class="mr-4">
                <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-xl font-bold text-gray-900">열쇠함 비밀번호</h1>
        </div>
    </div>

    <div class="px-4 py-6">
        <!-- 예약 내역 -->
        <div class="mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">예약 내역</h2>
            @if($reservations->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                    <p class="text-gray-600">예약 내역이 없습니다</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($reservations as $reservation)
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <button 
                                @click="showKeycode({{ $reservation->id }}, '{{ $reservation->key_code }}', {{ $reservation->is_keycode_disclosed ? 'true' : 'false' }}, {{ json_encode($reservation->keycode_disclosure_time_formatted) }})"
                                class="w-full p-4 text-left">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center flex-1">
                                        <div class="flex-shrink-0 mr-4">
                                            @if($reservation->start_at->isPast())
                                                <!-- 진행 중 또는 완료 -->
                                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                            @else
                                                <!-- 예약됨 -->
                                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $reservation->start_at->format('Y년 m월 d일') }}</p>
                                            <p class="text-sm text-gray-600">
                                                {{ ($reservation->start_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $reservation->start_at->format('g:i') }} - {{ ($reservation->end_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $reservation->end_at->format('g:i') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        @if($reservation->start_at->isPast())
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                진행 중
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                예약됨
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 열쇠함 이용 안내 -->
        <div class="bg-blue-50 rounded-xl p-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">열쇠함 이용 안내</h3>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li>• 열쇠함 비밀번호는 예약 시간 10분 전부터 확인 가능합니다.</li>
                        <li>• 열쇠함은 가천관 6층 622호 문에 위치해 있습니다.</li>
                        <li>• 사용 후 반드시 원래 자리에 열쇠를 반납해 주세요.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Keycode Modal -->
    <div x-show="showModal" 
         x-cloak
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
            <h3 class="text-xl font-bold text-gray-900 mb-4">열쇠함 비밀번호</h3>
            
            <div class="mb-4">
                <span x-show="isDisclosed" class="text-sm font-semibold text-green-600">공개됨</span>
                <span x-show="!isDisclosed" class="text-sm font-semibold text-gray-500">미공개</span>
            </div>

            <div class="flex items-center justify-center mb-4">
                <template x-if="isDisclosed">
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 text-green-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <p class="text-2xl font-bold text-gray-900" x-text="keycode"></p>
                    </div>
                </template>
                <template x-if="!isDisclosed">
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <p class="text-2xl font-bold text-gray-400 mb-2">......</p>
                        <p class="text-sm text-gray-600" x-text="disclosureTime"></p>
                    </div>
                </template>
            </div>

            <button @click="showModal = false"
                    class="w-full py-3 bg-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                닫기
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function keycodeApp() {
    return {
        showModal: false,
        keycode: '',
        startAt: null,
        isDisclosed: false,
        disclosureTime: '',

        showKeycode(reservationId, code, isDisclosed, disclosureTimeData) {
            this.keycode = code;
            this.isDisclosed = isDisclosed;
            
            if (!this.isDisclosed && disclosureTimeData) {
                // 서버에서 전달받은 한국 시간 데이터 그대로 사용
                const hour = parseInt(disclosureTimeData.hour);
                const minute = parseInt(disclosureTimeData.minute);
                const ampm = hour >= 12 ? '오후' : '오전';
                const displayHour = hour % 12 || 12;
                
                this.disclosureTime = `${disclosureTimeData.month}월 ${disclosureTimeData.day}일 ${ampm} ${displayHour}:${minute.toString().padStart(2, '0')}에 공개`;
            } else {
                this.disclosureTime = '';
            }
            
            this.showModal = true;
        }
    }
}
</script>
@endpush
@endsection

