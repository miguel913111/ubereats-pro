# 6amMart v3.8 — New API Documentation

## Overview
This document describes the new API endpoints added in the custom feature set:
- Dine In (table reservations)
- Gift Cards
- Document Verification
- Delivery Optimization (batch suggestions & time windows)
- Story Management
- Store Delivery Zones
- Hybrid Pricing (backend only)

---

## Dine In API

### `GET /api/v1/customer/dine-in/stores`
**Description:** List stores that support dine-in.

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "stores": [
    {
      "id": 1,
      "name": "Restaurant Name",
      "address": "123 Main St",
      "latitude": "40.7128",
      "longitude": "-74.0060",
      "dine_in": 1
    }
  ]
}
```

---

### `GET /api/v1/customer/dine-in/tables?store_id={id}`
**Description:** List available tables for a store.

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "tables": [
    {
      "id": 1,
      "store_id": 1,
      "table_number": "A1",
      "capacity": 4,
      "status": "available"
    }
  ]
}
```

---

### `GET /api/v1/customer/dine-in/check-availability`
**Description:** Check if a table is available for a given date/time.

**Headers:** `Authorization: Bearer {token}`

**Query Params:**
- `store_id` (int, required)
- `store_table_id` (int, required)
- `reservation_date` (string, required) — format: YYYY-MM-DD
- `reservation_time` (string, required) — format: HH:mm

**Response:**
```json
{
  "available": true,
  "message": "Table is available"
}
```

---

### `POST /api/v1/customer/dine-in/book`
**Description:** Book a table reservation.

**Headers:** `Authorization: Bearer {token}`

**Body:**
```json
{
  "store_id": 1,
  "store_table_id": 1,
  "reservation_date": "2025-06-01",
  "reservation_time": "19:00",
  "number_of_guests": 4,
  "special_request": "Window seat please"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Reservation created successfully",
  "reservation": { "id": 1, "status": "pending" }
}
```

---

