@extends('layouts.app')

@section('title', '관리자 - 예약 관리')
@section('admin_title', '예약 관리')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24">
    @include('admin._nav')

    <div class="p-5 space-y-6">
        @if(session('success'))
            <div class="bg-green-50 text-green-700 border border-green-200 rounded-xl p-4">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl p-4 space-y-1">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <h2 class="text-lg font-bold text-gray-900 mb-2">현재 예약 목록</h2>
            <p class="text-sm text-gray-600">예약된 내역(취소 제외) 중 아직 끝나지 않은 예약만 표시됩니다.</p>
        </div>

        @if($reservations->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border p-8 text-center text-gray-600">
                현재 예약된 내역이 없습니다.
            </div>
        @else
            <div class="space-y-3">
                @foreach($reservations as $r)
                    @php
                        $rep = $r->getRepresentative();
                        $repLabel = $rep ? ($rep->name . ' (' . $rep->email . ')') : ($r->users->first()?->email ?? '-');
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm text-gray-600">
                                    {{ $r->start_at->format('Y-m-d') }}
                                </div>
                                <div class="text-lg font-bold text-gray-900">
                                    {{ ($r->start_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $r->start_at->format('g:i') }}
                                    -
                                    {{ ($r->end_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $r->end_at->format('g:i') }}
                                </div>
                                <div class="mt-2 text-sm text-gray-700">
                                    <span class="font-semibold">{{ $r->room?->name ?? '-' }}</span>
                                    <span class="text-gray-400 mx-2">|</span>
                                    <span class="break-all">{{ $repLabel }}</span>
                                </div>
                            </div>

                            <form method="POST"
                                  action="{{ route('admin.reservations.destroy', $r->id) }}"
                                  x-data="{ showModal: false, reason: '' }"
                                  x-init="$watch('showModal', v => { document.body.style.overflow = v ? 'hidden' : ''; })">
                                @csrf
                                @method('DELETE')

                                <button type="button"
                                        @click="showModal = true"
                                        class="flex-shrink-0 px-4 py-2.5 rounded-xl bg-red-50 text-red-700 text-sm font-semibold border border-red-200 hover:bg-red-100 whitespace-nowrap">
                                    삭제
                                </button>

                                <!-- Delete Modal -->
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

                                        <h3 class="text-xl font-bold text-gray-900 mb-4">예약 삭제</h3>
                                        <p class="text-gray-700 mb-2">해당 예약을 삭제하시겠습니까?</p>
                                        <p class="text-sm text-gray-500 mb-4">삭제 사유는 예약자에게 표시됩니다.</p>

                                        <div class="mb-5">
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">삭제 사유</label>
                                            <textarea name="cancel_reason"
                                                      x-model="reason"
                                                      required
                                                      maxlength="1000"
                                                      rows="4"
                                                      class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-300"
                                                      placeholder="삭제 사유를 입력해주세요."></textarea>
                                        </div>

                                        <div class="flex gap-3">
                                            <button type="button"
                                                    @click="showModal = false"
                                                    class="flex-1 py-3 bg-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                                                취소
                                            </button>
                                            <button type="submit"
                                                    :disabled="!reason || reason.trim().length === 0"
                                                    class="flex-1 py-3 bg-red-500 text-white rounded-xl font-semibold hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                삭제
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection



