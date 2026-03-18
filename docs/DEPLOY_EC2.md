# EC2 Deployment Guide

This guide is written for a first deployment on a single EC2 instance.

## 1. Before You Start

- Prepare an Ubuntu EC2 instance.
- Open inbound ports:
  - `22` for SSH
  - `80` for HTTP
  - `443` for HTTPS
- Point `gc-math.co.kr` to the EC2 public IP.

## 2. Install Required Packages

```bash
sudo apt update
sudo apt install -y nginx mysql-server php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-intl unzip git composer nodejs npm certbot python3-certbot-nginx
```

Adjust the PHP package version if your EC2 image provides a different one.

## 3. Put the Project on the Server

```bash
cd /var/www
sudo git clone <your-repository-url> remit
sudo chown -R $USER:$USER /var/www/remit
cd /var/www/remit
```

## 4. Create the Production Env File

```bash
cp .env.ec2.example .env
php artisan key:generate
```

Then edit `.env` and fill in the real values:

- `APP_URL`
- `DB_*`
- `MAIL_*`
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `ADMIN_EMAIL`
- `ADMIN_CONTACT_EMAIL`

For this project, use:

- `APP_URL=https://gc-math.co.kr`
- `SESSION_DOMAIN=gc-math.co.kr`
- `GOOGLE_REDIRECT_URL=${APP_URL}/auth/google/callback`

Recommended production values:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_FORCE_HTTPS=true`
- `SESSION_SECURE_COOKIE=true`

If you want only specific networks to access the service:

- `INTERNAL_IP_FILTER_ENABLED=true`
- `INTERNAL_IP_ALLOWED_CIDRS=127.0.0.1,::1,1.2.3.4/32`

## 5. Install App Dependencies

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

## 6. Create MySQL Database

```bash
sudo mysql
```

```sql
CREATE DATABASE remit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'remit_user'@'localhost' IDENTIFIED BY 'change_this_database_password';
GRANT ALL PRIVILEGES ON remit.* TO 'remit_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Make sure the DB values in `.env` match what you created.

## 7. Run Laravel Setup

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Because this app uses database-backed session, cache, and queue, the migrations are required before serving traffic.

## 8. Set Directory Permissions

```bash
sudo chown -R www-data:www-data /var/www/remit
sudo find /var/www/remit/storage -type d -exec chmod 775 {} \\;
sudo find /var/www/remit/bootstrap/cache -type d -exec chmod 775 {} \\;
```

## 9. Configure Nginx

Create `/etc/nginx/sites-available/remit`:

```nginx
server {
    listen 80;
    server_name gc-math.co.kr;

    root /var/www/remit/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable it:

```bash
sudo ln -s /etc/nginx/sites-available/remit /etc/nginx/sites-enabled/remit
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
```

## 10. Enable HTTPS

```bash
sudo certbot --nginx -d gc-math.co.kr
```

After HTTPS is enabled, keep these values in `.env`:

- `APP_URL=https://gc-math.co.kr`
- `APP_FORCE_HTTPS=true`
- `SESSION_SECURE_COOKIE=true`

## 11. Add the Scheduler

Open crontab:

```bash
crontab -e
```

Add:

```cron
* * * * * cd /var/www/remit && php artisan schedule:run >> /dev/null 2>&1
```

This is required because the app schedules `reservations:purge-old` every day.

## 12. Optional Queue Worker

If you keep `QUEUE_CONNECTION=database`, run a worker with systemd.

Example service file: `/etc/systemd/system/remit-queue.service`

```ini
[Unit]
Description=Remit Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/remit/artisan queue:work --sleep=3 --tries=1
WorkingDirectory=/var/www/remit

[Install]
WantedBy=multi-user.target
```

Then enable it:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now remit-queue
```

## 13. Final Checks

Run these after deployment:

```bash
php artisan about
php artisan migrate:status
php artisan config:show mail
```

Then test:

- login
- registration email
- password reset email
- inquiry email
- admin page access

## 14. Secret Safety

If you previously stored real passwords or API secrets in `.env` on your local machine, rotate them before production use:

- database password
- mail app password
- Google client secret
- any copied `APP_KEY` used outside your machine
