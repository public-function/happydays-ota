<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

$schema = $capsule->schema();

echo "Testing migrations...\n\n";

// Drop all tables if they exist (in reverse order of creation)
$tables = ['refunds', 'payments', 'booking_items', 'bookings', 'inventory_hold_items', 'inventory_holds', 'hotel_room_type_inventories', 'hotel_room_type_product_offer', 'product_offers', 'rate_plans', 'hotel_room_type_room_type', 'hotel_room_types', 'room_types', 'hotels'];

foreach ($tables as $table) {
    $schema->dropIfExists($table);
}

// Run migrations with SQLite-compatible syntax
echo "Creating hotels table...\n";
$schema->create('hotels', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('name');
    $table->text('address');
    $table->string('city');
    $table->string('country');
    $table->string('postal_code');
    $table->string('phone');
    $table->string('email');
    $table->string('website')->nullable();
    $table->string('status')->default('active');
    $table->time('checkin_time')->default('14:00:00');
    $table->time('checkout_time')->default('12:00:00');
    $table->string('timezone')->default('UTC');
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    $table->softDeletes('deleted_at');
});

echo "Creating room_types table...\n";
$schema->create('room_types', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('code')->unique();
    $table->string('name');
    $table->text('description')->nullable();
    $table->integer('default_max_occupancy')->nullable();
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
});

echo "Creating hotel_room_types table...\n";
$schema->create('hotel_room_types', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('hotel_id');
    $table->string('supplier_code');
    $table->string('supplier_name');
    $table->integer('max_occupancy');
    $table->integer('min_occupancy')->default(1);
    $table->string('status')->default('active');
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    $table->softDeletes('deleted_at');
    $table->unique(['hotel_id', 'supplier_code']);
    $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('restrict');
});

echo "Creating hotel_room_type_room_type pivot table...\n";
$schema->create('hotel_room_type_room_type', function (Blueprint $table) {
    $table->unsignedBigInteger('hotel_room_type_id');
    $table->unsignedBigInteger('room_type_id');
    $table->boolean('is_primary')->default(false);
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    $table->primary(['hotel_room_type_id', 'room_type_id']);
    $table->foreign('hotel_room_type_id')->references('id')->on('hotel_room_types')->onDelete('cascade');
    $table->foreign('room_type_id')->references('id')->on('room_types')->onDelete('cascade');
});

echo "Creating rate_plans table...\n";
$schema->create('rate_plans', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('name');
    $table->string('board_type');
    $table->json('cancellation_policy')->nullable();
    $table->string('status')->default('active');
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
});

echo "Creating product_offers table...\n";
$schema->create('product_offers', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('hotel_id');
    $table->unsignedBigInteger('rate_plan_id');
    $table->string('name');
    $table->integer('duration_nights');
    $table->integer('min_guests')->default(1);
    $table->integer('max_guests')->default(2);
    $table->decimal('base_price', 10, 2);
    $table->string('status')->default('draft');
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    $table->softDeletes('deleted_at');
    $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('restrict');
    $table->foreign('rate_plan_id')->references('id')->on('rate_plans')->onDelete('restrict');
});

echo "Creating hotel_room_type_product_offer pivot table...\n";
$schema->create('hotel_room_type_product_offer', function (Blueprint $table) {
    $table->unsignedBigInteger('hotel_room_type_id');
    $table->unsignedBigInteger('product_offer_id');
    $table->boolean('is_default')->default(false);
    $table->decimal('price_delta_amount', 10, 2)->nullable();
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    $table->primary(['hotel_room_type_id', 'product_offer_id']);
    $table->foreign('hotel_room_type_id')->references('id')->on('hotel_room_types')->onDelete('cascade');
    $table->foreign('product_offer_id')->references('id')->on('product_offers')->onDelete('cascade');
});

echo "Creating hotel_room_type_inventories table...\n";
$schema->create('hotel_room_type_inventories', function (Blueprint $table) {
    $table->unsignedBigInteger('hotel_room_type_id');
    $table->date('night_date');
    $table->integer('available_units');
    $table->integer('held_units')->default(0);
    $table->boolean('stop_sell')->default(false);
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    $table->primary(['hotel_room_type_id', 'night_date']);
    $table->index(['night_date', 'hotel_room_type_id']);
    $table->foreign('hotel_room_type_id')->references('id')->on('hotel_room_types')->onDelete('cascade');
});

echo "Creating inventory_holds table...\n";
$schema->create('inventory_holds', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->uuid('hold_token');
    $table->string('status')->default('active');
    $table->timestamp('expires_at')->nullable();
    $table->unsignedBigInteger('customer_id')->nullable();
    $table->unsignedBigInteger('booking_id')->nullable();
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    $table->index('expires_at');
    $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null');
});

echo "Creating inventory_hold_items table...\n";
$schema->create('inventory_hold_items', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('inventory_hold_id');
    $table->unsignedBigInteger('hotel_room_type_id');
    $table->date('night_date');
    $table->integer('quantity')->default(1);
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    $table->unique(['inventory_hold_id', 'hotel_room_type_id', 'night_date']);
    $table->foreign('inventory_hold_id')->references('id')->on('inventory_holds')->onDelete('cascade');
    $table->foreign('hotel_room_type_id')->references('id')->on('hotel_room_types')->onDelete('restrict');
});

echo "Creating bookings table...\n";
$schema->create('bookings', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('booking_reference')->unique();
    $table->string('customer_name');
    $table->string('customer_email');
    $table->string('customer_phone');
    $table->string('status')->default('pending_payment');
    $table->string('currency')->default('EUR');
    $table->decimal('total_amount', 10, 2);
    $table->decimal('tax_amount', 10, 2)->nullable();
    $table->unsignedBigInteger('inventory_hold_id')->nullable();
    $table->text('notes')->nullable();
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    $table->foreign('inventory_hold_id')->references('id')->on('inventory_holds')->onDelete('set null');
});

echo "Creating booking_items table...\n";
$schema->create('booking_items', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('booking_id');
    $table->unsignedBigInteger('product_offer_id');
    $table->unsignedBigInteger('hotel_room_type_id');
    $table->date('check_in_date');
    $table->integer('nights');
    $table->integer('adults');
    $table->integer('children')->default(0);
    $table->integer('quantity');
    $table->decimal('unit_price', 10, 2);
    $table->string('status')->default('confirmed');
    $table->string('hotel_name_snapshot')->nullable();
    $table->string('offer_name_snapshot')->nullable();
    $table->string('rate_plan_name_snapshot')->nullable();
    $table->string('hotel_room_type_code_snapshot')->nullable();
    $table->string('hotel_room_type_name_snapshot')->nullable();
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('restrict');
    $table->foreign('product_offer_id')->references('id')->on('product_offers')->onDelete('restrict');
    $table->foreign('hotel_room_type_id')->references('id')->on('hotel_room_types')->onDelete('restrict');
});

echo "Creating payments table...\n";
$schema->create('payments', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('booking_id');
    $table->string('provider');
    $table->string('method');
    $table->decimal('amount', 10, 2);
    $table->string('currency');
    $table->string('status')->default('authorized');
    $table->string('provider_reference')->nullable();
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('restrict');
});

echo "Creating refunds table...\n";
$schema->create('refunds', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('payment_id');
    $table->decimal('amount', 10, 2);
    $table->string('currency');
    $table->string('status')->default('pending');
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    $table->foreign('payment_id')->references('id')->on('payments')->onDelete('restrict');
});

echo "\n✅ All migrations tested successfully!\n\n";
echo "Tables created: " . implode(', ', $tables) . "\n";
