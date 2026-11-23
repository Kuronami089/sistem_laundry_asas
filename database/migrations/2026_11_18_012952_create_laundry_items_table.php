<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laundry_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['Cepat', 'Kilat', 'Standar', 'Komplit'])->default('Standar');
            $table->integer('price');
            $table->enum('unit', ['kg', 'pcs', 'pasang'])->default('kg');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_items');
    }
};
