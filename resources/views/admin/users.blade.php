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


