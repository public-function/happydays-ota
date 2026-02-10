<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('booking_id');
            $table->string('reference', 64)->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('method', ['credit_card', 'bank_transfer', 'cash', 'other'])->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->json('transaction_data')->nullable();
            $table->timestamps();

            $table->index(['booking_id']);
            $table->index(['reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
