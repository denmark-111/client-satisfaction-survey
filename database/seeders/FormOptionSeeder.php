<?php

namespace Database\Seeders;

use App\Models\FormOption;
use Illuminate\Database\Seeder;

class FormOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $centers = [
            ['code' => 'CO', 'label' => 'National (Central Office)'],
            ['code' => 'NL', 'label' => 'North Luzon'],
            ['code' => 'SL', 'label' => 'South Luzon'],
            ['code' => 'CV', 'label' => 'Central Visayas'],
            ['code' => 'WV', 'label' => 'Western Visayas'],
            ['code' => 'NM', 'label' => 'North Mindanao'],
            ['code' => 'SM', 'label' => 'South Mindanao'],
        ];

        foreach ($centers as $index => $center) {
            FormOption::updateOrCreate(
                ['category' => 'center', 'code' => $center['code']],
                [
                    'label' => $center['label'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }

        $regions = [
            ['code' => 'R01',   'label' => 'Region I - Ilocos Region'],
            ['code' => 'R02',   'label' => 'Region II - Cagayan Valley'],
            ['code' => 'R03',   'label' => 'Region III - Central Luzon'],
            ['code' => 'R04A',  'label' => 'Region IV - A - CALABARZON'],
            ['code' => 'R04B',  'label' => 'Region IV - B - MIMAROPA'],
            ['code' => 'R05',   'label' => 'Region V - Bicol Region'],
            ['code' => 'R06',   'label' => 'Region VI - Western Visayas'],
            ['code' => 'R07',   'label' => 'Region VII - Central Visayas'],
            ['code' => 'R08',   'label' => 'Region VIII - Eastern Visayas'],
            ['code' => 'R09',   'label' => 'Region IX - Zamboanga Peninsula'],
            ['code' => 'R10',   'label' => 'Region X - Northern Mindanao'],
            ['code' => 'R11',   'label' => 'Region XI - Davao Region'],
            ['code' => 'R12',   'label' => 'Region XII - SOCCSKSARGEN'],
            ['code' => 'R13',   'label' => 'Region XIII - Caraga'],
            ['code' => 'NCR',   'label' => 'NCR - National Capital Region'],
            ['code' => 'CAR',   'label' => 'CAR - Cordillera Administrative Region'],
            ['code' => 'BARMM', 'label' => 'BARMM - Bangsamoro Autonomous Region in Muslim Mindanao'],
        ];

        foreach ($regions as $index => $region) {
            FormOption::updateOrCreate(
                ['category' => 'region', 'code' => $region['code']],
                [
                    'label' => $region['label'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
