<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('token', 64)->nullable()->unique()->after('id');
        });

        DB::table('bookings')->whereNull('token')->get()->each(function ($booking) {
            DB::table('bookings')->where('id', $booking->id)->update(['token' => Str::random(32)]);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('token', 64)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
};
