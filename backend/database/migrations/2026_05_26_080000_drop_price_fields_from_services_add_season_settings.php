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
            $table->dropColumn(['price', 'price_per_day']);
        });

        // Vloží defaultné sezónne nastavenia ak ešte neexistujú
        $defaults = [
            'season_start_month' => '5',
            'season_start_day'   => '1',
            'season_end_month'   => '9',
            'season_end_day'     => '1',
        ];
        foreach ($defaults as $key => $value) {
            DB::table('settings')->insertOrIgnore([
                'key'   => $key,
                'value' => $value,
                'type'  => 'string',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('slug');
            $table->decimal('price_per_day', 10, 2)->nullable()->after('price');
        });
    }
};
