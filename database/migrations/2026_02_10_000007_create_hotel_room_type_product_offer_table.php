<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_room_type_product_offer', function (Blueprint $table) {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_room_type_product_offer');
    }
};
