<?php

namespace Database\Seeders;

use App\Models\SalesChannel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SalesChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Langsung', 'GoFood', 'GrabFood', 'ShopeeFood'] as $name) {
            SalesChannel::updateOrCreate(
                ['name' => $name]
            );
        }
    }
}
