<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_id')->nullable(); // ID de Mercado Pago
        });
    }
    public function down() {
        Schema::table('orders', function ($table) {
            $table->dropColumn('payment_id');
        });
    }
};
