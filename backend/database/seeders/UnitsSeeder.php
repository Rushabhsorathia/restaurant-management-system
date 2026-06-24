<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $units = [
            ['name' => 'Kilogram', 'code' => 'kg', 'base_unit_id' => null, 'conversion_factor' => 1],
            ['name' => 'Gram', 'code' => 'g', 'base_unit_id' => null, 'conversion_factor' => 1],
            ['name' => 'Litre', 'code' => 'l', 'base_unit_id' => null, 'conversion_factor' => 1],
            ['name' => 'Millilitre', 'code' => 'ml', 'base_unit_id' => null, 'conversion_factor' => 1],
            ['name' => 'Piece', 'code' => 'pc', 'base_unit_id' => null, 'conversion_factor' => 1],
            ['name' => 'Dozen', 'code' => 'dz', 'base_unit_id' => null, 'conversion_factor' => 12],
            ['name' => 'Box', 'code' => 'box', 'base_unit_id' => null, 'conversion_factor' => 1],
        ];

        foreach ($units as $u) {
            DB::table('units')->updateOrInsert(
                ['code' => $u['code']],
                $u + ['created_at' => $now, 'updated_at' => $now]
            );
        }

        $gramId = DB::table('units')->where('code', 'g')->value('id');
        if ($gramId) {
            DB::table('units')->where('code', 'kg')->update(['base_unit_id' => $gramId, 'conversion_factor' => 1000]);
        }
        $mlId = DB::table('units')->where('code', 'ml')->value('id');
        if ($mlId) {
            DB::table('units')->where('code', 'l')->update(['base_unit_id' => $mlId, 'conversion_factor' => 1000]);
        }
    }
}
