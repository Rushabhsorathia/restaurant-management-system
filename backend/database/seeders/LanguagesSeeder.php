<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguagesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $languages = [
            ['code' => 'en', 'name' => 'English', 'is_active' => true, 'is_default' => true],
            ['code' => 'hi', 'name' => 'Hindi', 'is_active' => true, 'is_default' => false],
            ['code' => 'gu', 'name' => 'Gujarati', 'is_active' => true, 'is_default' => false],
        ];

        foreach ($languages as $lang) {
            DB::table('languages')->updateOrInsert(
                ['code' => $lang['code']],
                $lang + ['created_at' => $now, 'updated_at' => $now]
            );
        }

        // ensure exactly one default
        DB::table('languages')->where('code', '!=', 'en')->update(['is_default' => false]);
    }
}
