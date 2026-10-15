<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NaamLibrary;

class NaamLibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $naams = [
            [
                'name' => 'Shri Ram',
                'name_hindi' => 'श्री राम',
                'name_punjabi' => 'ਸ਼੍ਰੀ ਰਾਮ',
                'slug' => 'shri-ram',
                'description' => 'Bhagwan Shri Ram Naam Jap.',
                'parent_id' => null,
                'icon' => null,
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Radha',
                'name_hindi' => 'राधा',
                'name_punjabi' => 'ਰਾਧਾ',
                'slug' => 'radha',
                'description' => 'Mata Radha Naam Jap.',
                'parent_id' => null,
                'icon' => null,
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Shiv',
                'name_hindi' => 'शिव',
                'name_punjabi' => 'ਸ਼ਿਵ',
                'slug' => 'shiv',
                'description' => 'Bhagwan Shiv Naam Jap.',
                'parent_id' => null,
                'icon' => null,
                'status' => 'inactive',
                'sort_order' => 3,
            ],
        ];

        foreach ($naams as $naam) {
            NaamLibrary::updateOrCreate(
                ['slug' => $naam['slug']],
                $naam
            );
        }
    }
}