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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('barcode')->nullable()->unique();
            $table->string('name');
            $table->string('image')->nullable();
            $table->date('expired_at')->nullable();
            $table->longText('description')->nullable();
            $table->integer('selling_price');
            $table->integer('stock')->default(1);
            $table->boolean('is_active')->default(true);
            //$table->integer('quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
