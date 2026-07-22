@extends('layouts.app')

@section('title', '내 예약')

@section('content')
<div class="min-h-screen bg-gray-50 pb-20" x-data="reservationCalendarApp()">
    <!-- Header -->
    <div class="bg-white sticky top-0 z-20 border-b shadow-sm">
        <div class="flex items-center justify-between px-5 py-4">
            <div class="w-[90px]"></div>
            <h1 class="text-xl font-bold text-gray-900">예약 내역</h1>
            <div class="w-[90px] flex items-center justify-end gap-2">
                @php
                    $user = \Illuminate\Support\Facades\Auth::user();
                    $envAdminEmail = config('admin.email');
                    $isAdmin = $user
                        && (
                            ($user->role ?? null) === 'admin'
                            || ($envAdminEmail && \App\Models\AllowedEmail::normalize($envAdminEmail) === \App\Models\AllowedEmail::normalize($user->email))
                        );
                @endphp
                @if($isAdmin)
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-gray-700">관리자</a>
                @endif
                <a href="{{ route('notification.index') }}" class="relative">
                <img src="{{ asset('images/notification-bell.png') }}" alt="알림" width="26" height="26" class="w-[26px] h-[26px]">
                    @if(isset($unreadCount) && $unreadCount > 0)
                        <span class="absolute -top-0.5 -right-1 w-3 h-3 bg-[#FF8282] rounded-full"></span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="bg-white px-4 py-4 mb-4">
        <!-- Month Navigation -->
        <div class="flex items-center justify-between mb-4">
            <button @click="prevMonth()" class="p-2 rounded-full hover:bg-gray-100">
                <x-icons.chevron-left class="w-5 h-5 text-gray-600" />
            </button>
            <span class="text-lg font-bold text-gray-900" x-text="currentMonthYear"></span>
            <button @click="nextMonth()" class="p-2 rounded-full hover:bg-gray-100">
                <x-icons.chevron-right class="w-5 h-5 text-gray-600" />
            </button>
        </div>
        
        <!-- Day Headers -->
        <div class="grid grid-cols-7 gap-1 mb-2">
            <template x-for="day in ['일', '월', '화', '수', '목', '금', '토']" :key="day">
                <div class="text-center text-xs font-medium py-2" 
                     :class="day === '일' ? 'text-red-500' : day === '토' ? 'text-blue-500' : 'text-gray-500'"
                     x-text="day"></div>
            </template>
        </div>
        
        <!-- Calendar Grid -->
        <div class="grid grid-cols-7 gap-1">
            <template x-for="(day, index) in calendarDays" :key="index">
                <button 
                    @click="day.date && selectDate(day.date)"
                    class="aspect-square flex flex-col items-center justify-center rounded-full text-sm transition-all relative"
                    :class="{
                        'bg-blue-500 text-white font-bold': day.date && isSelected(day.date),
                        'text-gray-300 cursor-default': !day.date,
                        'hover:bg-gray-100': day.date && !isSelected(day.date),
                        'text-red-500': day.date && !isSelected(day.date) && isSunday(day.date),
                        'text-blue-500': day.date && !isSelected(day.date) && isSaturday(day.date),
                        'text-gray-900': day.date && !isSelected(day.date) && !isSunday(day.date) && !isSaturday(day.date),
                    }"
                    :disabled="!day.date"
                >
                    <span x-text="day.dayNum || ''"></span>
                    <!-- Reservation indicator dot -->
                    <!-- 과거 날짜: 검은색 점, 미래/오늘 날짜: 파란색 점 -->
                    <span x-show="day.date && hasReservation(day.date) && !isSelected(day.date) && isPastDate(day.date)" 
                          class="absolute bottom-1 w-1 h-1 bg-gray-900 rounded-full"></span>
                    <span x-show="day.date && hasReservation(day.date) && !isSelected(day.date) && !isPastDate(day.date)" 
                          class="absolute bottom-1 w-1 h-1 bg-blue-500 rounded-full"></span>
                    <!-- 선택된 날짜는 흰색 점 -->
                    <span x-show="day.date && hasReservation(day.date) && isSelected(day.date)" 
                          class="absolute bottom-1 w-1 h-1 bg-white rounded-full"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- Reservations List -->
    <div class="px-4 py-6">
        <template x-if="filteredReservations.length === 0">
            <div class="bg-gray-100 rounded-xl shadow-sm p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-200 rounded-full mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <p class="text-sm text-gray-600">예약 내역이 없어요</p>
            </div>
        </template>

        <template x-if="filteredReservations.length > 0">
            <div class="space-y-4">
                
            <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-lg font-semibold text-gray-900" x-text="reservation.room_name"></span>
                <p class="font-semibold">가천관 622호</p>
            </div>
                <template x-for="reservation in filteredReservations" :key="reservation.id">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <a :href="`/reservation/${reservation.id}/detail`" class="block p-4">
                            <!-- Status -->
                            <div class="flex items-center justify-start">
                                <div class="h-6 w-1 bg-gray-700 rounded-full mr-2"></div>
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-600 mr-3" x-text="reservation.time"></span>
                                    <span class="text-sm text-gray-500 mr-3" x-show="reservation.seat_label" x-text="`좌석 ${reservation.seat_label}`"></span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" 
                                          :class="reservation.status === 'confirmed' ? 'border border-blue-500 text-blue-500' : 'border border-gray-400 text-gray-500'"
                                          x-text="reservation.status_text"></span>
                                </div>
                            </div>
                        </a>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
