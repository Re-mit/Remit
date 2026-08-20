# Remit

> 학과 공용 스터디룸(622호) 예약 및 관리 시스템

이메일/비밀번호로 로그인하여 회의실을 예약하고 관리할 수 있는 웹 애플리케이션입니다.

---

## 📋 목차

- [주요 기능](#-주요-기능)
- [기술 스택](#-기술-스택)
- [프로젝트 구조](#-프로젝트-구조)
- [설치 및 실행](#-설치-및-실행)
- [환경 설정](#-환경-설정)
- [관리자 통계](#-관리자-통계)
- [사용자 행동 모니터링](#-사용자-행동-모니터링)
- [운영 배포](#-운영-배포)
- [데이터베이스 스키마](#-데이터베이스-스키마)
- [라우트 구조](#-라우트-구조)
- [문제 해결](#-문제-해결)

---

## ✨ 주요 기능

### 🔐 인증
- **이메일/비밀번호 회원가입 & 로그인**
  - `@gachon.ac.kr` 이메일만 가입/로그인 허용
  - 이메일 인증 코드 및 비밀번호 재설정 지원

### 📅 예약 관리
- **예약 생성**
  - 날짜/시간 선택
  - 좌석 선택 및 여러 시간 구간 예약
  - 다중 사용자 참여 (공동 예약)
- **내 예약 조회**
  - 내가 생성한 예약 목록 확인
  - 예약 상세 정보 보기
- **예약 취소**
  - 불필요한 예약 삭제

### 🏠 회의실 관리
- **622호 스터디룸** 예약 지원
- 회의실별 예약 내역 추적
- 향후 다른 회의실 추가 가능한 구조

### 👤 마이페이지
- 사용자 프로필 정보 확인

### 🔔 알림
- 공지, 페널티 및 예약 관련 알림 제공

### 🛡️ 관리자
- 회원, 허용 이메일, 관리자 및 페널티 관리
- 예약 현황·취소 내역 및 열쇠함 URL 관리
- 공지사항과 서비스 개선 요청 관리
- 7일/30일 기준 회원·예약 사용 통계 제공

### 📊 사용자 행동 분석
- Microsoft Clarity를 통한 운영 환경의 세션 기록과 히트맵 수집
- 로컬 환경에서는 추적하지 않으며, 민감 정보는 Clarity 마스킹 설정으로 보호

---

## 🛠 기술 스택

### Backend
| 기술 | 버전 | 용도 |
|------|------|------|
| PHP | 7.4+ | 서버사이드 언어 (`composer.json` 기준) |
| Laravel | 8.x | PHP 프레임워크 |
| MySQL | 8.0+ | 관계형 데이터베이스 |

### Frontend
| 기술 | 용도 |
|------|------|
| Blade | Laravel 템플릿 엔진 |
| Webpack (Laravel Mix) | 프론트엔드 빌드 도구 |
| Tailwind CSS | 스타일링 |
| Alpine.js / JavaScript | 화면 상태 및 클라이언트 스크립트 |

### Development
| 도구 | 용도 |
|------|------|
| Composer | PHP 의존성 관리 |
| npm | JS 의존성 관리 |
| Git | 버전 관리 |

---

## 📁 프로젝트 구조

```
Remit/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php              # 회원가입/로그인(세션)
│   │       ├── ReservationController.php       # 예약 CRUD
│   │       ├── MypageController.php            # 마이페이지
│   │       ├── NotificationController.php      # 알림
│   │       └── AdminController.php             # 관리자 및 통계
│   ├── Models/
│   │   ├── User.php                            # 사용자 모델
│   │   ├── Room.php                            # 회의실 모델
│   │   ├── Reservation.php                     # 예약 모델
│   │   ├── ReservationUser.php                 # 예약-사용자 연결
│   │   └── UsageLog.php                        # 사용 기록
│   └── Providers/
│       └── AppServiceProvider.php
├── config/
│   ├── auth.php                                # 인증 설정
│   ├── database.php                            # DB 설정
│   ├── services.php                            # 외부 서비스 설정
│   └── ...                                     # 기타 Laravel 설정
├── database/
│   ├── migrations/                             # 데이터베이스 스키마
│   │   ├── *_create_users_table.php
│   │   ├── *_add_google_fields_to_users_table.php
│   │   ├── *_create_rooms_table.php
│   │   ├── *_create_reservations_table.php
│   │   ├── *_create_reservation_users_table.php
│   │   ├── *_create_usage_logs_table.php
│   │   ├── *_create_cache_table.php
│   │   └── *_create_jobs_table.php
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   └── RoomSeeder.php                      # 622호 초기 데이터
│   └── factories/
│       └── UserFactory.php
├── public/
│   ├── index.php                               # 애플리케이션 진입점
│   └── ...
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   └── app.blade.php                   # 기본 레이아웃
│   │   ├── admin/
│   │   │   └── statistics.blade.php            # 관리자 사용 통계
│   │   ├── auth/
│   │   │   └── login.blade.php                 # 로그인 페이지
│   │   ├── reservation/
│   │   │   ├── index.blade.php                 # 예약하기
│   │   │   └── my.blade.php                    # 내 예약
│   │   ├── mypage/
│   │   │   └── index.blade.php                 # 마이페이지
│   │   └── notification/
│   │       └── index.blade.php                 # 알림
│   ├── css/
│   │   └── app.css
│   └── js/
│       ├── app.js
│       └── bootstrap.js
├── routes/
│   ├── web.php                                 # 웹 라우트 정의
│   └── console.php
├── storage/                                    # 로그, 캐시, 세션 등
├── tests/                                      # 테스트 코드
├── .env                                        # 환경 변수 (Git 제외)
├── .gitignore
├── composer.json                               # PHP 의존성
├── package.json                                # JS 의존성
├── webpack.mix.js                              # Webpack(Mix) 설정
└── README.md
```

---

## 🚀 설치 및 실행

### 사전 요구사항
- PHP 7.4 이상
- Composer
- MySQL 8.0 이상
- Node.js 18+ (npm 권장)

---

### 1️⃣ 저장소 클론
```bash
git clone <repository-url>
cd Remit
```

---

### 2️⃣ 의존성 설치
```bash
# PHP 의존성
composer install

# JavaScript 의존성
npm install
```

---

### 3️⃣ 환경 설정 파일 생성
```bash
cp .env.example .env
php artisan key:generate
```

---

### 4️⃣ 데이터베이스 설정

#### MySQL 데이터베이스 생성
```bash
mysql -u root -p
```

MySQL 쉘에서
```sql
CREATE DATABASE remit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

#### `.env` 파일 수정
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=remit
DB_USERNAME=root
DB_PASSWORD=your_password
```

---

### 5️⃣ 마이그레이션 및 시딩
```bash
php artisan migrate --seed
```

이 명령어는:
- 모든 테이블 생성 (users, rooms, reservations 등)
- 622호 스터디룸 초기 데이터 삽입

---

### 6️⃣ 회원가입/로그인

브라우저에서 아래로 접속해 계정을 생성/로그인합니다.
- 회원가입: `/register`
- 로그인: `/login`

> 참고: 과거 Google OAuth로 생성된 계정이 DB에 남아있더라도, 해당 이메일로 `/register`에서 비밀번호를 설정하면(비밀번호가 비어있는 경우) 동일 계정으로 로그인할 수 있습니다.

---

### 7️⃣ 프론트엔드 에셋 빌드
```bash
npm run build
```

개발 중에는:
```bash
npm run dev
```

---

### 8️⃣ 서버 실행

#### 로컬에서만 실행 (localhost)
```bash
php artisan serve
```

브라우저에서 **http://localhost:8000** 접속

#### 모바일에서도 접속 가능하게 실행 (권장)
```bash
./start.sh
```

이 스크립트는:
- 서버를 `0.0.0.0:8000`에 바인딩 (모든 네트워크 인터페이스)
- 로컬 IP 주소를 자동으로 감지하여 표시
- 모바일 접속 안내 메시지 출력

**출력 예시:**
```
📍 Server will be accessible at:
   - Local:   http://localhost:8000
   - Network: http://172.30.1.85:8000

📱 To access from mobile device:
   1. Connect your phone to the same Wi-Fi network
   2. Open browser and go to: http://172.30.1.85:8000
```

---

### 📱 모바일 기기에서 접속하기

#### 1️⃣ 로컬 IP 주소 확인
서버를 실행하면 `start.sh`가 자동으로 표시합니다.
수동으로 확인하려면:
```bash
# macOS
ipconfig getifaddr en0

# 또는
ifconfig | grep "inet " | grep -v 127.0.0.1
```

#### 2️⃣ `.env` 파일 수정
```env
APP_URL=http://172.30.1.85:8000  # 실제 로컬 IP로 변경
```

#### 3️⃣ 모바일에서 접속
- 같은 Wi-Fi에 연결된 모바일 기기에서
- 브라우저로 `http://172.30.1.85:8000` 접속

> ⚠️ **주의**: IP 주소는 네트워크에 따라 바뀔 수 있습니다. Wi-Fi가 변경되면 위 단계를 다시 수행하세요.

---

## ⚙️ 환경 설정

### `.env.example` 파일 복사
```bash
cp .env.example .env
php artisan key:generate
```

### 주요 환경 변수 (`.env`)

#### 애플리케이션 설정
```env
APP_NAME=Remit
APP_ENV=local
APP_KEY=                          # php artisan key:generate로 자동 생성
APP_DEBUG=true
APP_TIMEZONE=Asia/Seoul
APP_URL=http://localhost:8000

APP_LOCALE=ko
APP_FALLBACK_LOCALE=en
```

#### 데이터베이스 설정
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=remit
DB_USERNAME=root
DB_PASSWORD=                      # MySQL 비밀번호 (없으면 비워두기)
```

#### 세션 및 캐시 설정
```env
SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=database
QUEUE_CONNECTION=database
```

#### 선택적 설정
```env
# 특정 키워드가 포함된 사용자만 허용 (예: "철학과")
ALLOWED_USER_FILTER=

# 관리자 계정과 문의 메일 수신자
ADMIN_EMAIL=admin@example.com
ADMIN_CONTACT_EMAIL=admin@example.com

# 로그인/회원가입 화면에 노출되는 "사용법(영상)"(유튜브) 링크
HELP_VIDEO_URL=

# 내부 IP만 접근시키려는 경우
INTERNAL_IP_FILTER_ENABLED=false
INTERNAL_IP_ALLOWED_CIDRS=127.0.0.1,::1
```

#### 메일 설정
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 📈 관리자 통계

관리자 계정은 `/admin/statistics`에서 실제 DB의 회원 및 예약 현황을 확인할 수 있습니다.

- 조회 기간: 최근 7일 또는 30일
- 회원: 전체 일반 회원, 기간 신규 회원, 기간 예약 이용자
- 예약: 전체·유효·취소 예약, 취소율 및 취소 주체
- 이용: 재이용자, 예약당 평균 참여자, 인기 시간대와 공간
- 추이: 7일은 일별, 30일은 주별 집계

예약 지표는 선택 기간 안에 **시작하는 예약의 `start_at`**을 기준으로 계산합니다. 외부 분석 도구의 방문자 수와 실제 예약 수는 정의가 다르므로 운영 지표는 이 페이지를 기준으로 확인합니다.

---

## 🔎 사용자 행동 모니터링

공통 레이아웃 `resources/views/layouts/app.blade.php`에 Microsoft Clarity가 설치되어 있습니다.

- Clarity Project ID: `y58auzrycx`
- `@production` 조건으로 운영 환경에서만 실행
- 결과 확인: [Microsoft Clarity](https://clarity.microsoft.com/)의 Dashboard, Recordings, Heatmaps
- 설치 이후의 방문부터 수집되며 과거 데이터는 소급되지 않음
- 회원 정보와 예약 값 보호를 위해 Clarity의 **Masking** 설정 유지 권장

Clarity에는 이메일, 학번 등 직접 식별자를 사용자 ID나 커스텀 값으로 전송하지 않습니다. 실제 회원·예약 수치는 자체 관리자 통계에서 확인하고, Clarity는 클릭·스크롤·이탈 흐름 분석에 사용합니다.

---

## 🌐 운영 배포

현재 운영 환경은 가비아 PHP 웹호스팅이며, FileZilla에서 보이는 원격 `/`가 애플리케이션 경로 `/www_root`에 해당합니다.

### 배포 전 준비

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

서버에서 Composer 또는 npm을 실행할 수 없다면 로컬에서 준비한 `vendor`와 `public/css`, `public/js`, `public/mix-manifest.json`을 함께 배포합니다. `node_modules`, `tests`, Docker 관련 파일은 운영 실행에 필요하지 않습니다.

### 필수 배포 주의사항

- 서버 루트의 `composer.json`은 Blade 컴포넌트의 애플리케이션 네임스페이스 판별에 필요하므로 반드시 유지합니다.
- `composer.lock`도 함께 배포해 운영 의존성 버전을 고정합니다.
- 로컬 `.env`를 운영 서버에 업로드하지 않습니다. 운영 `.env`는 서버에서 별도로 관리하고 Git에 커밋하지 않습니다.
- `database/migrations` 파일을 올리는 것만으로 DB가 변경되지는 않습니다.
- 마이그레이션 전 DB를 백업한 다음 `php artisan migrate --force`를 실행합니다.
- `storage`와 `bootstrap/cache`는 PHP 프로세스가 쓸 수 있어야 합니다.

### 운영 `.env` 핵심값

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_FORCE_HTTPS=true

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
```

HTTPS 인증서가 정상 적용되기 전에는 `APP_FORCE_HTTPS=false`, `SESSION_SECURE_COOKIE=false`로 운영하고, 인증서 적용 직후 HTTPS 설정으로 전환합니다.

배포 후 서버에서 실행할 수 있다면 다음 캐시 정리를 수행합니다.

```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

SSH를 사용할 수 없다면 FileZilla에서 `storage/framework/views` 안의 생성된 `.php` 파일만 제거해 뷰 캐시를 갱신할 수 있습니다. 디렉터리 자체와 `.gitignore`는 유지합니다.

---

## 🗄 데이터베이스 스키마

### `users` - 사용자
| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint | PK |
| name | string | 사용자 이름 |
| email | string | 이메일 (unique) |
| google_id | string | 과거 Google OAuth 호환 ID (nullable, unique) |
| password | string | 해시된 비밀번호 (nullable) |
| role | string | `user` 또는 `admin` |
| warning | integer | 경고 횟수 |
| student_id | string | 학번 (nullable) |
| terms_agreed_at | timestamp | 약관 동의 시각 (nullable) |
| suspended_at | timestamp | 이용 정지 시각 (nullable) |
| remember_token | string | 로그인 유지 토큰 |
| created_at | timestamp | 생성 시간 |
| updated_at | timestamp | 수정 시간 |

---

### `rooms` - 회의실
| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint | PK |
| name | string | 회의실 이름 (예: "622호") |
| description | text | 회의실 설명 |
| created_at | timestamp | 생성 시간 |
| updated_at | timestamp | 수정 시간 |

---

### `reservations` - 예약
| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint | PK |
| room_id | bigint | FK (예약한 회의실) |
| seat_id | bigint | FK (선택 좌석, nullable) |
| seat_no | integer | 레거시 좌석 번호 (nullable) |
| group_member_emails | json | 공동 예약자 이메일 (nullable) |
| start_at | datetime | 예약 시작 시간 |
| end_at | datetime | 예약 종료 시간 |
| key_code | string | 열쇠함 코드 |
| status | string | `confirmed` 또는 `cancelled` |
| cancelled_at | datetime | 취소 시각 (nullable) |
| cancelled_by | string | `user` 또는 `admin` (nullable) |
| cancel_reason | text | 취소 사유 (nullable) |
| created_at | timestamp | 생성 시간 |
| updated_at | timestamp | 수정 시간 |

**관계:**
- `room_id` → `rooms.id`
- `seat_id` → `seats.id`
- 예약 참여자는 `reservation_users` 피벗 테이블을 통해 `users`와 연결

---

### `reservation_users` - 예약 참여자 (Pivot)
| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint | PK |
| reservation_id | bigint | FK (예약) |
| user_id | bigint | FK (참여 사용자) |
| is_representative | boolean | 대표 예약자 여부 |
| created_at | timestamp | 생성 시간 |
| updated_at | timestamp | 수정 시간 |

**관계:**
- `reservation_id` → `reservations.id`
- `user_id` → `users.id`

**용도:** 한 예약에 여러 사용자가 참여할 수 있도록 연결

---

### `usage_logs` - 사용 기록
| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint | PK |
| user_id | bigint | FK (사용자) |
| reservation_id | bigint | FK (예약, nullable) |
| entry_at | datetime | 입실 시간 |
| exit_at | datetime | 퇴실 시간 (nullable) |
| duration | integer | 사용 시간(초, nullable) |
| created_at | timestamp | 생성 시간 |
| updated_at | timestamp | 수정 시간 |

**관계:**
- `user_id` → `users.id`
- `reservation_id` → `reservations.id`

**용도:** 회의실 입/퇴실 이력 추적

---

### 추가 시스템 테이블
- `seats` - 공간별 좌석
- `notifications`, `notices` - 사용자 알림과 공지
- `allowed_emails`, `email_verification_codes` - 가입 허용 및 이메일 인증
- `lockbox_urls` - 열쇠함 URL
- `error_reports` - 서비스 개선 요청
- `sessions`, `password_reset_tokens` - 인증 및 세션
- `cache`, `cache_locks` - 데이터베이스 기반 캐시
- `jobs`, `job_batches`, `failed_jobs` - 큐 작업 관리

---

## 🔐 라우트 구조

### 공개 라우트 (인증 불필요)
| Method | URI | 설명 |
|--------|-----|------|
| GET | `/` | 루트 (로그인으로 리다이렉트) |
| GET | `/login` | 로그인 페이지 |
| GET | `/register` | 회원가입 페이지 |
| POST | `/register/send-code` | 이메일 인증 코드 발송 |
| POST | `/register/verify-code` | 이메일 인증 코드 확인 |
| GET | `/forgot-password` | 비밀번호 재설정 요청 |

### 인증 필요 라우트 (`auth` 미들웨어)
| Method | URI | 설명 |
|--------|-----|------|
| GET | `/reservation` | 예약하기 페이지 |
| POST | `/reservation` | 예약 생성 |
| POST | `/reservation/available-seats` | 예약 가능 좌석 조회 |
| DELETE | `/reservation/{id}` | 예약 삭제 |
| GET | `/reservation/my` | 내 예약 조회 |
| GET | `/mypage` | 마이페이지 |
| GET | `/notifications` | 알림 |
| POST | `/logout` | 로그아웃 |

### 관리자 라우트
| Method | URI | 설명 |
|--------|-----|------|
| GET | `/admin` | 관리자 대시보드 |
| GET | `/admin/statistics` | 회원·예약 사용 통계 |
| GET | `/admin/users` | 회원 및 허용 이메일 관리 |
| GET | `/admin/reservations` | 현재 예약 관리 |
| GET | `/admin/reservations/history` | 예약·취소 이력 |
| GET | `/admin/notices` | 공지 관리 |
| GET | `/admin/error-reports` | 서비스 개선 요청 관리 |

관리자 라우트는 인증 후 접근하며, 각 관리자 액션에서 관리자 권한을 추가로 확인합니다.

---

## 🐛 문제 해결

### MySQL 접속 오류
```bash
# MySQL 서비스 확인
mysql --version

# MySQL 시작
brew services start mysql  # macOS
sudo systemctl start mysql  # Linux

# root 비밀번호 재설정 (필요 시)
sudo mysql -u root
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'new_password';
FLUSH PRIVILEGES;
```

### 캐시 정리
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### `composer.json` 누락으로 인한 500 오류

다음 오류가 로그에 나타나면 서버 애플리케이션 루트에 `composer.json`을 다시 배포합니다.

```text
file_get_contents(/www_root/composer.json): failed to open stream: No such file or directory
```

`composer.json`은 PHP 패키지 설치용일 뿐 아니라 Laravel이 Blade 클래스 컴포넌트의 `App\\` 네임스페이스를 판별할 때도 사용합니다.

### 운영 오류 확인

브라우저의 `500 Internal Server Error`는 결과만 보여줍니다. 실제 원인은 서버의 `storage/logs/laravel.log`에서 가장 최근 `production.ERROR` 항목을 확인합니다. 운영 환경에서는 오류 상세가 사용자 화면에 노출되지 않도록 `APP_DEBUG=false`를 유지합니다.

---
