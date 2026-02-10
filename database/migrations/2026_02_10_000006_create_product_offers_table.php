<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_offers', function (Blueprint $table) {
            $table->id('id')->bigIncrements();
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('rate_plan_id');
            $table->string('name');
            $table->integer('duration_nights');
            $table->integer('min_guests')->default(1);
            $table->integer('max_guests')->default(2);
            $table->decimal('base_price', 10, 2);
            $table->enum('status', ['draft', 'active', 'paused', 'archived'])->default('draft');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes('deleted_at');
            
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('restrict');
            $table->foreign('rate_plan_id')->references('id')->on('rate_plans')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_offers');
    }
};
