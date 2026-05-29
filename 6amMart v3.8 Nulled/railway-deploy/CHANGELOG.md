# Changelog

All notable changes to this 6amMart v3.8 custom build.

## [Unreleased] — 2026-05-22

### Added

#### Backend (Laravel)
- **Dine In API** (`Api/V1/DineInController`)
  - `GET /api/v1/customer/dine-in/stores` — list dine-in enabled stores
  - `GET /api/v1/customer/dine-in/tables` — list tables by store
  - `GET /api/v1/customer/dine-in/check-availability` — check table availability
  - `POST /api/v1/customer/dine-in/book` — create reservation
  - `GET /api/v1/customer/dine-in/my-reservations` — user reservation history
  - `POST /api/v1/customer/dine-in/cancel/{id}` — cancel reservation

- **Gift Card API** (`Api/V1/GiftCardController`)
  - `GET /api/v1/customer/gift-card/list`
  - `POST /api/v1/customer/gift-card/apply`
  - `POST /api/v1/customer/gift-card/purchase`
  - `POST /api/v1/customer/gift-card/redeem`

- **Document Verification API** (`Api/V1/DocumentVerificationController`)
  - `POST /api/v1/customer/document-verification/store`
  - Supports image upload via multipart/form-data

- **Delivery Optimization API** (`Api/V1/DeliveryOptimizationController`)
  - `POST /api/v1/delivery-man/delivery-optimization/suggest-batch` — AI-powered batch suggestions
  - `POST /api/v1/delivery-man/delivery-optimization/estimate-window` — delivery time estimation

- **Admin Panel Controllers** with `module_permission_check` integration
  - `Admin/DineInController`
  - `Admin/GiftCardController`
  - `Admin/StoryController`
  - `Admin/DocumentVerificationController`
  - `Admin/StoreDeliveryZoneController`
  - `Admin/HybridPricingController`
  - `Admin/DeliveryOptimizationController`

- **DeliveryOptimizationService** — batch grouping, route optimization (nearest neighbor), Haversine distance calculation

- **New Permissions** added to `admin_roles.modules` JSON:
  - `dine_in`, `delivery_optimization`, `hybrid_pricing`, `story`, `store_delivery_zone`, `gift_card`, `document_verification`

- **Translations** added to `resources/lang/en/messages.php` and all 3 Flutter `en.json` files

#### Flutter — User App
- `DineInScreen` + `TableReservationScreen` — full table booking flow
- `GiftCardScreen` — apply gift card code
- `DocumentVerificationScreen` — document upload
- `DineInController`, `DineInRepository`, `DineInService` — full DI wiring

#### Flutter — Vendor App
- `DineInManagementScreen` — QR check-in for reservations
- `QrScannerScreen` — `mobile_scanner` integration
- `StoryManagementScreen` — create stories with image picker
- `SelfDeliveryScreen` — add delivery men
- `StoreDeliveryZoneScreen` + `MapZoneScreen` — Google Maps polygon drawing
- `DocumentVerificationScreen` — upload documents

#### Flutter — Delivery Man App
- `DocumentVerificationScreen` — upload documents
- `ActiveBatchesScreen` — view active delivery batches
- `BatchController` + `DeliveryBatchModel` — full batch lifecycle
- Extended `BatchService` / `BatchRepository` with `getBatchDetails`, `acceptBatch`, `rejectBatch`, `completeDeliveryInBatch`, `updateSequence`

### Fixed
- **PaymentController** — fatal error `Cannot declare class ExtendedController` caused by `eval()` on subsequent requests. Added `class_exists()` guard.
- **AddonController** — same `eval()` fix applied.
- **OrderLogic.php** — null safety check for `$store->fixed_delivery_fee_active` before accessing hybrid pricing fields.
- **Migrations** — removed dead/conflicting migrations `000020`–`000023`.
- **httpd-vhosts.conf** — corrected VirtualHost on port 80 from non-existent `credito1` to 6amMart `public/` folder.

### Security
- Added `CAMERA` permission to all 3 `AndroidManifest.xml` files
- Added `NSCameraUsageDescription` to Delivery Man `Info.plist`

---

## [3.8.0] — Original Release
- Base 6amMart v3.8 Nulled build

---

*Format based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).*
