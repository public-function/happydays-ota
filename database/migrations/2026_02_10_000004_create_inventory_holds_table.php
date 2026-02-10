<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_holds', function (Blueprint $table) {
            $table->id('id');
            $table->string('token', 64)->unique();
            $table->unsignedBigInteger('product_offer_id');
            $table->unsignedBigInteger('hotel_room_type_id');
            $table->date('check_in_date');
            $table->integer('quantity')->default(1);
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('status', ['active', 'converted', 'expired', 'cancelled'])->default('active');
            $table->timestamp('expires_at');
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_holds');
    }
};
