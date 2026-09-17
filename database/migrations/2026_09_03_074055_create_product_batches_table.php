<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('batch_number');
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->date('manufactured_date')->nullable();
            $table->date('expiration_date');
            $table->timestamps();

            // FEFO Index: Optimizes queries selecting earliest available non-expired batch
            $table->index(['product_id', 'expiration_date', 'stock_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
