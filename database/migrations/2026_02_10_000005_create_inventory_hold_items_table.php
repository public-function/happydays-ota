<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_hold_items', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('inventory_hold_id');
            $table->unsignedBigInteger('inventory_id');
            $table->date('night_date');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->index(['inventory_hold_id']);
            $table->index(['inventory_id', 'night_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_hold_items');
    }
};
