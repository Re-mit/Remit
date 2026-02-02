@extends('layouts.app')

@section('title', '관리자 - 에러 보고 게시판')
@section('admin_title', '에러 보고')

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
            <h2 class="text-lg font-bold text-gray-900 mb-2">에러 보고 게시판</h2>

            <div class="mt-4 space-y-3">
                @if($reports->isEmpty())
                    <div class="text-sm text-gray-600">미해결 에러 보고가 없습니다.</div>
                @else
                    @foreach($reports as $r)
                        <div class="rounded-xl border p-4">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 break-words">
                                        {{ $r->title }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        작성자: {{ $r->user?->name }} ({{ $r->user?->email }})
                                        @if($r->user?->student_id) · {{ $r->user->student_id }} @endif
                                        · 작성일: {{ $r->created_at?->timezone('Asia/Seoul')->format('Y-m-d H:i') }}
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('admin.error-reports.resolve', $r->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full sm:w-auto px-3 py-2 rounded-lg bg-blue-50 text-blue-700 text-sm font-semibold border border-blue-200 hover:bg-blue-100 whitespace-nowrap">
                                        해결
                                    </button>
                                </form>
                            </div>

                            <div class="mt-3 text-sm text-gray-700 whitespace-pre-line break-words">
                                {{ $r->content }}
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-4">
                        {{ $reports->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


