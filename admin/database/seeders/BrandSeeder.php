<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Brand::truncate();

        $brands = [
            [
                'name' => 'Hyola Ultra 30K'
            ],
            [
                'name' => 'PIXL 8000'
            ],
            [
                'name' => 'PIXL Duo'
            ],
            [
                'name' => 'IVG Pro'
            ],
            [
                'name' => 'O Pro by Insta 10k'
            ],
            [
                'name' => 'Fuji Energy'
            ],
            [
                'name' => 'Duracell'
            ],
             [
                'name' => 'Panasonic'
             ],
              [
                'name' => 'Energizer'
              ],
              [
                'name' => 'Till Rolls'
              ],
              [
                'name' => 'FIFO',
            ],
             [
                'name' => 'Reach On USB',
            ],
            [
                'name' => 'Designer Fragrances'
            ],
             [
                'name' => 'Skins'
             ],
             [
                'name' => 'Warrior'
             ],
             [
                'name' => 'IVG Intense Nic Salts'
             ],
             [
                'name' => 'Elux Legend Nic Salt'
             ],
             [
                'name' => 'Elux Firerose'
             ],
              [
                'name' => 'Edge Liq Nic Salts'
              ],
               [
                'name' => 'Nordic Spirit!'
               ],
                  [
                'name' => 'Signature'
             ]
        ];

        Brand::insert($brands);
    }
}
