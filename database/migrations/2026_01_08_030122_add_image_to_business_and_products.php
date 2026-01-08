<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('image')->nullable();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->string('image')->nullable();
        });
    }
    public function down() {
        Schema::table('businesses', function ($table) { $table->dropColumn('image'); });
        Schema::table('products', function ($table) { $table->dropColumn('image'); });
    }
};
