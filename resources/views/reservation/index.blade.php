@extends('layouts.app')

@section('title', '예약')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24" x-data="reservationApp()">
    <!-- Header -->
    <div class="bg-white sticky top-0 z-20 border-b shadow-sm">
        <div class="flex items-center justify-between px-5 py-4">
            <div class="w-[90px]"></div>
            <h1 class="text-xl font-bold text-gray-900">예약</h1>
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
            <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <rect width="26" height="26" fill="url(#pattern0_58_543)"/>
            <defs>
            <pattern id="pattern0_58_543" patternContentUnits="objectBoundingBox" width="1" height="1">
            <use xlink:href="#image0_58_543" transform="scale(0.00195312)"/>
            </pattern>
            <image id="image0_58_543" width="512" height="512" preserveAspectRatio="none" xlink:href="data:image/png;base64,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"/>
            </defs>
                </svg>
                <!-- Notification badge -->
            @if(isset($unreadCount) && $unreadCount > 0)
            <span class="absolute -top-0.5 -right-1 w-3 h-3 bg-[#FF8282] rounded-full"></span>
            @endif
            </a>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="bg-white border-b border-gray-100 px-4 py-4 m-4 rounded-2xl">
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
                    class="aspect-square flex items-center justify-center rounded-full text-sm transition-all relative"
                    :class="{
                        'bg-blue-500 text-white font-bold': day.date && isSelected(day.date),
                        'text-gray-300 cursor-default': !day.date || !isSelectable(day.date),
                        'hover:bg-gray-100': day.date && isSelectable(day.date) && !isSelected(day.date),
                   'text-red-500': day.date && isSelectable(day.date) && !isSelected(day.date) && isSunday(day.date),
                   'text-blue-500': day.date && isSelectable(day.date) && !isSelected(day.date) && isSaturday(day.date),
                   'text-gray-900': day.date && isSelectable(day.date) && !isSelected(day.date) && !isSunday(day.date) && !isSaturday(day.date),
                        'font-semibold': day.isToday
                    }"
                    :disabled="!day.date || !isSelectable(day.date)"
                >
                    <span x-text="day.dayNum || ''"></span>
                    <!-- Today indicator -->
                    <span x-show="day.isToday && !isSelected(day.date)" class="absolute bottom-1 w-1 h-1 bg-blue-500 rounded-full"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- Time Slots -->
    <div class="px-4 py-4">
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4">
                <h2 class="font-semibold text-gray-900">시간 선택</h2>
                <div class="mt-4 flex h-[14px] items-center justify-start gap-3">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_58_1042)">
                    <path d="M7 14C8.85652 14 10.637 13.2625 11.9497 11.9497C13.2625 10.637 14 8.85652 14 7C14 5.14348 13.2625 3.36301 11.9497 2.05025C10.637 0.737498 8.85652 0 7 0C5.14348 0 3.36301 0.737498 2.05025 2.05025C0.737498 3.36301 0 5.14348 0 7C0 8.85652 0.737498 10.637 2.05025 11.9497C3.36301 13.2625 5.14348 14 7 14ZM5.90625 9.1875H6.5625V7.4375H5.90625C5.54258 7.4375 5.25 7.14492 5.25 6.78125C5.25 6.41758 5.54258 6.125 5.90625 6.125H7.21875C7.58242 6.125 7.875 6.41758 7.875 6.78125V9.1875H8.09375C8.45742 9.1875 8.75 9.48008 8.75 9.84375C8.75 10.2074 8.45742 10.5 8.09375 10.5H5.90625C5.54258 10.5 5.25 10.2074 5.25 9.84375C5.25 9.48008 5.54258 9.1875 5.90625 9.1875ZM7 3.5C7.23206 3.5 7.45462 3.59219 7.61872 3.75628C7.78281 3.92038 7.875 4.14294 7.875 4.375C7.875 4.60706 7.78281 4.82962 7.61872 4.99372C7.45462 5.15781 7.23206 5.25 7 5.25C6.76794 5.25 6.54538 5.15781 6.38128 4.99372C6.21719 4.82962 6.125 4.60706 6.125 4.375C6.125 4.14294 6.21719 3.92038 6.38128 3.75628C6.54538 3.59219 6.76794 3.5 7 3.5Z" fill="#3B82F6"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_58_1042">
                    <path d="M0 0H14V14H0V0Z" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg>
                    <p class="text-xs text-gray-600 mt-1">최대 4시간까지 선택 가능</p>
                </div>
            </div>
            
            <div class="p-4 grid grid-cols-3 gap-2">
                <template x-for="slot in timeSlots" :key="slot.time">
                    <button 
                        @click="toggleTimeSlot(slot)"
                        class="py-3 px-2 rounded-xl text-sm font-medium transition-all border-2"
                        :class="{
                            'bg-gray-100 text-gray-400 border-transparent cursor-not-allowed': slot.isPast,
                            'bg-blue-500 text-white border-blue-500': isTimeSelected(slot.time) && !slot.isPast,
                            'bg-white text-gray-700 border-gray-200 hover:border-blue-300': !isTimeSelected(slot.time) && !slot.isPast
                        }"
                        :disabled="slot.isPast"
                    >
                        <span x-text="slot.time"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- Selected Time Summary -->
    <div class="px-4" x-show="selectedTimes.length > 0" x-transition>
        <div class="bg-blue-50 rounded-2xl p-4 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-600 font-medium">선택한 시간</p>
                    <p class="text-lg font-bold text-blue-900" x-text="getSelectedTimeRange()"></p>
                </div>
                <button @click="clearSelection()" class="text-blue-500 text-sm font-medium">초기화</button>
            </div>
        </div>
    </div>

    <!-- Seat Selection -->
    <div class="px-4" x-show="selectedTimes.length > 0" x-transition>
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-4 py-4 px-2">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">좌석 선택</h2>
                    <p class="text-xs text-gray-500" x-show="!loadingSeats && totalSeats > 0">
                        잔여 <span class="font-semibold text-gray-700" x-text="availableSeatsCount"></span> / <span x-text="totalSeats"></span>
                    </p>
                </div>

                <div class="mt-2" x-show="loadingSeats">
                    <p class="text-sm text-gray-500">좌석 정보를 불러오는 중...</p>
                </div>

                <div class="mt-2" x-show="!loadingSeats && selectedTimes.length > 0 && availableSeatsCount === 0">
                    <p class="text-sm text-red-600">해당 시간대는 만석입니다. 다른 시간을 선택해주세요.</p>
                </div>

                <!-- 5좌석 코너 배치 (좌상/우상/가운데/좌하/우하) -->
                <div class="mt-4 grid grid-cols-3 gap-4 w-fit mx-auto" x-show="!loadingSeats && totalSeats > 0">
                    <template x-for="(cell, idx) in seatCells" :key="idx">
                        <div class="w-16 h-10 flex items-center justify-center">
                            <template x-if="cell">
                                <button
                                    type="button"
                                    @click="cell.is_available && selectSeat(cell.id)"
                                    class="w-full h-full rounded-lg text-sm font-semibold transition-all border-2 overflow-hidden"
                                    :disabled="!cell.is_available"
                                    :class="{
                                        'bg-blue-500 text-white border-blue-500': selectedSeatId === cell.id,
                                        'bg-white text-gray-700 border-gray-200 hover:border-blue-300': selectedSeatId !== cell.id && cell.is_available,
                                        'bg-gray-100 text-gray-400 border-transparent cursor-not-allowed': !cell.is_available
                                    }"
                                >
                                    <div class="w-full h-full flex flex-col items-center justify-center">
                                        <div class="flex-1 flex items-center justify-center whitespace-nowrap text-xs w-full text-center text-nowrap h-full">
                                            <span class="text-center" x-text="cell.label"></span>
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Reserve Button -->
    <div class="px-4 pb-6">
        <form method="POST" action="{{ route('reservation.store') }}" x-ref="reservationForm">
            @csrf
            <input type="hidden" name="segments" :value="getSegmentsJson()">
            <input type="hidden" name="seat_id" :value="selectedSeatId">
            
            <button 
                type="submit"
                class="w-full py-4 rounded-xl font-semibold text-lg transition-all shadow-lg"
                :class="{
                    'bg-blue-500 text-white hover:bg-blue-600': selectedTimes.length > 0 && !!selectedSeatId && !loadingSeats && availableSeatsCount > 0,
                    'bg-gray-200 text-gray-400 cursor-not-allowed': selectedTimes.length === 0 || !selectedSeatId || loadingSeats || availableSeatsCount === 0
                }"
                :disabled="selectedTimes.length === 0 || !selectedSeatId || loadingSeats || availableSeatsCount === 0"
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
        seats: [],
        availableSeatsCount: 0,
        totalSeats: 0,
        selectedSeatId: null,
        loadingSeats: false,
        get seatCells() {
            // 3x3: [좌상,_,우상]/[_,가운데,_]/[좌하,_,우하]
            const order = [0, null, 1, null, 2, null, 3, null, 4];
            const arr = [];
            for (const idx of order) {
                arr.push(idx === null ? null : (this.seats[idx] || null));
            }
            return arr;
        },
        
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

        isSunday(date) {
            return date.getDay() === 0;
        },

        isSaturday(date) {
            return date.getDay() === 6;
        },
        
        get timeSlots() {
            const slots = [];
            const selectedDateStr = this.formatDate(this.selectedDate);
            const today = new Date();
            const isToday = this.formatDate(this.selectedDate) === this.formatDate(today);
            const currentHour = today.getHours();
            
            for (let hour = 9; hour <= 21; hour++) {
                const time = `${hour.toString().padStart(2, '0')}:00`;
                const isPast = isToday && hour <= currentHour;
                slots.push({ time, isPast });
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
                this.resetSeats();
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
        
        toggleTimeSlot(slot) {
            if (slot.isPast) return;
            
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
            this.refreshAvailableSeats();
        },
        
        isTimeSelected(time) {
            return this.selectedTimes.includes(time);
        },
        
        getSelectedTimeRange() {
            if (this.selectedTimes.length === 0) return '';
            return this.getSegments()
                .map(seg => `${seg.start_time} ~ ${seg.end_time}`)
                .join(', ');
        },
        
        clearSelection() {
            this.selectedTimes = [];
            this.resetSeats();
        },

        resetSeats() {
            this.seats = [];
            this.availableSeatsCount = 0;
            this.totalSeats = 0;
            this.selectedSeatId = null;
            this.loadingSeats = false;
        },

        selectSeat(seatId) {
            this.selectedSeatId = seatId;
        },
        
        formatDate(date) {
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        
        getSegments() {
            if (this.selectedTimes.length === 0) return [];

            const dateStr = this.formatDate(this.selectedDate);
            const hours = [...this.selectedTimes]
                .map(t => parseInt(t.split(':')[0], 10))
                .sort((a, b) => a - b);

            const groups = [];
            let current = [hours[0]];
            for (let i = 1; i < hours.length; i++) {
                if (hours[i] === hours[i - 1] + 1) {
                    current.push(hours[i]);
                } else {
                    groups.push(current);
                    current = [hours[i]];
                }
            }
            groups.push(current);

            return groups.map(g => {
                const startHour = g[0];
                const endHour = g[g.length - 1] + 1;
                const start_time = `${startHour.toString().padStart(2, '0')}:00`;
                const end_time = `${endHour.toString().padStart(2, '0')}:00`;
                return {
                    start_at: `${dateStr}T${start_time}`,
                    end_at: `${dateStr}T${end_time}`,
                    start_time,
                    end_time,
                    hours: g.length,
                };
            });
        },

        getSegmentsJson() {
            const segments = this.getSegments().map(s => ({ start_at: s.start_at, end_at: s.end_at }));
            return JSON.stringify(segments);
        },

        async refreshAvailableSeats() {
            if (this.selectedTimes.length === 0) {
                this.resetSeats();
                return;
            }

            this.loadingSeats = true;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch("{{ route('reservation.available_seats') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                    },
                    body: JSON.stringify({
                        segments: this.getSegmentsJson(),
                    }),
                });

                if (!res.ok) {
                    this.resetSeats();
                    return;
                }

                const data = await res.json();
                this.seats = data.seats || [];
                this.availableSeatsCount = data.available_seats_count || 0;
                this.totalSeats = data.total_seats || 0;

                // 현재 선택 좌석이 더 이상 가능하지 않으면 해제
                if (this.selectedSeatId) {
                    const picked = this.seats.find(s => s.id === this.selectedSeatId);
                    if (!picked || !picked.is_available) this.selectedSeatId = null;
                }
            } catch (e) {
                this.resetSeats();
            } finally {
                this.loadingSeats = false;
            }
        }
    }
}
</script>
@endpush
@endsection
