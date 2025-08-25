# E-Ticaret Projesi ToDo Listesi (PHP Laravel 11 + Copilot)

Sen kıdemli bir Laravel mimarı ve DevOps uzmanısın. 6 haftada **basit ama sağlam ve güvenli** bir e-ticaret sistemi geliştirmemi sağlayacaksın. PHP 8.2+, Laravel 11, VS Code kullanıyorum. Her adımda gerekli dosyaları oluştur, kod örnekleri ver, komutları listele ve bir sonraki adıma geçmeden önce kontrol listesi sun.

## Proje Özeti
- **Stack**: Laravel 11, Filament 3 (admin), PostgreSQL, Redis, Blade+Tailwind, Docker
- **Güvenlik**: CSRF, 2FA, güvenlik headers, rate limiting, input validation
- **Ödeme**: iyzico 3D Secure entegrasyonu
- **Operasyon**: Cloudflare DNS/SSL, günlük yedek, izleme
- **Dil**: Dokümantasyon İngilizce, UI Türkçe

---

## HAFTA 1: Proje Altyapısı

### [ ] 1.1 Proje Başlatma
- Laravel 11 projesi oluştur
- Git repo başlat, README.md yaz
- .gitignore, .editorconfig, .gitattributes ekle
- Composer script'leri ekle (test, cs, stan, fix)
- **Çıktı**: Temel proje yapısı, README dosyası

### [ ] 1.2 Docker Ortamı
- docker-compose.yml oluştur (nginx, php-fpm, postgres, redis, mailhog, minio)
- Dockerfile'lar yaz
- Makefile komutları ekle (up, down, logs, test, cs, stan)
- **Çıktı**: Çalışan Docker stack'i

### [ ] 1.3 VS Code Yapılandırması
- .vscode/settings.json oluştur
- .vscode/extensions.json ekle (Intelephense, PHP Debug, EditorConfig, GitLens)
- .vscode/launch.json (Xdebug pathMappings)
- **Çıktı**: VS Code konfigürasyonu

### [ ] 1.4 Kalite Araçları
- Pint, PHPCS (PSR-12) kur
- PHPStan/Larastan yüksek seviye kur
- Pest test framework kur
- İlk çalıştırmaları yap
- **Çıktı**: Kalite araçları kurulu ve çalışır

### [ ] 1.5 Ortam Yapılandırması
- .env.example doldur
- Local/stage/prod ayrımları ekle
- Temel Laravel ayarları (app name, timezone, locale)
- **Çıktı**: Yapılandırılmış ortam

---

## HAFTA 2: Veri Modeli ve Migrasyonlar

### [ ] 2.1 Core Modeller
Bu modelleri migrations ve factories ile oluştur:
- **User** (name, email, email_verified_at, password, remember_token, two_factor_secret, two_factor_recovery_codes)
- **Category** (name, slug, description, parent_id, is_active, sort_order)
- **Product** (name, slug, description, short_description, sku, is_active, meta_title, meta_description)
- **ProductVariant** (product_id, name, sku, price, compare_price, cost_price, inventory_quantity, weight, requires_shipping)
- **Inventory** (variant_id, quantity, reserved_quantity, available_quantity, low_stock_threshold)

### [ ] 2.2 E-ticaret Modelleri
- **Cart** (user_id, session_id, expires_at)
- **CartItem** (cart_id, variant_id, quantity, price)
- **Order** (user_id, order_number, status, total, subtotal, tax_total, shipping_total, currency)
- **OrderItem** (order_id, variant_id, quantity, price, total)
- **Address** (user_id, type, first_name, last_name, company, phone, address_line_1, address_line_2, city, state, postal_code, country)

### [ ] 2.3 Ödeme ve Kargo Modelleri
- **Payment** (order_id, gateway, gateway_transaction_id, status, amount, currency, gateway_response)
- **Shipment** (order_id, tracking_number, status, shipped_at, delivered_at, carrier, tracking_url)
- **Coupon** (code, type, value, minimum_amount, usage_limit, used_count, starts_at, expires_at, is_active)

