<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('businesses', function (Blueprint $table) {
            $table->time('open_at')->default('08:00');
            $table->time('close_at')->default('22:00');
        });
    }
    public function down() {
        Schema::table('businesses', function ($table) {
            $table->dropColumn(['open_at', 'close_at']);
        });
    }
};
