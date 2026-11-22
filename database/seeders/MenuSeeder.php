<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\OptionChoice;
use App\Models\OptionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topping = [
            ['Mie', true, false, [
                'Mie kuning' => 0,
                'Bihun' => 0,
                'Mie dan bihun' => 0,
                'Tanpa mie dan bihun' => 0
            ]],
            ['Level Pedas', true, false, [
                'Tidak pedas' => 0,
                'Sedang' => 0,
                'Pedas' => 0,
                'Sangat pedas' => 0
            ]],
            ['Extra Topping', false, true, [
                'Telur rebur' => 5000,
                'Pangsit goreng' => 4000,
                'Tetelan' => 5000,
                'Bakso urat kecil' => 3000,
                'Bakso urat besar' => 6000,
                'Bakso polos kecil' => 3000,
                'Bakso polos besar' => 6000,
                'Extra kuah' => 0
            ]]
        ];

        $drink = [
            ['Suhu', true, false, [
                'Es' => 0,
                'Hangat' => 0
            ]],
            ['Gula', true, false, [
                'Normal' => 0,
                'Setengah' => 0,
                'Tanpa gula' => 0
            ]]
        ];

        $menus = [
            ['Bakso Urat Komplit', 'Bakso', 20000, 50, 'Bakso urat yang kenyal dengan kuah kaldu sapi', $topping],
            ['Bakso Polos Komplit', 'Bakso', 20000, 35, 'Bakso polos yang kenyal dengan kuah kaldu sapi', $topping],
            ['Bakso Telur', 'Bakso', 22000, 30, 'Bakso isi telur puyuh utuh', $topping],
            ['Paket Hemat Berdua', 'Paket', 38000, 20, '1 mangkuk bakso urat dan 1 mangkuk bakso polos', $topping],
            ['Paket Hemat Single', 'Paket', 23000, 25, '1 mangkuk bakso (polos/urat) dan 1 teh', $topping],
            ['Pangsit Goreng (5 pcs)', 'Extra Topping', 10000, 45, '5 pcs pangsit gorang yang renyah', []],
            ['Kerupuk Keriting', 'Extra Topping', 5000, 100, '1 bungkus kecil kerupuk', []],
            ['Teh', 'Minuman', 5000, 100, 'Teh segar dengan gula sesuai selera', $drink],
            ['Es Jeruk', 'Minuman', 10000, 40, 'Jeruk peras segar', $drink],
            ['Bakso Frozen 1 kg', 'Frozen Food', 100000, 10, 'Tahan 2 minggu di freezer', []],
            ['Bakso Frozen 1/2 kg', 'Frozen Food', 50000, 20, 'Tahan 2 minggu di freezer', []]
        ];

        foreach ($menus as [$name, $cat, $price, $stock, $desc, $groups]) {
            $category = Category::where('name', $cat)->firstOrFail();

            $menu = Menu::updateOrCreate(
                ['name' => $name],
                [
                    'category_id' => $category->id,
                    'price' => $price,
                    'stock' => $stock,
                    'description' => $desc
                ]
            );

            foreach ($groups as [$groupName, $required, $multiple, $choices]) {
                $group = OptionGroup::updateOrCreate(
                    ['menu_id' => $menu->id, 'name' =>$groupName],
                    ['is_required' => $required, 'is_multiple' => $multiple]
                );

                foreach ($choices as $choiceName => $extraPrice) {
                    OptionChoice::updateOrCreate(
                        ['option_group_id' => $group->id, 'name' => $choiceName],
                        ['extra_price' => $extraPrice]
                    );
                }
            }
        }
    }
}
