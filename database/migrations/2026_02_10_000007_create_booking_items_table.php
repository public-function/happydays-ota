<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('product_offer_id');
            $table->unsignedBigInteger('hotel_room_type_id');
            $table->date('check_in_date');
            $table->integer('nights')->default(1);
            $table->integer('quantity')->default(1);
            $table->integer('adults')->default(2);
            $table->integer('children')->default(0);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->json('snapshot')->nullable();
            $table->timestamps();

            $table->index(['booking_id']);
            $table->index(['product_offer_id', 'hotel_room_type_id', 'check_in_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
