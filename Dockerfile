FROM php:8.2-cli

# ติดตั้ง extension ที่ Laravel ต้องใช้
RUN apt-get update && apt-get install -y unzip git libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip bcmath

# ติดตั้ง Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# คัดลอกไฟล์ทั้งหมดเข้า container
WORKDIR /app
COPY . .

# ติดตั้ง dependency
RUN composer install --no-dev --optimize-autoloader

# เปิด port
EXPOSE 8080

# รัน Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
