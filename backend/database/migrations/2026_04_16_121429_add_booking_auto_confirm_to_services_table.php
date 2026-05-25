<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Was: add booking_auto_confirm to services.
     * Not needed for CampingForYou — all bookings require manual confirmation.
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