function reservationCalendarApp() {
    // 예약 데이터 (서버에서 전달)
    const allReservations = @json($reservationsData);

    // 초기 선택 날짜: 예약이 있는 날짜 중 가장 최근 날짜, 없으면 오늘
    const getInitialDate = () => {
        if (allReservations.length === 0) {
            return new Date();
        }
        const reservationDates = allReservations.map(r => new Date(r.date + 'T00:00:00'));
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // 오늘 날짜에 예약이 있으면 오늘 선택
        const todayReservation = reservationDates.find(d => d.getTime() === today.getTime());
        if (todayReservation) {
            return todayReservation;
        }
        
        // 과거 예약 중 가장 최근 날짜 선택
        const pastReservations = reservationDates.filter(d => d <= today).sort((a, b) => b - a);
        if (pastReservations.length > 0) {
            return pastReservations[0];
        }
        
        // 미래 예약 중 가장 가까운 날짜 선택
        const futureReservations = reservationDates.filter(d => d > today).sort((a, b) => a - b);
        if (futureReservations.length > 0) {
            return futureReservations[0];
        }
        
        return new Date();
    };

    const initialDate = getInitialDate();
    const initialMonth = initialDate.getMonth();
    const initialYear = initialDate.getFullYear();

    return {
        currentMonth: initialMonth,
        currentYear: initialYear,
        selectedDate: initialDate,
        reservations: allReservations,
        
        get currentMonthYear() {
            return `${this.currentYear}년 ${this.currentMonth + 1}월`;
        },
        
        get calendarDays() {
            const days = [];
            const firstDay = new Date(this.currentYear, this.currentMonth, 1);
            const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // 첫째 주 빈칸
            for (let i = 0; i < firstDay.getDay(); i++) {
                days.push({ date: null, dayNum: null });
            }
            
            // 날짜들
            for (let d = 1; d <= lastDay.getDate(); d++) {
                const date = new Date(this.currentYear, this.currentMonth, d);
                days.push({ 
                    date: date, 
                    dayNum: d,
                });
            }
            
            return days;
        },

        isSunday(date) {
            return date.getDay() === 0;
        },

        isSaturday(date) {
            return date.getDay() === 6;
        },
        
        get filteredReservations() {
            const selectedDateStr = this.formatDate(this.selectedDate);
            return this.reservations.filter(r => r.date === selectedDateStr);
        },
        
        prevMonth() {
            if (this.currentMonth === 0) {
                this.currentMonth = 11;
                this.currentYear--;
            } else {
                this.currentMonth--;
            }
        },
        
        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentMonth = 0;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
        },
        
        selectDate(date) {
            if (date) {
                this.selectedDate = date;
            }
        },
        
        isSelected(date) {
            return this.formatDate(date) === this.formatDate(this.selectedDate);
        },
        
        hasReservation(date) {
            const dateStr = this.formatDate(date);
            return this.reservations.some(r => r.date === dateStr);
        },
        
        isPastDate(date) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const compareDate = new Date(date);
            compareDate.setHours(0, 0, 0, 0);
            return compareDate < today;
        },
        
        formatDate(date) {
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    }
}
</script>
@endpush
@endsection
