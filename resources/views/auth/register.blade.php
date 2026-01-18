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

                <form method="POST"
                      action="{{ route('register.store') }}"
                      class="space-y-3"
                      x-data="{
                        showTerms: false,
                        canProceed: false,
                        termsPage: 1,
                        termsAgreed: @json((bool) old('agree_terms')),
                        isVerified: @json((bool) $isVerified),
                        updateCanProceed() {
                          const el = this.$refs.termsScroll;
                          if (!el) return;
                          // 내용이 짧아 스크롤이 필요 없으면 즉시 진행 가능
                          if (el.scrollHeight <= el.clientHeight + 2) {
                            this.canProceed = true;
                            return;
                          }
                          this.canProceed = (el.scrollTop + el.clientHeight) >= (el.scrollHeight - 8);
                        },
                        openTerms() {
                          this.showTerms = true;
                          this.canProceed = false;
                          this.termsPage = 1;
                          this.$nextTick(() => {
                            const el = this.$refs.termsScroll;
                            if (el) el.scrollTop = 0;
                            this.updateCanProceed();
                          });
                        },
                        onScroll() {
                          this.updateCanProceed();
                        },
                        nextPage() {
                          if (!this.canProceed) return;
                          if (this.termsPage < 5) {
                            this.termsPage += 1;
                            this.canProceed = false;
                            this.$nextTick(() => {
                              const el = this.$refs.termsScroll;
                              if (el) el.scrollTop = 0;
                              this.updateCanProceed();
                            });
                          }
                        },
                        agree() {
                          if (this.termsPage !== 5) return;
                          if (!this.canProceed) return;
                          this.termsAgreed = true;
                          this.showTerms = false;
                        },
                        cancel() {
                          this.showTerms = false;
                        }
                      }"
                      x-init="$watch('showTerms', v => { document.body.style.overflow = v ? 'hidden' : ''; })">
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

                    <!-- 필수 동의 -->
                    <div class="pt-2">
                        <div class="flex items-start gap-3">
                            <input type="checkbox"
                                   class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   :checked="termsAgreed"
                                   @click.prevent="openTerms()"
                                   aria-label="이용약관 및 개인정보 처리방침 동의" />

                            <div class="min-w-0">
                                <button type="button"
                                        class="text-left text-sm text-gray-700 hover:underline"
                                        @click="openTerms()">
                                    <span class="font-semibold">[필수]</span> 이용약관 및 개인정보 처리방침 동의
                                </button>
                                <div class="text-xs text-gray-500 mt-1">
                                    체크 시 약관이 열리며, 각 페이지를 끝까지 스크롤해야 다음/동의가 가능합니다.
                                </div>
                            </div>
                        </div>

                        <template x-if="termsAgreed">
                            <input type="hidden" name="agree_terms" value="1" />
                        </template>
                    </div>

                    <button
                        type="submit"
                        :disabled="!isVerified || !termsAgreed"
                        :class="(!isVerified || !termsAgreed)
                          ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                          : 'bg-blue-500 text-white hover:bg-blue-600'"
                        class="w-full px-4 py-3 rounded-lg font-medium transition-colors duration-200"
                    >
                        회원가입
                    </button>

                    <!-- Terms Modal -->
                    <div x-show="showTerms"
                         x-cloak
                         style="display: none;"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
                         @click.self="cancel()">

                        <div class="bg-gray-50 rounded-2xl w-full max-w-md overflow-hidden border border-gray-200"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2">

                            <div class="px-5 py-4 border-b bg-gray-50">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="text-lg font-bold text-gray-900">이용약관 및 개인정보 처리방침</h3>
                                    <button type="button"
                                            class="text-gray-500 hover:text-gray-700"
                                            @click="cancel()">
                                        닫기
                                    </button>
                                </div>
                            </div>

                            <div class="px-5 py-4">
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                    <div>현재: <span class="font-semibold" x-text="termsPage"></span>/5</div>
                                    <div x-show="!canProceed" class="text-gray-500">끝까지 스크롤해 주세요</div>
                                </div>
                                <div class="border rounded-xl bg-white p-4 max-h-[60vh] overflow-y-auto text-sm text-gray-700 leading-relaxed"
                                     x-ref="termsScroll"
                                     @scroll.passive="onScroll()">
                                    <div class="space-y-3">
                                        <!-- Page 1 -->
                                        <template x-if="termsPage === 1">
                                            <div class="space-y-3">
                                                <div>
                                                    <div class="font-semibold">1. 서비스 개요</div>
                                                    <div class="text-gray-600 mt-1">
                                                        Remit은 학과 공용 스터디룸 예약 및 관리를 위한 서비스입니다. 본 약관에 동의해야 회원가입 및 서비스 이용이 가능합니다.
                                                    </div>
                                                </div>

                                                <div>
                                                    <div class="font-semibold">2. 계정 및 이용</div>
                                                    <div class="text-gray-600 mt-1">
                                                        - 가천대학교 이메일(@gachon.ac.kr) 기반으로 계정을 생성/관리합니다.<br>
                                                        - 이용자는 본인의 계정 정보를 안전하게 관리해야 합니다.<br>
                                                    </div>
                                                </div>

                                                <div class="text-gray-500 text-xs pt-2">
                                                    내용을 확인한 뒤, 끝까지 스크롤하면 다음 버튼이 활성화됩니다.
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Page 2 -->
                                        <template x-if="termsPage === 2">
                                            <div class="space-y-3">
                                                <div>
                                                    <div class="font-semibold">3. 공용 공간 이용 수칙(민폐 금지)</div>
                                                    <div class="text-gray-600 mt-1">
                                                        - 모두가 이용하는 공간이니 서로 배려하고, 타인에게 민폐가 되는 행위(소란, 무단 점유, 규칙 위반 등)를 금지합니다.<br>
                                                        - 운영 및 안전을 위해 필요한 경우, 운영진 안내에 협조해야 합니다.<br>
                                                    </div>
                                                </div>

                                                <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                                                    <div class="font-semibold text-red-700">[중요] 도난/분실 등 문제 발생 시</div>
                                                    <div class="text-red-700 mt-1">
                                                        도난, 분실, 안전사고 등 문제가 반복되거나 운영이 어렵다고 판단되는 경우,
                                                        <span class="font-semibold">사전 안내 후 서비스가 종료될 수 있습니다.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Page 3 -->
                                        <template x-if="termsPage === 3">
                                            <div class="space-y-3">
                                                <div>
                                                    <div class="font-semibold">4. 수집하는 개인정보 항목</div>
                                                    <div class="text-gray-600 mt-1">
                                                        - 필수: 이름, 학번, 이메일, 비밀번호(암호화 저장)<br>
                                                        - 이용기록: 예약 내역(시간/좌석), 접속/사용 기록(서비스 운영 목적)<br>
                                                    </div>
                                                </div>

                                                <div>
                                                    <div class="font-semibold">5. 개인정보 이용 목적</div>
                                                    <div class="text-gray-600 mt-1">
                                                        - 본인 확인 및 계정 관리<br>
                                                        - 예약 생성/조회/취소 및 서비스 운영<br>
                                                        - 부정 이용 방지 및 서비스 안정성 확보<br>
                                                    </div>
                                                </div>

                                                <div class="text-gray-500 text-xs pt-2">
                                                    내용을 확인한 뒤, 끝까지 스크롤하면 다음 버튼이 활성화됩니다.
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Page 4 -->
                                        <template x-if="termsPage === 4">
                                            <div class="space-y-3">
                                                <div>
                                                    <div class="font-semibold">6. 기물 파손/이상 발생 시 즉시 문의</div>
                                                    <div class="text-gray-600 mt-1">
                                                        - 공간 내 기물(가구/장비/도어락/열쇠 등) 파손, 이상 징후, 분실이 발생하거나 발견한 경우 <span class="font-semibold">즉시 문의를 남겨야 합니다.</span><br>
                                                        - <span class="font-semibold">문의 없이 방치하거나 고의로 은폐</span>하는 경우, 정황에 따라 <span class="font-semibold">책임을 물을 수 있습니다.</span><br>
                                                    </div>
                                                </div>

                                                <div>
                                                    <div class="font-semibold">7. 보관 및 파기</div>
                                                    <div class="text-gray-600 mt-1">
                                                        - 예약 내역은 서비스 정책에 따라 최근 1달치만 보관되며, 기간이 경과하면 자동으로 삭제될 수 있습니다.<br>
                                                        - 관련 법령 또는 분쟁 대응 등 정당한 사유가 있는 경우 일부 정보가 추가 보관될 수 있습니다.<br>
                                                    </div>
                                                </div>

                                                <div class="text-gray-500 text-xs pt-2">
                                                    내용을 확인한 뒤, 끝까지 스크롤하면 다음 버튼이 활성화됩니다.
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Page 5 -->
                                        <template x-if="termsPage === 5">
                                            <div class="space-y-3">
                                                <div>
                                                    <div class="font-semibold">8. 마감(마지막 시간대) 이용자 의무</div>
                                                    <div class="text-gray-600 mt-1">
                                                        - 마지막 시간대 이용자는 퇴실 시 <span class="font-semibold">반드시 문/열쇠를 잠금 처리</span>해야 합니다.<br>
                                                        - 잠금 미이행 등 안전에 영향을 주는 위반이 발생할 경우, 운영 및 보안 목적상 <span class="font-semibold">이용 기록 기반으로 확인/추적</span>할 수 있습니다.<br>
                                                        - 문의는 관리자에게 연락해 주세요.<br>
                                                    </div>
                                                </div>

                                                <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                                                    <div class="font-semibold text-red-700">[중요] 쓰레기/정리 미준수</div>
                                                    <div class="text-red-700 mt-1">
                                                        이용 후 쓰레기는 반드시 수거/정리해야 합니다.
                                                        청결/관리가 지속적으로 되지 않는 경우 <span class="font-semibold">사전 안내 후 서비스가 종료될 수 있습니다.</span>
                                                    </div>
                                                </div>

                                                <div class="text-gray-500 text-xs pt-2">
                                                    내용을 확인하신 후, 끝까지 스크롤하면 동의 버튼이 활성화됩니다.
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="px-5 py-4 border-t bg-gray-50 flex gap-3">
                                <button type="button"
                                        class="flex-1 py-3 rounded-xl bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300"
                                        @click="cancel()">
                                    취소
                                </button>

                                <!-- Page 1~4: Next -->
                                <template x-if="termsPage < 5">
                                    <button type="button"
                                            :disabled="!canProceed"
                                            :class="!canProceed
                                              ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                              : 'bg-gray-200 text-gray-700 hover:bg-blue-500 hover:text-white'"
                                            class="flex-1 py-3 rounded-xl font-semibold transition-colors"
                                            @click="nextPage()">
                                        다음
                                    </button>
                                </template>

                                <!-- Page 5: Agree -->
                                <template x-if="termsPage === 5">
                                    <button type="button"
                                            :disabled="!canProceed"
                                            :class="!canProceed
                                              ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                              : 'bg-gray-200 text-gray-700 hover:bg-blue-500 hover:text-white'"
                                            class="flex-1 py-3 rounded-xl font-semibold transition-colors"
                                            @click="agree()">
                                        동의
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="mt-4 text-center text-sm text-gray-600">
                    이미 계정이 있나요?
                    <a href="{{ route('login') }}" class="text-blue-600 hover:underline">로그인</a>
                </div>

                @if(config('help.video_url'))
                    <div class="mt-3 text-center text-sm">
                        <a href="{{ config('help.video_url') }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-blue-500 hover:text-white transition-colors">
                            사용법(영상) 보기
                        </a>
                    </div>
                @endif
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


