<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Motor / pohon
            $table->string('engine', 100)->nullable()->after('tow_ball_size');
            $table->string('transmission', 50)->nullable()->after('engine');
            $table->string('fuel_consumption', 30)->nullable()->after('transmission');
            $table->unsignedSmallInteger('fuel_tank_l')->nullable()->after('fuel_consumption');
            $table->unsignedSmallInteger('max_towing_kg')->nullable()->after('fuel_tank_l');
            $table->unsignedTinyInteger('driving_seats')->nullable()->after('max_towing_kg');
            $table->string('license_category', 10)->nullable()->after('driving_seats');
            // Kapacity
            $table->unsignedSmallInteger('fresh_water_l')->nullable()->after('license_category');
            $table->unsignedSmallInteger('waste_water_l')->nullable()->after('fresh_water_l');
            $table->unsignedSmallInteger('battery_ah')->nullable()->after('waste_water_l');
            $table->string('battery_type', 30)->nullable()->after('battery_ah');
            $table->unsignedSmallInteger('fridge_l')->nullable()->after('battery_type');
            $table->decimal('awning_m', 4, 1)->nullable()->after('fridge_l');
            // Vybavenie
            $table->boolean('has_outdoor_shower')->default(false)->after('has_spare_wheel');
            $table->boolean('has_reversing_camera')->default(false)->after('has_outdoor_shower');
        });

        // Seed ukázkové parametre pre existujúce karavany
        DB::table('services')->where('slug', 'hobby-de-luxe-540-ul')->update([
            'length_cm'         => 754,
            'width_cm'          => 225,
            'height_cm'         => 271,
            'weight_kg'         => 1358,
            'max_weight_kg'     => 1800,
            'tow_ball_size'     => '50 mm',
            'sleeping_capacity' => 4,
            'license_category'  => 'B',
            'max_towing_kg'     => 1800,
            'fresh_water_l'     => 100,
            'waste_water_l'     => 85,
            'fridge_l'          => 133,
            'battery_ah'        => 95,
            'battery_type'      => 'AGM',
            'awning_m'          => 3.5,
            'has_shower'        => true,
            'has_toilet'        => true,
            'has_kitchen'       => true,
            'has_heating'       => true,
            'has_awning'        => true,
            'has_spare_wheel'   => true,
        ]);

        DB::table('services')->where('slug', 'fendt-bianco-activ-515-sg')->update([
            'length_cm'          => 784,
            'width_cm'           => 231,
            'height_cm'          => 269,
            'weight_kg'          => 1480,
            'max_weight_kg'      => 1800,
            'tow_ball_size'      => '50 mm',
            'sleeping_capacity'  => 5,
            'license_category'   => 'B',
            'max_towing_kg'      => 1800,
            'fresh_water_l'      => 110,
            'waste_water_l'      => 90,
            'fridge_l'           => 142,
            'battery_ah'         => 95,
            'battery_type'       => 'AGM',
            'awning_m'           => 4.0,
            'has_shower'         => true,
            'has_toilet'         => true,
            'has_kitchen'        => true,
            'has_heating'        => true,
            'has_awning'         => true,
            'has_bike_rack'      => true,
            'has_solar'          => true,
            'has_spare_wheel'    => true,
        ]);

    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'engine', 'transmission', 'fuel_consumption', 'fuel_tank_l',
                'max_towing_kg', 'driving_seats', 'license_category',
                'fresh_water_l', 'waste_water_l', 'battery_ah', 'battery_type',
                'fridge_l', 'awning_m',
                'has_outdoor_shower', 'has_reversing_camera',
            ]);
        });
    }
};
