@extends('layouts.app')

@section('title', '관리자 - 공지 작성')
@section('admin_title', '공지 작성')

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
            <h2 class="text-lg font-bold text-gray-900 mb-4">공지사항 발송</h2>

            <form method="POST" action="{{ route('admin.notices.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">제목</label>
                    <input name="title" value="{{ old('title') }}" class="w-full rounded-lg border px-3 py-2" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">내용</label>
                    <textarea name="message" rows="6" class="w-full rounded-lg border px-3 py-2" required>{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="w-full bg-blue-500 text-white py-3 rounded-xl font-semibold hover:bg-blue-600">
                    공지 발송
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-5">
            <h2 class="text-lg font-bold text-gray-900 mb-2">내가 작성한 공지</h2>
            <p class="text-sm text-gray-600 mb-4">잘못 작성한 공지는 여기서 삭제할 수 있습니다. (삭제 시 모든 사용자에게서도 함께 삭제됩니다.)</p>

            @if(empty($myNotices) || $myNotices->isEmpty())
                <div class="text-sm text-gray-600">아직 작성한 공지가 없습니다.</div>
            @else
                <div class="space-y-3">
                    @foreach($myNotices as $n)
                        <div class="rounded-xl border p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 break-all">{{ $n->title }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $n->created_at?->format('Y-m-d H:i') ?? '' }}
                                    </div>
                                    <div class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $n->message }}</div>
                                </div>

                                <form method="POST" action="{{ route('admin.notices.destroy', $n->id) }}"
                                      onsubmit="return confirm('이 공지를 삭제하시겠습니까? 삭제하면 모든 사용자에게서도 사라집니다.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="flex-shrink-0 px-3 py-2 rounded-lg bg-red-50 text-red-700 text-sm font-semibold border border-red-200 hover:bg-red-100 whitespace-nowrap">
                                        삭제
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $myNotices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


