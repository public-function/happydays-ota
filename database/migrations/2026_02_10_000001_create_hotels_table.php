<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id('id')->bigIncrements();
            $table->string('name');
            $table->text('address');
            $table->string('city');
            $table->string('country');
            $table->string('postal_code');
            $table->string('phone');
            $table->string('email');
            $table->string('website')->nullable();
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->time('checkin_time')->default('14:00:00');
            $table->time('checkout_time')->default('12:00:00');
            $table->string('timezone')->default('UTC');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
