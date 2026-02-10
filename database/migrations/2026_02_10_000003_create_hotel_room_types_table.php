<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_room_types', function (Blueprint $table) {
            $table->id('id')->bigIncrements();
            $table->unsignedBigInteger('hotel_id');
            $table->string('supplier_code');
            $table->string('supplier_name');
            $table->integer('max_occupancy');
            $table->integer('min_occupancy')->default(1);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes('deleted_at');
            
            $table->unique(['hotel_id', 'supplier_code']);
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_room_types');
    }
};
