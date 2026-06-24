<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\TaxConfig;
use Illuminate\Database\Seeder;

class TaxConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['name' => 'GST 5% (CGST+SGST)', 'type' => 'intra_state', 'cgst' => 2.5, 'sgst' => 2.5, 'igst' => 0, 'cess' => 0],
            ['name' => 'GST 12% (CGST+SGST)', 'type' => 'intra_state', 'cgst' => 6, 'sgst' => 6, 'igst' => 0, 'cess' => 0],
            ['name' => 'GST 18% (CGST+SGST)', 'type' => 'intra_state', 'cgst' => 9, 'sgst' => 9, 'igst' => 0, 'cess' => 0],
            ['name' => 'GST 28% (CGST+SGST)', 'type' => 'intra_state', 'cgst' => 14, 'sgst' => 14, 'igst' => 0, 'cess' => 0],
            ['name' => 'IGST 18%', 'type' => 'inter_state', 'cgst' => 0, 'sgst' => 0, 'igst' => 18, 'cess' => 0],
        ];

        foreach (Restaurant::all() as $restaurant) {
            foreach ($configs as $cfg) {
                TaxConfig::firstOrCreate(
                    [
                        'restaurant_id' => $restaurant->id,
                        'name' => $cfg['name'],
                    ],
                    [
                        'type' => $cfg['type'],
                        'cgst_rate' => $cfg['cgst'],
                        'sgst_rate' => $cfg['sgst'],
                        'igst_rate' => $cfg['igst'],
                        'cess_rate' => $cfg['cess'],
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
