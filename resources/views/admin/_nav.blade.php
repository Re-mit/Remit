<div class="bg-white sticky top-0 z-20 border-b shadow-sm">
    <div class="flex items-center justify-between px-5 py-4">
        <a href="{{ route('reservation.index') }}" class="text-sm text-gray-600">←</a>
        <h1 class="text-xl font-bold text-gray-900">@yield('admin_title', '관리자')</h1>
        <div class="w-[26px]"></div>
    </div>

    <div class="px-5 pb-3">
        <div class="-mx-5 px-5 overflow-x-auto pb-1">
            <div class="flex gap-2 w-max min-w-full">
            <a href="{{ route('admin.urls') }}"
               class="flex-shrink-0 px-3 py-2 text-sm font-semibold whitespace-nowrap {{ request()->routeIs('admin.urls') ? 'font-bold text-blue-500' : 'bg-white text-gray-700' }}">
                URL 등록
            </a>
            <a href="{{ route('admin.users') }}"
               class="flex-shrink-0 px-3 py-2 text-sm font-semibold whitespace-nowrap {{ request()->routeIs('admin.users') ? 'font-bold text-blue-500' : 'bg-white text-gray-700' }}">
                사용자 관리
            </a>
            <a href="{{ route('admin.notices') }}"
               class="flex-shrink-0 px-3 py-2 text-sm font-semibold whitespace-nowrap {{ request()->routeIs('admin.notices') ? 'font-bold text-blue-500' : 'bg-white text-gray-700' }}">
                공지 작성
            </a>
            <a href="{{ route('admin.reservations') }}"
               class="flex-shrink-0 px-3 py-2 text-sm font-semibold whitespace-nowrap {{ request()->routeIs('admin.reservations') ? 'font-bold text-blue-500' : 'bg-white text-gray-700' }}">
                예약 관리
            </a>
        </div>
        </div>
    </div>
</div>


