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
- [데이터베이스 스키마](#-데이터베이스-스키마)

---

## ✨ 주요 기능

### 🔐 인증
- **이메일/비밀번호 회원가입 & 로그인**
  - `@gachon.ac.kr` 이메일만 가입/로그인 허용

### 📅 예약 관리
- **예약 생성**
  - 날짜/시간 선택
  - 예약 목적 입력
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
- 예약 관련 알림 수신 (예정)

---

## 🛠 기술 스택

### Backend
| 기술 | 버전 | 용도 |
|------|------|------|
| PHP | 8.2+ | 서버사이드 언어 |
| Laravel | 12.x | PHP 프레임워크 |
| MySQL | 8.0+ | 관계형 데이터베이스 |

### Frontend
| 기술 | 용도 |
|------|------|
| Blade | Laravel 템플릿 엔진 |
| Webpack (Laravel Mix) | 프론트엔드 빌드 도구 |
| Vanilla CSS | 스타일링 |
| Vanilla JS | 클라이언트 스크립트 |

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
│   │       └── NotificationController.php      # 알림
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
- PHP 8.2 이상
- Composer
- MySQL 8.0 이상
- Node.js 16+ (npm)

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

# 로그인/회원가입 화면에 노출되는 "사용법(영상)"(유튜브) 링크
HELP_VIDEO_URL=

# 문의하기 메일 수신자(관리자 이메일)
ADMIN_CONTACT_EMAIL=
```

---

## 🗄 데이터베이스 스키마

### `users` - 사용자
| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint | PK |
| name | string | 사용자 이름 |
| email | string | 이메일 (unique) |
| google_id | string | Google OAuth ID (unique) |
| profile_photo | string | 프로필 사진 URL |
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
| user_id | bigint | FK (예약한 사용자) |
| room_id | bigint | FK (예약한 회의실) |
| start_time | datetime | 예약 시작 시간 |
| end_time | datetime | 예약 종료 시간 |
| purpose | text | 예약 목적 |
| created_at | timestamp | 생성 시간 |
| updated_at | timestamp | 수정 시간 |

**관계:**
- `user_id` → `users.id`
- `room_id` → `rooms.id`

---

### `reservation_users` - 예약 참여자 (Pivot)
| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint | PK |
| reservation_id | bigint | FK (예약) |
| user_id | bigint | FK (참여 사용자) |
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
| reservation_user_id | bigint | FK (예약 참여자) |
| entry_time | datetime | 입실 시간 |
| exit_time | datetime | 퇴실 시간 (nullable) |
| created_at | timestamp | 생성 시간 |
| updated_at | timestamp | 수정 시간 |

**관계:**
- `reservation_user_id` → `reservation_users.id`

**용도:** 회의실 입/퇴실 이력 추적

---

### 추가 시스템 테이블
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

### 인증 필요 라우트 (`auth` 미들웨어)
| Method | URI | 설명 |
|--------|-----|------|
| GET | `/reservation` | 예약하기 페이지 |
| POST | `/reservation` | 예약 생성 |
| DELETE | `/reservation/{id}` | 예약 삭제 |
| GET | `/reservation/my` | 내 예약 조회 |
| GET | `/mypage` | 마이페이지 |
| GET | `/notifications` | 알림 |
| POST | `/logout` | 로그아웃 |

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

---
