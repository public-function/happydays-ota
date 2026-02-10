<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('hotel_room_type_id')->nullable();
            $table->unsignedBigInteger('product_offer_id')->nullable();
            $table->date('date');
            $table->integer('total_units')->default(0);
            $table->integer('available_units')->default(0);
            $table->integer('held_units')->default(0);
            $table->boolean('stop_sell')->default(false);
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['hotel_room_type_id', 'date'], 'inv_room_date_unique');
            $table->index(['product_offer_id', 'hotel_room_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
