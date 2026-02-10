<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_room_type_inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_room_type_id');
            $table->date('night_date');
            $table->integer('available_units');
            $table->integer('held_units')->default(0);
            $table->boolean('stop_sell')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->primary(['hotel_room_type_id', 'night_date']);
            $table->index(['night_date', 'hotel_room_type_id']);
            $table->foreign('hotel_room_type_id')->references('id')->on('hotel_room_types')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_room_type_inventories');
    }
};
