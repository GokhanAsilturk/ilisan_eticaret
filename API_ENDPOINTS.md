# İlisan E-Ticaret API Documentation

## Base URL

```
http://localhost/api
```

## Authentication

API uses Laravel Sanctum for token-based authentication.

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## Payment Endpoints

### 1. Initiate Payment (3D Secure)

**POST** `/payment/initiate`

Starts a 3D Secure payment process with iyzico.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "order_number": "ORDER-001",
    "card": {
        "holder_name": "Test User",
        "number": "5890040000000016",
        "expire_month": "12",
        "expire_year": "2030",
        "cvc": "123"
    },
    "billing_address": {
        "name": "Test User",
        "address": "Test Mahallesi, Test Sokak No:1",
        "city": "İstanbul",
        "postal_code": "34000"
    }
}
```

**Success Response (200):**

```json
{
    "success": true,
    "data": {
        "payment_id": "uuid",
        "threeds_html_content": "<html>...</html>",
        "conversation_id": "order_ORDER-001_abc123"
    }
}
```

### 2. Get Payment Status

**GET** `/payment/{paymentId}/status`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**

```json
{
    "success": true,
    "data": {
        "payment_id": "uuid",
        "order_number": "ORDER-001",
        "status": "captured",
        "amount": 100.5,
        "currency": "TRY",
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T10:05:00.000000Z"
    }
}
```

### 3. Request Refund

**POST** `/payment/{paymentId}/refund`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "reason": "Customer request",
    "amount": 50.25
}
```

### 4. 3D Secure Callback (Public)

**POST** `/payment/iyzico/callback`

Handles iyzico 3D Secure callback.

### 5. iyzico Webhook (Public)

**POST** `/payment/iyzico/webhook`

Handles iyzico webhook notifications.

---

## Payment Test Endpoints

### 1. Get Test Cards

**GET** `/payment-test/cards`

Returns iyzico test cards for development.

**Response:**

```json
{
    "success": true,
    "data": {
        "test_cards": {
            "5890040000000016": {
                "number": "5890040000000016",
                "expire_month": "12",
                "expire_year": "2030",
                "cvc": "123",
                "holder_name": "Test User",
                "description": "3D Secure başarılı kart"
            }
        }
    }
}
```

### 2. Get Payment Config

**GET** `/payment-test/config`

Returns payment configuration for testing.

### 3. Get Sample Payment Request

**GET** `/payment-test/sample-request`

Returns a complete sample payment request.

---

## Authentication Endpoints

### 1. Register

**POST** `/auth/register`

**Request Body:**

```json
{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "+90 555 123 4567",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!"
}
```

### 2. Login

**POST** `/auth/login`

**Request Body:**

```json
{
    "email": "john@example.com",
    "password": "SecurePass123!"
}
```

**Success Response:**

```json
{
    "success": true,
    "data": {
        "user": {...},
        "token": "bearer_token_here"
    }
}
```

---

## Order Endpoints

### 1. Create Order

**POST** `/checkout/order`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "shipping_address": {
        "first_name": "John",
        "last_name": "Doe",
        "company": "",
        "address_line_1": "Test Address 123",
        "address_line_2": "",
        "city": "İstanbul",
        "state": "İstanbul",
        "postal_code": "34000",
        "country": "Turkey",
        "phone": "+90 555 123 4567"
    },
    "billing_same_as_shipping": true,
    "notes": "Please deliver carefully"
}
```

### 2. Get Orders

**GET** `/orders`

**Headers:** `Authorization: Bearer {token}`

### 3. Get Order Details

**GET** `/orders/{orderNumber}`

**Headers:** `Authorization: Bearer {token}`

---

## Product Endpoints

### 1. Get Products

**GET** `/products?page=1&per_page=12&category=1&featured=true&sort=price_asc`

**Query Parameters:**

-   `page`: Page number (default: 1)
-   `per_page`: Items per page (default: 12)
-   `category`: Category ID filter
-   `featured`: Show only featured products (true/false)
-   `sort`: Sort order (price_asc, price_desc, name_asc, name_desc, newest)

### 2. Get Product Details

**GET** `/products/{slug}`

### 3. Get Product Variant

**GET** `/products/variant/{variantId}`

---

## Cart Endpoints

### 1. Get Cart

**GET** `/cart`

### 2. Add to Cart

**POST** `/cart/add`

**Request Body:**

```json
{
    "variant_id": 1,
    "quantity": 2
}
```

### 3. Update Cart Item

**PUT** `/cart/{cartItemId}`

**Request Body:**

```json
{
    "quantity": 3
}
```

### 4. Remove Cart Item

**DELETE** `/cart/{cartItemId}`

### 5. Clear Cart

**DELETE** `/cart`

---

## Test Endpoints

### 1. API Ping Test

**GET** `/api-test/ping`

### 2. Auth Headers Test

**GET** `/api-test/auth-headers`

### 3. POST Data Test

**POST** `/api-test/post-data`

---

## Status Codes

-   `200` - Success
-   `201` - Created
-   `400` - Bad Request
-   `401` - Unauthorized
-   `403` - Forbidden
-   `404` - Not Found
-   `422` - Validation Error
-   `429` - Too Many Requests
-   `500` - Server Error

## Rate Limiting

-   **General API**: 60 requests per minute
-   **Authenticated**: 100 requests per minute
-   **Auth endpoints**: 5 requests per minute

## Error Response Format

```json
{
    "success": false,
    "message": "Error message",
    "error_code": "ERROR_CODE",
    "errors": {
        "field": ["validation error"]
    }
}
```
