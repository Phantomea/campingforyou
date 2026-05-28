<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('has_roof_aircon')->default(false)->after('has_aircon');
            $table->boolean('has_gas_alarm')->default(false)->after('has_bike_rack');
            $table->boolean('has_smoke_alarm')->default(false)->after('has_gas_alarm');
            $table->boolean('has_co_alarm')->default(false)->after('has_smoke_alarm');
            $table->boolean('has_spare_wheel')->default(false)->after('has_co_alarm');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'has_roof_aircon',
                'has_gas_alarm',
                'has_smoke_alarm',
                'has_co_alarm',
                'has_spare_wheel',
            ]);
        });
    }
};
