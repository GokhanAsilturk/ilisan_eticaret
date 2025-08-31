# İlisan E-Ticaret API Endpoints

## Genel Bilgiler

- **Base URL**: `http://localhost:8000/api`
- **Authentication**: Laravel Sanctum (Bearer Token)
- **Content-Type**: `application/json`

## 🔐 Authentication Endpoints

### Kayıt Ol
```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+90 555 123 4567",
    "birth_date": "1990-01-01",
    "session_token": "optional_guest_session_token"
}
```

### Giriş Yap
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123",
    "session_token": "optional_guest_session_token"
}
```

### Çıkış Yap
```http
POST /api/user/logout
Authorization: Bearer {token}
```

### Profil Bilgileri
```http
GET /api/user
Authorization: Bearer {token}
```

### Profil Güncelle
```http
PUT /api/user
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Name",
    "phone": "+90 555 123 4567",
    "birth_date": "1990-01-01"
}
```

## 🛍️ Product Endpoints

### Ürün Listesi
```http
GET /api/products?category=celik-yelek&search=koruma&min_price=100&max_price=500&sort=price&order=asc&per_page=12&page=1
```

### Ürün Detayı
```http
GET /api/products/{slug}
```

### Öne Çıkan Ürünler
```http
GET /api/products/featured
```

### Kategoriler
```http
GET /api/products/categories
```

### Varyant Detayı
```http
GET /api/products/variant/{variantId}
```

## 🛒 Cart Endpoints

### Sepet Görüntüle
```http
GET /api/cart
Authorization: Bearer {token} (optional)
X-Session-Token: {guest_session_token} (for guests)
```

### Sepete Ürün Ekle
```http
POST /api/cart/add
Authorization: Bearer {token} (optional)
X-Session-Token: {guest_session_token} (for guests)
Content-Type: application/json

{
    "variant_id": 1,
    "quantity": 2
}
```

### Sepet Öğesi Güncelle
```http
PUT /api/cart/{cartItemId}
Authorization: Bearer {token} (optional)
X-Session-Token: {guest_session_token} (for guests)
Content-Type: application/json

{
    "quantity": 3
}
```

### Sepetten Öğe Kaldır
```http
DELETE /api/cart/{cartItemId}
Authorization: Bearer {token} (optional)
X-Session-Token: {guest_session_token} (for guests)
```

### Sepeti Temizle
```http
DELETE /api/cart
Authorization: Bearer {token} (optional)
X-Session-Token: {guest_session_token} (for guests)
```

## ✅ Checkout Endpoints

### Sepeti Doğrula
```http
POST /api/checkout/validate
Authorization: Bearer {token}
```

### Kargo Ücreti Hesapla
```http
POST /api/checkout/shipping
Authorization: Bearer {token}
Content-Type: application/json

{
    "address_id": 1
}
```

### Sipariş Oluştur
```http
POST /api/checkout/order
Authorization: Bearer {token}
Content-Type: application/json

{
    "shipping_address_id": 1,
    "billing_address_id": 2,
    "shipping_method": "standard",
    "notes": "Kapıya teslim edilsin"
}
```

### Sipariş Detayı (Checkout)
```http
GET /api/checkout/order/{orderNumber}
Authorization: Bearer {token}
```

## 📦 Order Endpoints

### Sipariş Listesi
```http
GET /api/orders?page=1
Authorization: Bearer {token}
```

### Sipariş Detayı
```http
GET /api/orders/{orderNumber}
Authorization: Bearer {token}
```

### Sipariş İptal Et
```http
POST /api/orders/{orderNumber}/cancel
Authorization: Bearer {token}
```

### Siparişi Tekrar Ver
```http
POST /api/orders/{orderNumber}/reorder
Authorization: Bearer {token}
```

## 🏠 Address Endpoints

### Adres Listesi
```http
GET /api/user/addresses
Authorization: Bearer {token}
```

### Adres Ekle
```http
POST /api/user/addresses
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Ev Adresi",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+90 555 123 4567",
    "address_line_1": "Atatürk Cad. No:123",
    "address_line_2": "Daire 4",
    "city": "İstanbul",
    "state": "İstanbul",
    "postal_code": "34000",
    "country": "TR",
    "is_default": true
}
```

### Adres Güncelle
```http
PUT /api/user/addresses/{addressId}
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "İş Adresi",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+90 555 123 4567",
    "address_line_1": "İş Merkezi No:456",
    "city": "Ankara",
    "state": "Ankara",
    "postal_code": "06000",
    "country": "TR",
    "is_default": false
}
```

### Adres Sil
```http
DELETE /api/user/addresses/{addressId}
Authorization: Bearer {token}
```

## 🔧 System Endpoints

### Health Check
```http
GET /api/health
```

## 📊 Response Formats

### Başarılı Response
```json
{
    "success": true,
    "data": {...},
    "message": "İşlem başarılı"
}
```

### Hata Response
```json
{
    "success": false,
    "error": "Hata mesajı",
    "errors": {
        "field": ["Validation hatası"]
    }
}
```

### Pagination
```json
{
    "data": [...],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 12,
        "total": 60
    }
}
```

## 🧪 Test Komutları

### Postman/Thunder Client
```javascript
// Auth token'ı kaydet
pm.environment.set("auth_token", pm.response.json().token);

// Headers
Authorization: Bearer {{auth_token}}
Content-Type: application/json
X-Session-Token: {{guest_session}} // Misafir kullanıcılar için
```

### cURL Examples
```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"password"}'

# Get Products
curl -X GET http://localhost:8000/api/products

# Add to Cart (Authenticated)
curl -X POST http://localhost:8000/api/cart/add \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"variant_id":1,"quantity":2}'
```
