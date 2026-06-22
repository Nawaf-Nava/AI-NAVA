# --- [ المرحلة 1: بناء البيئة الأساسية ] ---
# استخدام صورة PHP 8.2 الرسمية مع خادم Apache
FROM php:8.2-apache

# تحديث قائمة الحزم وتثبيت الأدوات الضرورية
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# --- [ المرحلة 2: تثبيت إضافات PHP المطلوبة ] ---
# تثبيت الإضافات الأساسية التي يحتاجها NAVA AI
RUN docker-php-ext-install pdo_mysql zip curl gd mbstring

# --- [ المرحلة 3: نسخ ملفات المشروع ] ---
# نسخ جميع ملفات المشروع إلى مجلد الخادم
COPY . /var/www/html/

# --- [ المرحلة 4: ضبط الصلاحيات ] ---
# منح Apache صلاحيات الكتابة على المجلدات التي تحتاجها
RUN chown -R www-data:www-data /var/www/html/vault /var/www/html/images/uploads