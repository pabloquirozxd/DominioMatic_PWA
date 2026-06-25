<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('companies')->updateOrInsert(
            ['slug' => 'colegio-demo.com'],
            [
                'name' => 'Colegio Demo',
                'logo_path' => null,
                'primary_color' => '#2563EB',
                'secondary_color' => '#16A34A',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('companies')
            ->where('slug', 'colegio-demo.com')
            ->delete();
    }
};