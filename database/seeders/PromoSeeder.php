<?php

namespace Database\Seeders;

use App\Models\Promo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Promo::updateOrCreate(
            ['code' => 'HEMAT1212'],
            [
                'type' => 'percent', 
                'value' => 10,
                'min_spend' => 30000
            ]
        );
        
        Promo::updateOrCreate(
            ['code' => 'GRATISONGKIR'],
            [
                'type' => 'free_shipping', 
                'value' => 0,
                'min_spend' => 0
            ]
        );
    }
}
