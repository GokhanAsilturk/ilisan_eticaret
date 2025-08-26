# Copilot Talimatları

<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

Bu proje Laravel 11 tabanlı **İlisan E-Ticaret** sistemidir. Proje özellikleri:

## 🛠️ Teknoloji Stack

-   **Backend**: PHP 8.2+, Laravel 11
-   **Frontend**: Blade Templates, Tailwind CSS, Alpine.js
-   **Admin Panel**: Filament 3
-   **Veritabanı**: PostgreSQL
-   **Cache & Queue**: Redis
-   **Ödeme**: iyzico 3D Secure entegrasyonu
-   **Konteynerizasyon**: Docker & Docker Compose
-   **Test**: PHPUnit, Pest
-   **Kalite**: Laravel Pint, PHPStan/Larastan

## 🏗️ Proje Yapısı

-   **Models**: User, Product, Category, Order, Cart, Payment vb.
-   **Services**: PricingService, StockService, CartService, CheckoutService
-   **Admin**: Filament 3 resources ve widgets
-   **API**: RESTful endpoints
-   **Auth**: 2FA, email verification, role-based access

## 🎯 Güvenlik & Kalite

-   **Güvenlik**: CSRF, Rate limiting, Input validation, Security headers
-   **Mimari**: Service pattern, Repository pattern (gerektiğinde)
-   **Dil**: Türkçe UI, İngilizce dokümantasyon
-   **Zaman Dilimi**: Europe/Istanbul
-   **Para Birimi**: TRY

## 📝 Kod Yazım Kuralları

### Laravel Standartları:

-   PSR-12 kod standartları kullan
-   Laravel naming conventions'a uygun ol
-   Eloquent relationships'leri doğru tanımla
-   Service class'ları için dependency injection kullan

### Güvenlik:

-   Tüm user input'ları validate et
-   SQL injection'a karşı Eloquent/Query Builder kullan
-   XSS koruması için blade escape'leri kullan
-   Authorization policies tanımla

### Performans:

-   Eager loading kullan (N+1 query problemini önle)
-   Cache'leme stratejileri uygula
-   Database indexleri optimize et
-   Queue kullanarak ağır işlemleri background'a al

### Test:

-   Feature testleri yaz (kullanıcı senaryoları)
-   Unit testleri yaz (business logic)
-   Test veritabanı olarak memory SQLite kullan
-   Factory'leri kullanarak test data oluştur

## 🌐 E-Ticaret Özellikleri

-   **Ürün Yönetimi**: Kategoriler, varyantlar, stok takibi
-   **Sipariş Süreci**: Sepet → Checkout → Ödeme → Kargo
-   **Kullanıcı Deneyimi**: Hesap yönetimi, sipariş takibi, adres defteri
-   **Admin Panel**: Sipariş yönetimi, ürün CRUD, raporlar

## 🎨 Frontend Kuralları

-   **Blade**: Component'lar kullan, partial'lara böl
-   **Tailwind CSS**: Utility-first yaklaşım
-   **Alpine.js**: Minimal JavaScript etkileşimleri
-   **Responsive**: Mobile-first tasarım

## 💳 İyzico Entegrasyonu

-   3D Secure akışı doğru implementasyonu
-   Webhook handling ve signature verification
-   Error handling ve retry logic
-   Payment status mapping

Kod yazarken:

-   **Türkçe**: UI metinleri, hata mesajları, validation mesajları
-   **İngilizce**: Kod comments(çok gerekmedikçe yorum satırı yazma), commit mesajları, dokümantasyon
-   **Security-first**: Her zaman güvenli kod yaz
-   **Performance-aware**: Performans etkileyici kod yazmaktan kaçın
-   **Test-driven**: Yazdığın kodu test etmeyi unutma

## 🗃️ Veritabanı Kuralları

-   Migration'larda foreign key constraints kullan
-   Index'leri unutma
-   Soft delete'ler için Laravel traits kullan
-   Audit log için model events kullan

## 🔄 API Kuralları

-   RESTful endpoint'ler oluştur
-   API Resource'ları kullan
-   Rate limiting uygula
-   Proper HTTP status code'ları döndür
-   API versioning uygula
