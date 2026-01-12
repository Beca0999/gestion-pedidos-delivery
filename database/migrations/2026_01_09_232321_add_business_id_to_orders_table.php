<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'business_id')) {
                $table->foreignId('business_id')->nullable()->constrained()->onDelete('cascade');
            }
        });
    }
    public function down() {
        Schema::table('orders', function ($table) { 
            $table->dropColumn('business_id'); 
        });
    }
};
