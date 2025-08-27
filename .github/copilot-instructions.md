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
-   **Error Handling**: Global exception handler, structured logging
-   **Monitoring**: Health checks, performance metrics, error tracking
-   **Backup**: Automated database backups, file storage backups
-   **Logging**: Structured logs, request/response logging, audit trails
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

### Error Handling & Monitoring:

-   Global exception handler ile structured error logging
-   User-friendly error pages (404, 500, 503)
-   API error responses standardized format
-   Health check endpoints (/health, /api/health)
-   Performance monitoring ve APM entegrasyonu
-   Database query monitoring ve slow query alerts
-   Memory leak detection ve resource monitoring

### Backup & Recovery:

-   Automated daily database backups
-   File storage backup strategies
-   Backup verification ve restore testing
-   Point-in-time recovery capabilities
-   Disaster recovery procedures documented

## 🌐 E-Ticaret Özellikleri

### 🛡️ Ürün Kategorileri:

-   **Çelik Yelek**: Farklı koruma seviyeleri, boyut ve renk seçenekleri
-   **Askeri Teçhizat**: Taktik ekipmanlar, güvenlik malzemeleri
-   **Yakın Koruma Kıyafetleri**: Özel tasarım gömlek, pantolon, takım elbise
-   **Güvenlik Aksesuarları**: Kemik, eldiven, şapka, ayakkabı

### 🎨 Renk ve Varyant Sistemi:

-   **Varyant Attributes**: `{"color": "Siyah", "size": "L", "material": "Kevlar"}`
-   **Renk Bazlı Fotoğraflar**: Her renk için ayrı image gallery
-   **SEO Friendly URLs**: `/celik-yelek/siyah-l-kevlar`
-   **Varyant Naming**: "Çelik Yelek - Siyah L Kevlar"

### 🔍 SEO Otomasyonu:

-   **Auto Meta Title**: "{Ürün Adı} | {Kategori} | İlisan"
-   **Auto Meta Description**: "{Ürün Kısa Açıklama} {Renk} {Beden} seçenekleri ile..."
-   **Auto Slug Generation**: Türkçe karakterleri temizle, SEO friendly
-   **Schema.org**: Product, Offer, Review markup'ları
-   **Open Graph**: Sosyal medya paylaşım optimizasyonu

### 📸 Medya Yönetimi:

-   **Ürün Ana Fotoğrafları**: Genel tanıtım görselleri
-   **Varyant Fotoğrafları**: Her renk için özel fotoğraflar
-   **Multiple Images**: Her varyant için çoklu görsel
-   **Image Optimization**: WebP format, lazy loading
-   **Alt Text**: SEO için otomatik alt text oluşturma

### 🛒 E-Ticaret Akışı:

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
