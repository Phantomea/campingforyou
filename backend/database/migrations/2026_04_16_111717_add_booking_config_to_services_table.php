<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Was: add time-slot booking config (booking_from, booking_to, booking_duration).
     * Not needed for CampingForYou — caravans use date-range bookings.
     */
    public function up(): void
    {
        // No-op
    }

    public function down(): void
    {
        // No-op
    }
};
