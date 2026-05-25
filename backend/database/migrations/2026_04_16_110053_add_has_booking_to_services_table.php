<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This migration is kept for historical consistency but the has_booking column
     * is no longer used in CampingForYou (every caravan supports date-range bookings).
     */
    public function up(): void
    {
        // No-op: caravan bookings are always enabled per caravan record
    }

    public function down(): void
    {
        // No-op
    }
};
