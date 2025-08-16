FROM php:8.2-cli

# ติดตั้ง extension ที่ Laravel ต้องใช้
RUN apt-get update && apt-get install -y unzip git libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip bcmath

# ติดตั้ง Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# คัดลอกไฟล์ Laravel ทั้งหมด
COPY . .

# ติดตั้ง dependency
RUN composer install --no-dev --optimize-autoloader

# ตั้งสิทธิ์ให้ storage และ bootstrap/cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# เปิด port 8080
EXPOSE 8080

# รัน Laravel จาก public
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
