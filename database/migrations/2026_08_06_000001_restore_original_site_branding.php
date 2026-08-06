<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('projects')
            ->where('slug', 'denghy-engineering-site')
            ->where('name', 'denghy / 工程现场')
            ->update([
                'name' => '程序员的个人修养 / 工程现场',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('projects')
            ->where('slug', 'denghy-engineering-site')
            ->where('name', '程序员的个人修养 / 工程现场')
            ->update([
                'name' => 'denghy / 工程现场',
                'updated_at' => now(),
            ]);
    }
};
