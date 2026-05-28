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
        $defaults = [
            ['key' => 'deposit_required',        'value' => '0',   'type' => 'boolean'],
            ['key' => 'upfront_payment_percent', 'value' => '100', 'type' => 'string'],
            ['key' => 'season_start_month',      'value' => '',    'type' => 'string'],
            ['key' => 'season_start_day',        'value' => '',    'type' => 'string'],
            ['key' => 'season_end_month',        'value' => '',    'type' => 'string'],
            ['key' => 'season_end_day',          'value' => '',    'type' => 'string'],
        ];

        foreach ($defaults as $setting) {
            \DB::table('settings')->insertOrIgnore($setting);
        }
    }

    public function down(): void
    {
        \DB::table('settings')->whereIn('key', [
            'deposit_required', 'upfront_payment_percent',
            'season_start_month', 'season_start_day', 'season_end_month', 'season_end_day',
        ])->delete();
    }
};
