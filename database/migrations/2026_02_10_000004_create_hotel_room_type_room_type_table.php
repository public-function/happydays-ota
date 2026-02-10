<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_room_type_room_type', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_room_type_id');
            $table->unsignedBigInteger('room_type_id');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->primary(['hotel_room_type_id', 'room_type_id']);
            $table->foreign('hotel_room_type_id')->references('id')->on('hotel_room_types')->onDelete('cascade');
            $table->foreign('room_type_id')->references('id')->on('room_types')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_room_type_room_type');
    }
};