### [ ] 2.4 Medya ve Yardımcı Modeller
- **Media** (mediable_type, mediable_id, filename, disk, mime_type, size, alt_text, title, sort_order)
- **AuditLog** (user_id, event, auditable_type, auditable_id, old_values, new_values, url, ip_address, user_agent)

### [ ] 2.5 Enum'lar ve İlişkiler
Enum'ları oluştur:
- **OrderStatus**: (pending, paid, processing, shipped, delivered, cancelled, refunded)
- **PaymentStatus**: (pending, authorized, captured, failed, refunded, cancelled)
- **ShipmentStatus**: (pending, processing, shipped, in_transit, delivered, exception)
- **CouponType**: (fixed, percentage)
- **AddressType**: (shipping, billing)

Model ilişkilerini kur ve factories/seeders yaz.

---

## HAFTA 3: Temel Servisler ve Storefront

### [ ] 3.1 Application Services
Bu servisleri oluştur:
- **PricingService**: vergi hesaplama, indirim uygulama, final price hesaplama
- **StockService**: stok kontrolü, rezervasyon, serbest bırakma
- **CartService**: ürün ekleme/çıkarma/güncelleme, cart temizleme
- **CheckoutService**: adres doğrulama → kargo hesaplama → ödeme hazırlık

### [ ] 3.2 Authentication & Authorization
- Laravel Breeze kur ve özelleştir
- 2FA (two-factor authentication) ekle
- Email verification akışı
- Password reset özelleştir
- User roles/permissions (Admin, Manager, Support, Customer)

### [ ] 3.3 Storefront Sayfaları (Blade + Tailwind)
- **Layout**: header, footer, navigation, search bar
- **Homepage**: featured products, categories
- **Category**: ürün listesi, filtreleme, sayfalama
- **Product**: detay sayfası, varyant seçimi, sepete ekleme
- **Search**: arama sonuçları, filtreler
- **Cart**: sepet görüntüleme, quantity update, remove

### [ ] 3.4 User Account Area
- **Dashboard**: sipariş özeti, recent orders
- **Profile**: bilgi güncelleme, password change, 2FA settings
- **Orders**: sipariş geçmişi, detay görüntüleme, tracking
- **Addresses**: adres defteri (shipping/billing)

---

## HAFTA 4: Checkout ve iyzico Entegrasyonu

### [ ] 4.1 Checkout Flow
Multi-step checkout oluştur:
- **Step 1**: Guest/Login seçimi, contact info
- **Step 2**: Shipping address, billing address
- **Step 3**: Shipping method seçimi, shipping cost hesaplama
- **Step 4**: Payment method, order review
- **Step 5**: Order confirmation

### [ ] 4.2 iyzico Payment Service
- **IyzicoPaymentService** oluştur
- 3D Secure checkout form implementasyonu
- Callback/webhook handlers (signature verification)
- Payment status mapping (iyzico → internal)
- Error handling ve retry logic
- Sandbox test kartları ile test

### [ ] 4.3 Payment Flow Implementation
- Payment form creation ve redirect
- Success/failure page handling
- Webhook endpoint güvenliği (IP whitelist, signature check)
- Idempotency key kullanımı
- Payment capture/void/refund endpoints

### [ ] 4.4 Order Management
- Order creation logic
- Stock reservation/release
- Order status transitions
- Email notifications (order confirmation, shipping)
- Invoice generation (basic PDF)

---

## HAFTA 5: Admin Panel ve İzleme

### [ ] 5.1 Filament Admin Setup
- Filament 3 kur ve yapılandır
- Admin user oluştur, 2FA enable et
- Dashboard widgets (orders, revenue, low stock)
- Navigation menü düzenle

### [ ] 5.2 Admin Resources
Bu Filament resources'ları oluştur:
- **ProductResource**: CRUD, variants, inventory management, media
- **CategoryResource**: tree structure, bulk actions
- **OrderResource**: status management, refund actions, view details
- **UserResource**: customer management, roles
- **CouponResource**: discount management
- **InventoryResource**: stock tracking, adjustments

