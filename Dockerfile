# PHP 8.5.0 기반 이미지 사용
FROM php:8.5.0-cli-alpine AS base

# 필요한 시스템 패키지 설치
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    postgresql-dev \
    sqlite \
    sqlite-dev \
    mysql-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    icu-dev \
    nodejs \
    npm \
    supervisor

# PHP 확장 설치
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Composer 설치
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 작업 디렉토리 설정
WORKDIR /var/www/html

# 애플리케이션 파일 복사
COPY . .

# 의존성 설치
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm install \
    && npm run build

# 권한 설정
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# 개발 환경용 스테이지
FROM base AS development

# 개발 의존성 설치
RUN composer install --optimize-autoloader --no-interaction

# 프로덕션 환경용 스테이지
FROM base AS production

# .env 파일이 없으면 생성 (런타임에 설정 가능)
RUN if [ ! -f .env ]; then \
    cp .env.example .env 2>/dev/null || true; \
    fi

# PHP-FPM 설정
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Supervisor 설정 (필요한 경우)
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000

CMD ["php-fpm"]

