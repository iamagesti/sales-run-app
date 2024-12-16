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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->integer('sub_total')->nullable();
            $table->integer('grand_total')->nullable();
            $table->enum('payment_method', ['Cash', 'QRIS', 'Transfer'])->default('Cash');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('paid_amount')->nullable();
            $table->integer('change_amount')->nullable();
            $table->float('discount_percentage', 5, 2)->nullable();
            $table->integer('discount_amount')->nullable();
            $table->float('tax_percentage', 5, 2)->nullable();
            $table->integer('tax_amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
