<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->insertOrIgnore([
            'key'   => 'bank_code',
            'value' => '',
            'type'  => 'string',
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'bank_code')->delete();
    }
};
