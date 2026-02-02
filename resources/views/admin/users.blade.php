@extends('layouts.app')

@section('title', '관리자 - 사용자 관리')
@section('admin_title', '사용자 관리')

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
            <h2 class="text-lg font-bold text-gray-900 mb-2">관리자 관리</h2>
            <p class="text-sm text-gray-600">
                관리자를 추가/해제하고, 현재 관리자 목록을 조회할 수 있습니다.
            </p>

            <form method="POST" action="{{ route('admin.admins.store') }}" class="mt-4 grid gap-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">관리자로 등록할 사용자 이메일</label>
                    <input name="email" class="w-full rounded-lg border px-3 py-2" placeholder="example@gachon.ac.kr" required />
                    <div class="mt-2 text-xs text-gray-500">이미 회원가입된 사용자만 관리자 등록이 가능합니다.</div>
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white py-3 rounded-xl font-semibold">
                    관리자 등록
                </button>
            </form>

            <div class="mt-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">관리자 목록</h3>

                @if($admins->isEmpty())
                    <div class="text-sm text-gray-600">등록된 관리자가 없습니다.</div>
                @else
                    <div class="space-y-2">
                        @foreach($admins as $row)
                            @php
                                $isEnvAdmin = !empty($envAdminEmail) && \App\Models\AllowedEmail::normalize($envAdminEmail) === \App\Models\AllowedEmail::normalize($row->email);
                            @endphp
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl border p-3">
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-900 break-all flex items-center gap-2">
                                        <span>{{ $row->email }}</span>
                                        @if($isEnvAdmin)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border border-indigo-300 text-indigo-700 bg-indigo-50">
                                                슈퍼
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $row->name }} @if($row->student_id) · {{ $row->student_id }} @endif
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('admin.admins.destroy', $row->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-full sm:w-auto px-3 py-2 rounded-lg bg-red-50 text-red-700 text-sm font-semibold border border-red-200 hover:bg-red-100 whitespace-nowrap"
                                            {{ $isEnvAdmin ? 'disabled' : '' }}>
                                        관리자 해제
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <h2 class="text-lg font-bold text-gray-900 mb-2">사용자 패널티/정지</h2>
            <p class="text-sm text-gray-600">
                사용자에게 패널티를 부여할 수 있습니다. <span class="font-semibold">패널티 2회 누적 시 계정이 정지됩니다.</span>
            </p>

            <div class="mt-4 space-y-2">
                @if(empty($users) || $users->isEmpty())
                    <div class="text-sm text-gray-600">표시할 사용자가 없습니다.</div>
                @else
                    @foreach($users as $u)
                        @php
                            $isAdmin = ($u->role ?? null) === 'admin';
                            $isSuspended = !empty($u->suspended_at);
                            $penalties = (int) ($u->warning ?? 0);
                            $isEnvAdmin = !empty($envAdminEmail) && \App\Models\AllowedEmail::normalize($envAdminEmail) === \App\Models\AllowedEmail::normalize($u->email);
                            $protected = $isAdmin || $isEnvAdmin;
                        @endphp

                        <div class="flex flex-col gap-3 rounded-xl border p-3">
                            <div class="flex flex-col gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-900 break-all flex flex-wrap items-center gap-2">
                                        <span>{{ $u->email }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $u->name }} @if($u->student_id) · {{ $u->student_id }} @endif
                                        · 패널티 <span class="font-semibold text-gray-700">{{ $penalties }}</span>회
                                        @if($isSuspended && $u->suspended_at)
                                            · 정지일시: {{ $u->suspended_at->timezone('Asia/Seoul')->format('Y-m-d H:i') }}
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions: 각 버튼을 컬럼(열)로 -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <form method="POST" action="{{ route('admin.users.penalty', $u->id) }}" class="w-full">
                                        @csrf
                                        <button type="submit"
                                                class="w-full px-3 py-2 rounded-lg text-sm font-semibold border whitespace-nowrap
                                                       {{ $protected ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' : 'bg-yellow-50 text-yellow-800 border-yellow-200 hover:bg-yellow-100' }}"
                                                {{ $protected ? 'disabled' : '' }}>
                                            패널티 +1
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.users.unsuspend', $u->id) }}" class="w-full">
                                        @csrf
                                        <button type="submit"
                                                class="w-full px-3 py-2 rounded-lg text-sm font-semibold border whitespace-nowrap
                                                       {{ ($protected || !$isSuspended) ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' : 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100' }}"
                                                {{ ($protected || !$isSuspended) ? 'disabled' : '' }}>
                                            정지 해제
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.users.penalty.reset', $u->id) }}" class="w-full">
                                        @csrf
                                        <button type="submit"
                                                class="w-full px-3 py-2 rounded-lg text-sm font-semibold border whitespace-nowrap
                                                       {{ $protected ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}"
                                                {{ $protected ? 'disabled' : '' }}>
                                            초기화
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if(!$protected)
                                <div class="text-xs text-gray-500">
                                    패널티 2회 이상이면 자동으로 정지 처리됩니다.
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="mt-4">
                        {{ $users->appends(request()->except('members'))->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <h2 class="text-lg font-bold text-gray-900 mb-2">회원가입 허용 이메일</h2>
            <p class="text-sm text-gray-600">
                여기에 등록된 이메일만 회원가입 가능합니다. 등록되지 않은 이메일로 회원가입 시
                <span class="font-semibold">"해당 학과가 아닙니다. 관리자에게 문의하세요"</span>가 표시됩니다.
            </p>

            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 grid gap-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">허용 이메일</label>
                    <input name="email" class="w-full rounded-lg border px-3 py-2" placeholder="example@gachon.ac.kr" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">메모(선택)</label>
                    <input name="memo" class="w-full rounded-lg border px-3 py-2" placeholder="예: 2026학번 대표자" />
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white py-3 rounded-xl font-semibold">
                    추가/수정
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <h3 class="text-lg font-bold text-gray-900 mb-4">허용 목록</h3>

            @if($allowedEmails->isEmpty())
                <div class="text-sm text-gray-600">아직 등록된 이메일이 없습니다.</div>
            @else
                <div class="space-y-2">
                    @foreach($allowedEmails as $row)
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl border p-3">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900 break-all">{{ $row->email }}</div>
                                @if($row->memo)
                                    <div class="text-xs text-gray-500 mt-1">{{ $row->memo }}</div>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('admin.users.destroy', $row->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full sm:w-auto px-3 py-2 rounded-lg bg-red-50 text-red-700 text-sm font-semibold border border-red-200 hover:bg-red-100 whitespace-nowrap">
                                    삭제
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $allowedEmails->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


