<?php

return [
    // Inventory hold TTL in minutes
    'hold_ttl_minutes' => env('OTA_HOLD_TTL_MINUTES', 15),

    // Default currency
    'default_currency' => env('OTA_DEFAULT_CURRENCY', 'EUR'),

    // Booking reference prefix
    'booking_reference_prefix' => 'BK',

    // Minimum inventory threshold for warnings
    'min_inventory_warning' => 3,
];
