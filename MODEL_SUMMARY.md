# HappyDays OTA - Data Model Summary

## Database Tables (14)

### 1. hotels
- **Purpose**: Core hotel information
- **Soft Deletes**: Yes
- **Key Fields**: name, address, city, country, status (active/inactive/archived), checkin_time, checkout_time, timezone

### 2. room_types
- **Purpose**: Internal canonical room type definitions (e.g., single, double, family)
- **Soft Deletes**: No
- **Key Fields**: code (unique), name, description, default_max_occupancy

### 3. hotel_room_types
- **Purpose**: Hotel-specific room configurations with supplier codes
- **Soft Deletes**: Yes
- **Unique Constraint**: (hotel_id, supplier_code)
- **Key Fields**: supplier_code, supplier_name, max_occupancy, min_occupancy, status

### 4. hotel_room_type_room_type
- **Purpose**: Pivot table mapping hotel room types to canonical room types
- **Primary Key**: (hotel_room_type_id, room_type_id)
- **Key Fields**: is_primary (boolean)

### 5. rate_plans
- **Purpose**: Rate plans (e.g., Breakfast, Breakfast+Dinner)
- **Soft Deletes**: No
- **Key Fields**: name, board_type (RO/BB/HB/FB), cancellation_policy (JSON)

### 6. product_offers
- **Purpose**: Bookable offers combining rate plans with durations
- **Soft Deletes**: Yes
- **Key Fields**: name, duration_nights, min_guests, max_guests, base_price, status (draft/active/paused/archived)

### 7. hotel_room_type_product_offer
- **Purpose**: Pivot linking room types to product offers with pricing
- **Primary Key**: (hotel_room_type_id, product_offer_id)
- **Key Fields**: is_default, price_delta_amount

### 8. hotel_room_type_inventories
- **Purpose**: Daily inventory availability per room type
- **Primary Key**: (hotel_room_type_id, night_date)
- **Index**: (night_date, hotel_room_type_id)
- **Key Fields**: available_units, held_units, stop_sell

### 9. inventory_holds
- **Purpose**: Temporary holds on inventory during checkout
- **Key Fields**: hold_token (UUID), status (active/converted/expired/cancelled), expires_at (indexed)

### 10. inventory_hold_items
- **Purpose**: Individual room/date items within an inventory hold
- **Unique Constraint**: (inventory_hold_id, hotel_room_type_id, night_date)
- **Key Fields**: quantity

### 11. bookings
- **Purpose**: Customer bookings
- **Key Fields**: booking_reference (unique, e.g., BK-2026-00001), customer info, status, total_amount, tax_amount

### 12. booking_items
- **Purpose**: Individual items within a booking
- **RESTRICT Deletes**: booking_id, product_offer_id, hotel_room_type_id
- **Snapshot Fields**: hotel_name_snapshot, offer_name_snapshot, rate_plan_name_snapshot, hotel_room_type_code_snapshot, hotel_room_type_name_snapshot
- **Key Fields**: check_in_date, nights, adults, children, quantity, unit_price, status

### 13. payments
- **Purpose**: Payment records
- **Key Fields**: provider (stripe/manual), method (card/bank_transfer), amount, currency, status, provider_reference

### 14. refunds
- **Purpose**: Refund records linked to payments
- **Key Fields**: amount, currency, status (pending/processed/failed)

---

## Eloquent Models & Relationships

### Hotel
```php
Hotel::hasMany(HotelRoomType::class)
Hotel::hasMany(ProductOffer::class)
```

### RoomType
```php
RoomType::belongsToMany(HotelRoomType::class, 'hotel_room_type_room_type')
  ->withPivot('is_primary')
```

### HotelRoomType
```php
HotelRoomType::belongsTo(Hotel::class)
HotelRoomType::belongsToMany(RoomType::class, 'hotel_room_type_room_type')
HotelRoomType::belongsToMany(ProductOffer::class, 'hotel_room_type_product_offer')
  ->withPivot('is_default', 'price_delta_amount')
HotelRoomType::hasMany(Inventory::class)
```

### RatePlan
```php
RatePlan::hasMany(ProductOffer::class)
```

### ProductOffer
```php
ProductOffer::belongsTo(Hotel::class)
ProductOffer::belongsTo(RatePlan::class)
ProductOffer::belongsToMany(HotelRoomType::class, 'hotel_room_type_product_offer')
```

### Inventory
```php
Inventory::belongsTo(HotelRoomType::class)
```

### InventoryHold
```php
InventoryHold::hasMany(InventoryHoldItem::class)
InventoryHold::belongsTo(Booking::class)
```

### InventoryHoldItem
```php
InventoryHoldItem::belongsTo(InventoryHold::class)
InventoryHoldItem::belongsTo(HotelRoomType::class)
```

### Booking
```php
Booking::hasMany(BookingItem::class)
Booking::belongsTo(InventoryHold::class)
```

### BookingItem
```php
BookingItem::belongsTo(Booking::class)
BookingItem::belongsTo(ProductOffer::class)
BookingItem::belongsTo(HotelRoomType::class)
```

### Payment
```php
Payment::belongsTo(Booking::class)
Payment::hasMany(Refund::class)
```

### Refund
```php
Refund::belongsTo(Payment::class)
```

---

## Foreign Key Strategy

- **RESTRICT deletes**: Tables referenced by bookings/booking_items (hotel_room_types, product_offers, rate_plans)
- **CASCADE**: Soft-deleted parent records (hotel_room_types → inventories)
- **SET NULL**: Optional references (inventory_hold_id in bookings, booking_id in inventory_holds)

---

## File Structure

```
happydays-ota/
├── database/migrations/
│   ├── 2026_02_10_000001_create_hotels_table.php
│   ├── 2026_02_10_000002_create_room_types_table.php
│   ├── 2026_02_10_000003_create_hotel_room_types_table.php
│   ├── 2026_02_10_000004_create_hotel_room_type_room_type_table.php
│   ├── 2026_02_10_000005_create_rate_plans_table.php
│   ├── 2026_02_10_000006_create_product_offers_table.php
│   ├── 2026_02_10_000007_create_hotel_room_type_product_offer_table.php
│   ├── 2026_02_10_000008_create_hotel_room_type_inventories_table.php
│   ├── 2026_02_10_000009_create_inventory_holds_table.php
│   ├── 2026_02_10_000010_create_inventory_hold_items_table.php
│   ├── 2026_02_10_000011_create_bookings_table.php
│   ├── 2026_02_10_000012_create_booking_items_table.php
│   ├── 2026_02_10_000013_create_payments_table.php
│   └── 2026_02_10_000014_create_refunds_table.php
└── app/Models/
    ├── Hotel.php
    ├── RoomType.php
    ├── HotelRoomType.php
    ├── RatePlan.php
    ├── ProductOffer.php
    ├── Inventory.php
    ├── InventoryHold.php
    ├── InventoryHoldItem.php
    ├── Booking.php
    ├── BookingItem.php
    ├── Payment.php
    └── Refund.php
```

---

## Migrations Verified

All 14 migrations have been tested successfully with SQLite and are ready for use with MySQL/PostgreSQL in a Laravel environment.