### `GET /api/v1/customer/dine-in/my-reservations`
**Description:** List current user's reservations.

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "reservations": [
    {
      "id": 1,
      "store_name": "Restaurant Name",
      "table_number": "A1",
      "reservation_date": "2025-06-01",
      "reservation_time": "19:00",
      "status": "pending"
    }
  ]
}
```

---

### `POST /api/v1/customer/dine-in/cancel/{id}`
**Description:** Cancel a reservation.

**Headers:** `Authorization: Bearer {token}`

**Body:**
```json
{
  "cancellation_reason": "Changed plans"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Reservation cancelled successfully"
}
```

---

## Gift Card API

### `GET /api/v1/customer/gift-card/list`
**Description:** List available gift cards.

**Headers:** `Authorization: Bearer {token}`

---

### `POST /api/v1/customer/gift-card/apply`
**Description:** Apply a gift card code to an order.

**Headers:** `Authorization: Bearer {token}`

**Body:**
```json
{
  "code": "GIFT2025"
}
```

**Response:**
```json
{
  "success": true,
  "discount": 10.00,
  "message": "Gift card applied successfully"
}
```

---

### `POST /api/v1/customer/gift-card/purchase`
**Description:** Purchase a gift card.

**Headers:** `Authorization: Bearer {token}`

**Body:**
```json
{
  "gift_card_id": 1,
  "payment_method": "card"
}
```

---

## Document Verification API

### `POST /api/v1/customer/document-verification/store`
**Description:** Submit documents for verification.

**Headers:** `Authorization: Bearer {token}`

**Body (multipart/form-data):**
- `document_type` (string) — e.g. "identity", "license"
- `document_number` (string)
- `document_images[]` (files)
- `notes` (string, optional)
- `verifiable_type` (string) — e.g. "App\\Models\\Store"
- `verifiable_id` (string)

---

## Delivery Optimization API (Delivery Man)

### `POST /api/v1/delivery-man/delivery-optimization/suggest-batch`
**Description:** Get batch delivery suggestions for the authenticated delivery man.

**Headers:** `Authorization: Bearer {token}`

**Body:**
```json
{
  "token": "dm_auth_token",
  "zone_id": 1
}
```

**Response:**
```json
{
  "message": "Batch suggestions retrieved successfully",
  "count": 2,
  "suggestions": [
    {
      "total_orders": 3,
      "total_distance_km": 4.5,
      "total_time_min": 18.5,
      "orders": [
        {
          "id": 101,
          "sequence": 1,
          "customer_lat": 40.7128,
          "customer_lng": -74.0060,
          "store_name": "Burger King",
          "distance_from_prev_km": 1.2
        }
      ]
    }
  ]
}
```

---

### `POST /api/v1/delivery-man/delivery-optimization/estimate-window`
**Description:** Estimate delivery time and available time windows.

**Headers:** `Authorization: Bearer {token}`

**Body:**
```json
{
  "token": "dm_auth_token",
  "store_lat": 40.7128,
  "store_lng": -74.0060,
  "customer_lat": 40.7300,
  "customer_lng": -74.0200,
  "zone_id": 1
}
```

**Response:**
```json
{
  "distance_km": 2.3,
  "estimated_time_min": 11.9,
  "estimated_time_formatted": "00:11",
  "time_windows": [
    {
      "id": 1,
      "start_time": "09:00",
      "end_time": "12:00",
      "day": "monday",
      "max_orders": 10
    }
  ]
}
```

---

## Story API

### `GET /api/v1/customer/story/list`
**Description:** List active stories.

---

### `POST /api/v1/vendor/story/create`
**Description:** Create a new story (Vendor App).

**Headers:** `Authorization: Bearer {token}`

**Body (multipart/form-data):**
- `image` (file) or `video` (file)
- `title` (string, optional)
- `duration` (int, seconds, optional)

---

## Store Delivery Zone API

### `GET /api/v1/vendor/store-delivery-zone/list`
**Description:** List delivery zones for the vendor's store.

---

### `POST /api/v1/vendor/store-delivery-zone/store`
**Description:** Save a new delivery zone polygon.

**Headers:** `Authorization: Bearer {token}`

**Body:**
```json
{
  "name": "Downtown Zone",
  "coordinates": [
    {"lat": 40.7128, "lng": -74.0060},
    {"lat": 40.7300, "lng": -74.0060},
    {"lat": 40.7300, "lng": -74.0200},
    {"lat": 40.7128, "lng": -74.0200}
  ],
  "delivery_charge": 5.00,
  "min_delivery_time": 30
}
```

---

## Admin Panel Web Routes

| Feature | Route | Permission Key |
|---------|-------|----------------|
| Dine In Tables | `/admin/dine-in/tables` | `dine_in` |
| Dine In Reservations | `/admin/dine-in/reservations` | `dine_in` |
| Gift Cards | `/admin/gift-card` | `gift_card` |
| Story Management | `/admin/story` | `story` |
| Document Verification | `/admin/document-verification` | `document_verification` |
| Store Delivery Zones | `/admin/store-delivery-zone` | `store_delivery_zone` |
| Hybrid Pricing | `/admin/hybrid-pricing` | `hybrid_pricing` |
| Delivery Optimization | `/admin/delivery-optimization` | `delivery_optimization` |

---

## Authentication

All API endpoints require authentication via:
- **Customer/User:** `Authorization: Bearer {token}` header
- **Delivery Man:** `token` field in request body + `actch:deliveryman_app` middleware
- **Vendor:** `Authorization: Bearer {token}` header

---

## Error Codes

| Code | Description |
|------|-------------|
| `auth` | Invalid or expired token |
| `zone` | Zone ID is required but not provided |
| `order-payment` | Order not found for payment |
| `batch_delivery_disabled` | Batch delivery feature is turned off |
| `no_batchable_orders` | No orders available for batching in zone |

---

*Generated: 2026-05-22*
