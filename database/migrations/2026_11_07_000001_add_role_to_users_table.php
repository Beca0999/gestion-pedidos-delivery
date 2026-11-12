<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->index();
        });

        DB::table('users')->where('id', 1)->update(['role' => 'admin']);
        DB::table('users')
            ->where('id', '!=', 1)
            ->whereIn('id', DB::table('businesses')->whereNotNull('user_id')->select('user_id'))
            ->update(['role' => 'owner']);
        DB::table('users')
            ->where('role', 'customer')
            ->whereIn('id', DB::table('riders')->whereNotNull('user_id')->select('user_id'))
            ->update(['role' => 'rider']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
