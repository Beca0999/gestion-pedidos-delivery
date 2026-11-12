<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recommended_product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('support', 10, 8);
            $table->decimal('confidence', 10, 8);
            $table->decimal('lift', 12, 6);
            $table->timestamps();
            $table->unique(['product_id', 'recommended_product_id']);
            $table->index(['product_id', 'confidence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_recommendations');
    }
};
