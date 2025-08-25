# İlisan E-Ticaret

Laravel 11 tabanlı güvenli ve ölçeklenebilir e-ticaret sistemi.

## Teknoloji Stack

-   **Backend**: PHP 8.2+, Laravel 11
-   **Frontend**: Blade Templates, Tailwind CSS, Alpine.js
-   **Admin Panel**: Filament 3
-   **Veritabanı**: PostgreSQL
-   **Cache**: Redis
-   **Queue**: Redis
-   **Konteynerizasyon**: Docker & Docker Compose
-   **Ödeme**: iyzico 3D Secure

## Özellikler

### ✅ Temel E-Ticaret

-   Ürün katalog yönetimi
-   Kategori bazlı organizasyon
-   Sepet yönetimi
-   Güvenli checkout süreci
-   Sipariş takip sistemi

### ✅ Kullanıcı Yönetimi

-   Kullanıcı kaydı ve girişi
-   İki faktörlü doğrulama (2FA)
-   Profil yönetimi
-   Adres defteri

### ✅ Admin Paneli

-   Filament 3 tabanlı modern arayüz
-   Ürün/kategori yönetimi
-   Sipariş yönetimi
-   Kullanıcı yönetimi
-   Raporlama ve analitik

### ✅ Güvenlik

-   CSRF koruması
-   Rate limiting
-   Güvenlik başlıkları
-   Input validation
-   Audit logging

### ✅ Ödeme Sistemi

-   iyzico 3D Secure entegrasyonu
-   Güvenli ödeme işleme
-   Webhook handling
-   Refund yönetimi

## Kurulum

### Docker ile Geliştirme Ortamı

```bash
# Projeyi klonla
git clone <repo-url> ilisan-eticaret
cd ilisan-eticaret

# Docker konteynerlerini başlat
make up

# Bağımlılıkları yükle
make composer install

# Uygulama anahtarını oluştur
make artisan key:generate

# Veritabanı migration'larını çalıştır
make migrate

# Admin kullanıcı oluştur
make artisan make:filament-user
```

### Manuel Kurulum

```bash
# Bağımlılıkları yükle
composer install
npm install

# Ortam dosyasını kopyala ve düzenle
cp .env.example .env
php artisan key:generate

# Veritabanını hazırla
php artisan migrate --seed

# Frontend assets'leri derle
npm run build

# Geliştirme sunucusunu başlat
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
