<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            // Some products (e.g. tools, seeds without a printed date) never expire.
            $table->date('expiration_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->date('expiration_date')->nullable(false)->change();
        });
    }
};
