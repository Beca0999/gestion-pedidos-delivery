<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('vehicle_type')->default('Moto'); // Moto, Bici, Auto
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('riders'); }
};
