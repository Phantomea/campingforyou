<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Technické parametre
            $table->unsignedSmallInteger('length_cm')->nullable()->after('license_plate');
            $table->unsignedSmallInteger('width_cm')->nullable()->after('length_cm');
            $table->unsignedSmallInteger('height_cm')->nullable()->after('width_cm');
            $table->unsignedSmallInteger('weight_kg')->nullable()->after('height_cm');
            $table->unsignedSmallInteger('max_weight_kg')->nullable()->after('weight_kg');
            $table->string('tow_ball_size', 20)->nullable()->after('max_weight_kg');
            $table->unsignedTinyInteger('sleeping_capacity')->nullable()->after('tow_ball_size');

            // Vybavenie
            $table->boolean('has_aircon')->default(false)->after('sleeping_capacity');
            $table->boolean('has_solar')->default(false)->after('has_aircon');
            $table->boolean('has_shower')->default(false)->after('has_solar');
            $table->boolean('has_toilet')->default(false)->after('has_shower');
            $table->boolean('has_kitchen')->default(true)->after('has_toilet');
            $table->boolean('has_heating')->default(false)->after('has_kitchen');
            $table->boolean('has_awning')->default(false)->after('has_heating');
            $table->boolean('has_bike_rack')->default(false)->after('has_awning');

            // Galéria (JSON pole ciest k súborom)
            $table->json('images')->nullable()->after('image');

            // Sezónne ceny
            $table->decimal('price_low_season', 8, 2)->nullable()->after('price_per_day');
            $table->decimal('price_high_season', 8, 2)->nullable()->after('price_low_season');

            // Podmienky prenájmu
            $table->unsignedTinyInteger('min_rental_days')->default(1)->after('sort_order');
            $table->decimal('deposit', 8, 2)->nullable()->after('min_rental_days');
            $table->text('internal_note')->nullable()->after('deposit');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'length_cm', 'width_cm', 'height_cm', 'weight_kg', 'max_weight_kg',
                'tow_ball_size', 'sleeping_capacity',
                'has_aircon', 'has_solar', 'has_shower', 'has_toilet',
                'has_kitchen', 'has_heating', 'has_awning', 'has_bike_rack',
                'images',
                'price_low_season', 'price_high_season',
                'min_rental_days', 'deposit', 'internal_note',
            ]);
        });
    }
};
