<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->insertOrIgnore([
            'key'   => 'full_payment_deadline_days',
            'value' => '14',
            'type'  => 'string',
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'full_payment_deadline_days')->delete();
    }
};
