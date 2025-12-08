@extends('layouts.app')

@section('title', '예약')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24" x-data="reservationApp()">
    <!-- Header -->
    <div class="bg-white sticky top-0 z-20">
        <div class="flex items-center justify-between px-5 py-4">
            <h1 class="text-xl font-bold text-gray-900">예약</h1>
            <a href="{{ route('notification.index') }}" class="relative">
                <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <!-- Notification badge -->
                <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </a>
        </div>
    </div>

    <!-- Calendar -->
    <div class="bg-white border-b border-gray-100 px-4 py-4">
        <!-- Month Navigation -->
        <div class="flex items-center justify-between mb-4">
            <button @click="prevMonth()" class="p-2 rounded-full hover:bg-gray-100">
                <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <span class="text-lg font-bold text-gray-900" x-text="currentMonthYear"></span>
            <button @click="nextMonth()" class="p-2 rounded-full hover:bg-gray-100">
                <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
        
        <!-- Day Headers -->
        <div class="grid grid-cols-7 gap-1 mb-2">
            <template x-for="day in ['일', '월', '화', '수', '목', '금', '토']" :key="day">
                <div class="text-center text-xs font-medium text-gray-500 py-2" x-text="day"></div>
            </template>
        </div>
        
        <!-- Calendar Grid -->
        <div class="grid grid-cols-7 gap-1">
            <template x-for="(day, index) in calendarDays" :key="index">
                <button 
                    @click="day.date && selectDate(day.date)"
                    class="aspect-square flex items-center justify-center rounded-full text-sm transition-all relative"
                    :class="{
                        'bg-indigo-500 text-white font-bold': day.date && isSelected(day.date),
                        'text-gray-300 cursor-default': !day.date || !isSelectable(day.date),
                        'text-gray-900 hover:bg-gray-100': day.date && isSelectable(day.date) && !isSelected(day.date),
                        'font-semibold': day.isToday
                    }"
                    :disabled="!day.date || !isSelectable(day.date)"
                >
                    <span x-text="day.dayNum || ''"></span>
                    <!-- Today indicator -->
                    <span x-show="day.isToday && !isSelected(day.date)" class="absolute bottom-1 w-1 h-1 bg-indigo-500 rounded-full"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- Time Slots -->
    <div class="px-4 py-4">
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">시간 선택</h2>
                <p class="text-xs text-gray-500 mt-1">최대 4시간까지 연속 선택 가능</p>
            </div>
            
            <div class="p-4 grid grid-cols-4 gap-2">
                <template x-for="slot in timeSlots" :key="slot.time">
                    <button 
                        @click="toggleTimeSlot(slot)"
                        class="py-3 px-2 rounded-xl text-sm font-medium transition-all border-2"
                        :class="{
                            'bg-gray-100 text-gray-400 border-transparent cursor-not-allowed': slot.isReserved,
                            'bg-indigo-500 text-white border-indigo-500': isTimeSelected(slot.time) && !slot.isReserved,
                            'bg-white text-gray-700 border-gray-200 hover:border-indigo-300': !isTimeSelected(slot.time) && !slot.isReserved
                        }"
                        :disabled="slot.isReserved"
                    >
                        <span x-text="slot.time"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- Selected Time Summary -->
    <div class="px-4" x-show="selectedTimes.length > 0" x-transition>
        <div class="bg-indigo-50 rounded-2xl p-4 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-indigo-600 font-medium">선택한 시간</p>
                    <p class="text-lg font-bold text-indigo-900" x-text="getSelectedTimeRange()"></p>
                </div>
                <button @click="clearSelection()" class="text-indigo-500 text-sm font-medium">초기화</button>
            </div>
        </div>
    </div>

    <!-- Reserve Button -->
    <div class="fixed bottom-20 left-0 right-0 px-4 pb-4 bg-gradient-to-t from-gray-50 via-gray-50 to-transparent pt-4">
        <form method="POST" action="{{ route('reservation.store') }}" x-ref="reservationForm">
            @csrf
            <input type="hidden" name="start_at" :value="getStartDateTime()">
            <input type="hidden" name="end_at" :value="getEndDateTime()">
            
            <button 
                type="submit"
                class="w-full py-4 rounded-2xl font-semibold text-lg transition-all shadow-lg"
                :class="{
                    'bg-indigo-500 text-white hover:bg-indigo-600': selectedTimes.length > 0,
                    'bg-gray-200 text-gray-400 cursor-not-allowed': selectedTimes.length === 0
                }"
                :disabled="selectedTimes.length === 0"
            >
                예약하기
            </button>
        </form>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="fixed top-20 left-4 right-4 bg-green-500 text-white px-4 py-3 rounded-xl shadow-lg z-50" 
             x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 4000)"
             x-transition>
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="fixed top-20 left-4 right-4 bg-red-500 text-white px-4 py-3 rounded-xl shadow-lg z-50"
             x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 4000)"
             x-transition>
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                @foreach($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function reservationApp() {
    // 기존 예약 데이터 (서버에서 전달)
    const existingReservations = @json($reservations->map(function($r) {
        return [
            'date' => $r->start_at->format('Y-m-d'),
            'start' => $r->start_at->format('H:i'),
            'end' => $r->end_at->format('H:i')
        ];
    }));

    return {
        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear(),
        selectedDate: new Date(),
        selectedTimes: [],
        
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
                const isToday = date.getTime() === today.getTime();
                days.push({ 
                    date: date, 
                    dayNum: d,
                    isToday: isToday
                });
            }
            
            return days;
        },
        
        get timeSlots() {
            const slots = [];
            const selectedDateStr = this.formatDate(this.selectedDate);
            
            for (let hour = 9; hour <= 21; hour++) {
                const time = `${hour.toString().padStart(2, '0')}:00`;
                const isReserved = this.isTimeReserved(selectedDateStr, time);
                slots.push({ time, isReserved });
            }
            return slots;
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
            if (this.isSelectable(date)) {
                this.selectedDate = date;
                this.selectedTimes = [];
            }
        },
        
        isSelected(date) {
            return this.formatDate(date) === this.formatDate(this.selectedDate);
        },
        
        isSelectable(date) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const oneWeek = new Date(today);
            oneWeek.setDate(today.getDate() + 7);
            return date >= today && date <= oneWeek;
        },
        
        isTimeReserved(dateStr, time) {
            const hour = parseInt(time.split(':')[0]);
            return existingReservations.some(r => {
                if (r.date !== dateStr) return false;
                const startHour = parseInt(r.start.split(':')[0]);
                const endHour = parseInt(r.end.split(':')[0]);
                return hour >= startHour && hour < endHour;
            });
        },
        
        toggleTimeSlot(slot) {
            if (slot.isReserved) return;
            
            const index = this.selectedTimes.indexOf(slot.time);
            if (index > -1) {
                this.selectedTimes.splice(index, 1);
            } else {
                if (this.selectedTimes.length >= 4) {
                    return;
                }
                this.selectedTimes.push(slot.time);
            }
            this.selectedTimes.sort();
        },
        
        isTimeSelected(time) {
            return this.selectedTimes.includes(time);
        },
        
        getSelectedTimeRange() {
            if (this.selectedTimes.length === 0) return '';
            const sorted = [...this.selectedTimes].sort();
            const start = sorted[0];
            const lastHour = parseInt(sorted[sorted.length - 1].split(':')[0]);
            const end = `${(lastHour + 1).toString().padStart(2, '0')}:00`;
            return `${start} ~ ${end}`;
        },
        
        clearSelection() {
            this.selectedTimes = [];
        },
        
        formatDate(date) {
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        
        getStartDateTime() {
            if (this.selectedTimes.length === 0) return '';
            const sorted = [...this.selectedTimes].sort();
            return `${this.formatDate(this.selectedDate)}T${sorted[0]}`;
        },
        
        getEndDateTime() {
            if (this.selectedTimes.length === 0) return '';
            const sorted = [...this.selectedTimes].sort();
            const lastHour = parseInt(sorted[sorted.length - 1].split(':')[0]);
            const endHour = `${(lastHour + 1).toString().padStart(2, '0')}:00`;
            return `${this.formatDate(this.selectedDate)}T${endHour}`;
        }
    }
}
</script>
@endpush
@endsection
