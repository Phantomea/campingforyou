<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('billing_company')->nullable()->after('note');
            $table->string('billing_ico')->nullable()->after('billing_company');
            $table->string('billing_dic')->nullable()->after('billing_ico');
            $table->string('billing_street')->nullable()->after('billing_dic');
            $table->string('billing_city')->nullable()->after('billing_street');
            $table->string('billing_zip')->nullable()->after('billing_city');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['billing_company', 'billing_ico', 'billing_dic', 'billing_street', 'billing_city', 'billing_zip']);
        });
    }
};
