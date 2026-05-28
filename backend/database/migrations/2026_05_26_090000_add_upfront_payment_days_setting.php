<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->insertOrIgnore([
            'key'   => 'upfront_payment_days',
            'value' => '30',
            'type'  => 'string',
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'upfront_payment_days')->delete();
    }
};
