<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recommended_business_id')->constrained('businesses')->cascadeOnDelete();
            $table->decimal('support', 10, 8);
            $table->decimal('confidence', 10, 8);
            $table->decimal('lift', 12, 6);
            $table->timestamps();
            $table->unique(['business_id', 'recommended_business_id']);
            $table->index(['business_id', 'confidence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_recommendations');
    }
};
