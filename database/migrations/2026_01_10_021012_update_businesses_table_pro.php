<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('businesses', function (Blueprint $table) {
            if (!Schema::hasColumn('businesses', 'delivery_price')) {
                $table->decimal('delivery_price', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('businesses', 'phone')) {
                $table->string('phone')->nullable();
            }
        });
    }
    public function down() {
        Schema::table('businesses', function ($table) {
            $table->dropColumn(['delivery_price', 'phone']);
        });
    }
};
