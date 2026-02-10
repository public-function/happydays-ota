# Database Seeders & Factories for HappyDays OTA

This directory contains database seeders and factories for testing and development of the HappyDays OTA project.

## Directory Structure

```
database/
├── Seeders/
│   ├── DatabaseSeeder.php      # Main seeder that calls all others
│   ├── RoomTypesSeeder.php     # Creates internal canonical room types
│   ├── HotelsSeeder.php        # Creates 5 sample hotels in Denmark
│   ├── RatePlansSeeder.php      # Creates standard rate plans
│   ├── ProductOffersSeeder.php # Creates sample offers for each hotel
│   ├── InventorySeeder.php     # Creates inventory for next 90 days
│   └── SampleBookingsSeeder.php # Creates 10-20 sample bookings

app/
└── Factories/
    ├── HotelFactory.php
    ├── RoomTypeFactory.php
    ├── HotelRoomTypeFactory.php
    ├── RatePlanFactory.php
    ├── ProductOfferFactory.php
    ├── InventoryFactory.php
    ├── BookingFactory.php
    └── BookingItemFactory.php
```

## Usage

### Running All Seeders

To seed the entire database:

```bash
php artisan db:seed
```

This will run all seeders in the following order:
1. `RoomTypesSeeder` - Creates canonical room types (single, double, twin, family, suite)
2. `RatePlansSeeder` - Creates rate plans (Room Only, Bed & Breakfast, Half Board, Full Board, All Inclusive, Flexible)
3. `HotelsSeeder` - Creates 5 hotels with room types
4. `ProductOffersSeeder` - Creates product offers for each hotel
5. `InventorySeeder` - Creates 90 days of inventory
6. `SampleBookingsSeeder` - Creates 10-20 sample bookings

### Running Individual Seeders

To run a specific seeder:

```bash
php artisan db:seed --class=HotelsSeeder
php artisan db:seed --class=SampleBookingsSeeder
```

### Using Factories in Tests/Tinker

You can use the factories in your tests or tinker:

```php
// Create a single hotel
App\Models\Hotel::factory()->create();

// Create multiple hotels
App\Models\Hotel::factory()->count(5)->create();

// Create a hotel with specific attributes
App\Models\Hotel::factory()->create([
    'city' => 'Copenhagen',
    'status' => 'active',
]);

// Create a booking with items
App\Models\Booking::factory()->hasItems(3)->create();
```

## Sample Data

### Hotels Created

| City | Name | Rooms |
|------|------|-------|
| Skagen | Hotel Skagen, Grand Hotel Skagen, The Beach House Skagen | 5 room types |
| Copenhagen | Hotel Copenhagen, The Imperial, Nobis Hotel Copenhagen | 5 room types |
| Aarhus | Hotel Aarhus, Comwell Aarhus, Scandic Aarhus City | 4 room types |
| Aalborg | Hotel Aalborg, Kul Hotel Aalborg, Comwell Hvide Hus | 4 room types |
| Odense | Hotel Odense, First Hotel Odense, Den Hvide Cafe | 4 room types |

### Rate Plans Created

- **Room Only (RO)** - No meals included
- **Bed & Breakfast (BB)** - Breakfast included
- **Half Board (HB)** - Breakfast and dinner included
- **Full Board (FB)** - All meals included
- **All Inclusive (AI)** - All meals and drinks included
- **Flexible Rate** - Free cancellation until check-in

### Product Offers

Each hotel gets offers with various durations:
- 3 nights packages
- 5 nights packages
- 7 nights packages
- 14 nights packages

### Inventory

90 days of inventory is created for each hotel room type with:
- 5-20 available units per night
- 0-3 held units per night
- 10% chance of stop-sell flag

### Sample Bookings

10-20 sample bookings are created with:
- Mix of confirmed (80%), pending (15%), and cancelled (5%) statuses
- 1-3 booking items per booking
- Realistic Danish pricing (1000-5000 DKK per night)
- Random future check-in dates (7-60 days ahead)

## Resetting Data

To reset and reseed:

```bash
php artisan migrate:fresh --seed
```

Or to only refresh seeders:

```bash
php artisan db:seed --fresh
```

## Notes

- All factories use Faker for realistic sample data
- Hotels are created with realistic Danish addresses
- Pricing varies by city (Copenhagen is most expensive, Aalborg/Odense are more affordable)
- All timestamps use Europe/Copenhagen timezone
- Booking references are generated in format: HDXXXXXX (e.g., HD8XK2M1)
