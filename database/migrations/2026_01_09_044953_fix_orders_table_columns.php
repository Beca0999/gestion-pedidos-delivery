<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'address')) {
                $table->text('address')->nullable();
            }
            if (!Schema::hasColumn('orders', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('orders', 'tracking_code')) {
                $table->string('tracking_code')->unique()->nullable();
            }
        });
    }
    public function down() {}
};
