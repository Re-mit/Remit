@extends('layouts.app')

@section('title', '회원가입')

@section('content')
<div class="bg-[#EFF5FF] min-h-screen flex flex-col">
    <div class="flex-1 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-semibold text-gray-900">회원가입</h1>
                <p class="mt-2 text-gray-600">가천 이메일로 계정을 생성합니다</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8">
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                    </div>
                @endif

                @php
                    $verifiedEmail = session('register_verified_email');
                    $pendingEmail = session('register_pending_email');
                    $isVerified = !empty($verifiedEmail) && $verifiedEmail === old('email');
                @endphp

                <form method="POST" action="{{ route('register.store') }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">이름</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="홍길동"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">학번</label>
                        <input
                            type="text"
                            name="student_id"
                            value="{{ old('student_id') }}"
                            required
                            inputmode="numeric"
                            pattern="[0-9]*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="ex) 202612345"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">이메일</label>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                class="flex-1 min-w-0 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="example@gachon.ac.kr"
                            />
                            <button
                                type="submit"
                                formaction="{{ route('register.send_code') }}"
                                formmethod="POST"
                                formnovalidate
                                class="js-loading-btn flex-shrink-0 px-4 py-2 min-w-[92px] rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 whitespace-nowrap"
                                data-loading-text="발송 중..."
                            >
                                번호 발송
                            </button>
                        </div>

                        <div class="mt-2 flex flex-col sm:flex-row gap-2">
                            <input
                                type="text"
                                name="code"
                                value="{{ old('code') }}"
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                maxlength="6"
                                class="w-40 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="인증번호 6자리"
                            />
                            <button
                                type="submit"
                                formaction="{{ route('register.verify_code') }}"
                                formmethod="POST"
                                formnovalidate
                                class="flex-shrink-0 px-4 py-2 min-w-[92px] rounded-lg bg-blue-500 text-white text-sm font-medium hover:bg-blue-600 whitespace-nowrap"
                            >
                                인증 확인
                            </button>
                        </div>

                        <div class="mt-2 text-xs">
                            @if($verifiedEmail && $verifiedEmail === old('email'))
                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-50 text-green-700 border border-green-200">
                                    이메일 인증 완료
                                </span>
                            @elseif($pendingEmail && $pendingEmail === old('email'))
                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                    인증번호 발송됨 (이메일 확인)
                                </span>
                            @else
                                <span class="text-gray-500">인증번호를 발송하고 인증을 완료해야 회원가입이 가능합니다.</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">비밀번호</label>
                        <input
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="4~12자"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">비밀번호 확인</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="비밀번호 확인"
                        />
                    </div>

                    <button
                        type="submit"
                        @disabled(!($verifiedEmail && $verifiedEmail === old('email')))
                        class="w-full px-4 py-3 rounded-lg font-medium transition-colors duration-200 {{ ($verifiedEmail && $verifiedEmail === old('email')) ? 'bg-blue-500 text-white hover:bg-blue-600' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}"
                    >
                        회원가입
                    </button>
                </form>

                <div class="mt-4 text-center text-sm text-gray-600">
                    이미 계정이 있나요?
                    <a href="{{ route('login') }}" class="text-blue-600 hover:underline">로그인</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.js-loading-btn').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      // 중복 클릭 방지
      if (btn.dataset.loading === '1') {
        e.preventDefault();
        return;
      }
      btn.dataset.loading = '1';

      // 텍스트는 즉시 변경하되, disabled는 다음 tick에 적용 (일부 브라우저에서 submit 취소 방지)
      btn.dataset.originalText = btn.textContent.trim();
      btn.textContent = btn.dataset.loadingText || '처리 중...';

      setTimeout(() => {
        btn.disabled = true;
        btn.classList.add('opacity-70', 'cursor-not-allowed');
      }, 0);
    });
  });
});
</script>
@endpush


