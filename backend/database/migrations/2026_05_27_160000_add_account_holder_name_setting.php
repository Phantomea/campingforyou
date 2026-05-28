<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->insertOrIgnore([
            'key'   => 'account_holder_name',
            'value' => '',
            'type'  => 'string',
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'account_holder_name')->delete();
    }
};