### [ ] 5.3 Admin Actions & Bulk Operations
- Order status değiştirme (processing → shipped)
- Bulk inventory update
- CSV export (products, orders, customers)
- Refund processing
- Email template management

### [ ] 5.4 RBAC ve Audit Logging
- Filament policies oluştur
- Admin/Manager/Support role permissions
- Audit log tüm admin actions için
- Activity timeline görüntüleme

---

## HAFTA 6: Güvenlik, SEO ve Production Hazırlık

### [ ] 6.1 Security Hardening
- **Middleware**: Security headers (CSP, HSTS, X-Frame-Options, X-Content-Type-Options)
- **Rate Limiting**: login, checkout, API endpoints
- **Input Validation**: comprehensive form requests
- **CSRF Protection**: tüm forms için
- **Environment Security**: .env best practices, secret management

### [ ] 6.2 SEO ve Performance
- **Sitemap**: dinamik XML sitemap
- **Robots.txt**: search engine directives
- **Meta Tags**: dynamic title, description, OG tags
- **Structured Data**: Product, Offer, Breadcrumb schema
- **Image Optimization**: WebP conversion, lazy loading
- **Caching**: route, view, config cache

### [ ] 6.3 Email ve Notifications
- **Mail Templates**: order confirmation, shipping notification, refund
- **SMTP Configuration**: Mailhog (dev), Postmark (prod)
- **Queue System**: Redis queue for emails
- **Notification Preferences**: user opt-in/opt-out

### [ ] 6.4 Legal Pages ve KVKK
Türkçe sayfa templateları oluştur:
- **KVKK** (Kişisel Verilerin Korunması)
- **Privacy Policy** (Gizlilik Politikası)
- **Terms of Service** (Hizmet Şartları)
- **Distance Sales Agreement** (Mesafeli Satış Sözleşmesi)
- **Return Policy** (İade Politikası)
- **Cookie Policy** (Çerez Politikası)

---

## Production Deployment

### [ ] CI/CD Setup
- **GitHub Actions** workflow:
  - Build & Test (composer validate, pint, phpcs, phpstan, pest)
  - Docker build & push
  - Deploy (SSH, zero-downtime, symlink releases)

### [ ] Server Configuration
- **Nginx** configuration
- **PHP-FPM** tuning
- **Supervisor** queue workers
- **SSL/TLS** via Cloudflare
- **Log rotation** ve management

### [ ] Monitoring ve Backup
- **Sentry** error tracking
- **Health Check** endpoints
- **Database Backup**: günlük automated
- **File Backup**: S3 compatible storage
- **Uptime Monitoring**: alerts setup

---

## Testing Strategy

### [ ] Unit Tests
- PricingService calculations
- StockService logic
- CartService operations
- Coupon application rules

### [ ] Feature Tests
- User registration/login
- Product browsing
- Cart functionality
- Checkout process
- Order management
- Payment flow (mock iyzico)

### [ ] Integration Tests
- iyzico webhook handling
- Email sending
- File upload/storage
- Admin panel critical functions

---

## Go-Live Checklist

### [ ] DNS & SSL
- Domain configuration (shop.ilisan.com.tr)
- Cloudflare DNS/WAF setup
- SSL certificate verification
- HSTS preload

### [ ] Security Verification
- Admin 2FA enabled
- Rate limiting active
- Security headers set
- Webhook signature verification
- IP whitelisting configured

### [ ] Performance Check
- Page load speeds < 3s
- Database query optimization
- Image optimization active
- CDN configuration
- Cache hit rates

### [ ] Backup & Recovery
- Database backup tested
- File backup verified
- Recovery procedure documented
- Rollback plan ready

---

Her bölümü tamamladıktan sonra bana rapor et:
- Tamamlanan görevler
- Oluşturulan dosyalar
- Çalıştırılan testler
- Karşılaşılan sorunlar
- Sonraki adım önerileri

**Bu listeyi adım adım takip ederek 6 haftada production-ready bir e-ticaret sistemi oluşturacağız.**
