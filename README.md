# İlisan E-Ticaret Backend API

Laravel 11 tabanlı güvenli ve ölçeklenebilir **backend API** e-ticaret sistemi. Bu proje sadece API sağlar, frontend ayrı bir proje olarak geliştirilir.

## Teknoloji Stack

-   **Backend**: PHP 8.2+, Laravel 11
-   **API**: RESTful API with Laravel Sanctum
-   **Admin Panel**: Filament 3
-   **Veritabanı**: PostgreSQL
-   **Cache**: Redis
-   **Queue**: Redis
-   **Konteynerizasyon**: Docker & Docker Compose
-   **Ödeme**: iyzico 3D Secure API

## Özellikler

### ✅ Backend API

-   RESTful API endpoints
-   Laravel Sanctum authentication
-   Product/Category management API
-   Cart & Checkout API
-   Order management API
-   User management API
-   Media upload/management API

### ✅ Kullanıcı Yönetimi

-   User registration/login API
-   Token-based authentication
-   Profile management API
-   Address management API

### ✅ Admin Paneli

-   Filament 3 tabanlı modern arayüz
-   API için admin dashboard
-   Ürün/kategori yönetimi
-   Sipariş yönetimi
-   Kullanıcı yönetimi
-   Raporlama ve analitik

### ✅ Güvenlik

-   API rate limiting
-   Input validation
-   Security headers
-   Audit logging
-   CORS configuration

### ✅ Ödeme Sistemi

-   iyzico 3D Secure API entegrasyonu
-   Payment webhook handlers
-   Secure payment processing API
-   Refund management API

## Kurulum

### Docker ile Backend API Kurulumu

```bash
# Projeyi klonla
git clone <repo-url> ilisan-eticaret-api
cd ilisan-eticaret-api

# Docker konteynerlerini başlat
make up

# Bağımlılıkları yükle
make composer install

# Uygulama anahtarını oluştur
make artisan key:generate

# Veritabanı migration'larını çalıştır
make migrate

# Admin kullanıcı oluştur (Filament admin)
make artisan make:filament-user
```

### Manuel Backend Kurulum

```bash
# Bağımlılıkları yükle
composer install

# Ortam dosyasını kopyala ve düzenle
cp .env.example .env
php artisan key:generate

# Veritabanını hazırla
php artisan migrate --seed

# API server'ı başlat
php artisan serve
```

## Makefile Komutları

```bash
make up          # Docker konteynerlerini başlat
make down        # Docker konteynerlerini durdur
make logs        # Konteyner loglarını göster
make test        # Testleri çalıştır
make cs          # Code style kontrolü
make stan        # Static analysis
make fix         # Code style ve static analysis düzeltmeleri
```

## Ortam Ayarları

Temel `.env` konfigürasyonu:

```env
APP_NAME="İlisan E-Ticaret"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE="Europe/Istanbul"
APP_LOCALE=tr

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=ilisan_eticaret
DB_USERNAME=postgres
DB_PASSWORD=password

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## API Dokümantasyonu

API dokümantasyonu `/docs` endpoint'inde mevcuttur.

## Test

```bash
# Tüm testleri çalıştır
make test

# Belirli bir test sınıfını çalıştır
php artisan test Tests/Feature/ProductTest.php

# Coverage raporu
php artisan test --coverage
```

## Deployment

Production deployment için `deploy/` klasöründeki dokümanlara bakın.

## Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Değişikliklerinizi commit edin (`git commit -m 'Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için `LICENSE` dosyasına bakın.

## Güvenlik

Güvenlik açığı bildirmek için `security@ilisan.com.tr` adresine e-posta gönderin.

## Destek

-   📧 E-posta: support@ilisan.com.tr
-   📝 Dokümantasyon: `/docs`
-   🐛 Bug Reports: GitHub Issues

---

**İlisan E-Ticaret** - Güvenli, Hızlı, Ölçeklenebilir
