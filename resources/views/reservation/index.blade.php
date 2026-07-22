@extends('layouts.app')

@section('title', '예약')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24" x-data="reservationApp()">
    <!-- Unread Notice Toasts (페이지 최상단 fixed, 헤더에 종속되지 않음) -->
    <div
        x-cloak
        x-show="noticeToasts.length > 0"
        x-ref="noticeToastWrap"
        class="fixed left-1/2 -translate-x-1/2 w-full max-w-[430px] px-4 pt-3 z-50"
        :style="`top: ${headerHeight}px`"
        aria-live="polite"
        aria-relevant="additions removals"
    >
        <div class="space-y-2">
            <template x-for="n in noticeToasts" :key="n.id">
                <div class="bg-white border border-gray-200 shadow-lg rounded-2xl p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold" :class="n.type === 'penalty' ? 'text-red-600' : 'text-blue-600'" x-text="n.type === 'penalty' ? '패널티 알림' : '공지 알림'"></div>
                            <div class="mt-1 text-sm font-bold text-gray-900 break-words" x-text="n.title"></div>
                            <div class="mt-2 text-sm text-gray-700 whitespace-pre-line break-words" x-text="n.message"></div>
                            <div class="mt-3 flex items-center gap-3">
                                <a href="{{ route('notification.index') }}" class="text-sm font-semibold text-blue-600 hover:underline">
                                    알림함 보기
                                </a>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="flex-shrink-0 text-gray-500 hover:text-gray-700 px-2 py-1 rounded-lg border border-gray-200"
                            @click="dismissNotice(n.id)"
                        >
                            닫기
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Header -->
    <div class="bg-white sticky top-0 z-20 border-b shadow-sm" x-ref="headerWrap">
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
            <img src="{{ asset('images/notification-bell.png') }}" alt="알림" width="26" height="26" class="w-[26px] h-[26px]">
                <!-- Notification badge -->
            <span x-cloak x-show="unreadCount > 0" class="absolute -top-0.5 -right-1 w-3 h-3 bg-[#FF8282] rounded-full"></span>
            </a>
            </div>
        </div>
    </div>

    <!-- fixed 토스트가 달력과 겹치지 않도록 공간 확보 -->
    <div
        x-cloak
        x-show="noticeToasts.length > 0"
        :style="`height: ${toastHeight}px`"
        aria-hidden="true"
    ></div>

    <!-- Calendar -->
    <div class="bg-white border-b border-gray-100 px-4 py-4 m-4 rounded-2xl">
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

                <!-- Seat 3 noise notice -->
                <div class="mt-3" x-cloak x-show="!loadingSeats && selectedTimes.length > 0 && isGroupSeatReserved" x-transition>
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-3 text-sm">
                        단체석이 예약되어있어서 소음이 발생할 수 있습니다.
                    </div>
                </div>

                <div class="mt-2" x-show="loadingSeats">
                    <p class="text-sm text-gray-500">좌석 정보를 불러오는 중...</p>
                </div>

                <div class="mt-2" x-show="!loadingSeats && selectedTimes.length > 0 && availableSeatsCount === 0">
                    <p class="text-sm text-red-600">해당 시간대는 만석입니다. 다른 시간을 선택해주세요.</p>
                </div>

                <!-- 6좌석 배치 (기존 5좌석 코너 배치 + 6번은 5번 아래) -->
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
                                        <div class="pb-1 leading-none" x-cloak x-show="getSeatNumber(cell.label) === 3">
                                            <span class="text-[10px] font-medium text-gray-500">(단체석)</span>
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

            <!-- Seat 3 (Group seat) member emails -->
            <template x-if="isGroupSeatSelected">
                <div class="mt-4 bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900">동반 이용자 이메일</h3>
                        <p class="mt-1 text-xs text-gray-600">
                            3번 좌석(단체석)을 함께 이용할 사람의 이메일을 입력해주세요. 최대 4명까지 추가할 수 있어요.
                        </p>

                        <template x-for="(email, i) in groupMemberEmails" :key="i">
                            <div class="mt-3 flex items-center gap-2">
                                <input
                                    type="email"
                                    name="group_member_emails[]"
                                    class="flex-1 px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 text-sm"
                                    placeholder="example@gachon.ac.kr"
                                    x-model="groupMemberEmails[i]"
                                    :required="i === 0"
                                >
                                <button
                                    type="button"
                                    class="px-3 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50"
                                    x-cloak
                                    x-show="groupMemberEmails.length > 1"
                                    @click="removeGroupMember(i)"
                                >
                                    삭제
                                </button>
                            </div>
                        </template>

                        <button
                            type="button"
                            class="mt-3 text-sm font-semibold text-blue-600 hover:underline disabled:text-gray-300 disabled:no-underline"
                            :disabled="groupMemberEmails.length >= 4"
                            @click="addGroupMember()"
                        >
                            + 사람 추가
                        </button>
                    </div>
                </div>
            </template>
            
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

    const unreadNotices = @json(($unreadNotices ?? collect())->values());
    const initialUnreadCount = @json($unreadCount ?? 0);
    const markReadUrlTemplate = @json(route('notification.read', ['id' => '___ID___']));

    return {
        headerHeight: 0,
        toastHeight: 0,
        unreadCount: initialUnreadCount,
        unreadNotices: unreadNotices,
        get noticeToasts() {
            return (this.unreadNotices || []).map(n => ({
                id: n.id,
                type: n.type || 'notice',
                title: n.title || '공지',
                message: n.message || '',
            }));
        },
        csrfToken() {
            const el = document.querySelector('meta[name="csrf-token"]');
            return el ? el.getAttribute('content') : '';
        },
        recalcHeaderHeight() {
            this.$nextTick(() => {
                const el = this.$refs.headerWrap;
                this.headerHeight = el ? (el.offsetHeight || 0) : 0;
            });
        },
        recalcToastHeight() {
            this.$nextTick(() => {
                const el = this.$refs.noticeToastWrap;
                this.toastHeight = (el && this.noticeToasts.length > 0) ? (el.offsetHeight || 0) : 0;
            });
        },
        init() {
            this.recalcHeaderHeight();
            this.recalcToastHeight();
            // 리사이즈 시 높이 재계산 (폰 회전/뷰포트 변화 대응)
            window.addEventListener('resize', () => {
                this.recalcHeaderHeight();
                this.recalcToastHeight();
            });
        },
        async dismissNotice(id) {
            // 1) 서버에 읽음 처리
            try {
                const url = markReadUrlTemplate.replace('___ID___', String(id));
                await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({}),
                });
            } catch (e) {
                // 네트워크 실패여도 UI는 닫기 처리(다음 새로고침 시 다시 뜰 수 있음)
            }

            // 2) UI에서 제거 + 뱃지 카운트 감소
            this.unreadNotices = (this.unreadNotices || []).filter(n => n.id !== id);
            if (this.unreadCount > 0) this.unreadCount -= 1;
            this.recalcToastHeight();
        },
        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear(),
        selectedDate: new Date(),
        selectedTimes: [],
        seats: [],
        availableSeatsCount: 0,
        totalSeats: 0,
        selectedSeatId: null,
        loadingSeats: false,
        groupMemberEmails: [''],
        getSeatNumber(label) {
            if (!label) return null;
            const m = String(label).trim().match(/^(\d+)\s*번$/);
            return m ? parseInt(m[1], 10) : null;
        },
        get groupSeatId() {
            const s = (this.seats || []).find(x => this.getSeatNumber(x?.label) === 3);
            return s ? s.id : null;
        },
        get isGroupSeatReserved() {
            const s = (this.seats || []).find(x => this.getSeatNumber(x?.label) === 3);
            return !!(s && s.is_available === false);
        },
        get isGroupSeatSelected() {
            return !!(this.selectedSeatId && this.groupSeatId && this.selectedSeatId === this.groupSeatId);
        },
        get seatCells() {
            // 3x4:
            // 1) [좌상,_,우상]
            // 2) [_,가운데,_]
            // 3) [좌하,_,우하]
            // 4) [_,_,(5번 아래)]
            //
            // 좌석 인덱스 기준:
            // - 0: 1번, 1: 2번, 2: 3번, 3: 4번, 4: 5번, 5: 6번
            const order = [0, null, 1, null, 2, null, 3, null, 4, null, null, 5];
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
            if (!this.isGroupSeatSelected) {
                this.groupMemberEmails = [''];
            }
        },
        addGroupMember() {
            if (this.groupMemberEmails.length >= 4) return;
            this.groupMemberEmails.push('');
        },
        removeGroupMember(i) {
            if (this.groupMemberEmails.length <= 1) return;
            this.groupMemberEmails.splice(i, 1);
            if (this.groupMemberEmails.length === 0) this.groupMemberEmails = [''];
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
                'Content-Type': 'application/x-www-form-urlencoded', // 가비아 방화벽 통과용
                'Accept': 'application/json',
                ...(token ? { 'X-CSRF-TOKEN': token } : {}),
            },
            // 중요: x-www-form-urlencoded 형식이면 body도 URLSearchParams여야 합니다.
            body: new URLSearchParams({
                segments: this.getSegmentsJson(),
            }),
        });

        // 1. 먼저 응답 데이터를 파싱합니다.
        const data = await res.json();

        // 2. 응답이 정상이 아닐 때 알림창을 띄웁니다.
        if (!res.ok) {
            alert(data.message || '좌석 정보를 불러올 수 없습니다.');
            this.resetSeats();
            return;
        }

        // 3. 정상일 때 좌석 정보를 업데이트합니다.
        this.seats = data.seats || [];
        this.availableSeatsCount = data.available_seats_count || 0;
        this.totalSeats = data.total_seats || 0;

        if (this.selectedSeatId) {
            const picked = this.seats.find(s => s.id === this.selectedSeatId);
            if (!picked || !picked.is_available) this.selectedSeatId = null;
        }
    } catch (e) {
        console.error(e);
        // 서버 연결 자체가 끊겼을 때의 예외 처리
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
