<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('use_opening_hours')->default(true)->after('is_active');
            $table->json('availability_schedule')->nullable()->after('use_opening_hours');
            $table->boolean('block_holidays')->default(false)->after('availability_schedule');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['use_opening_hours', 'availability_schedule', 'block_holidays']);
        });
    }
};
