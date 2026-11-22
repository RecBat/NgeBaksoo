<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\OptionChoice;
use App\Models\Promo;

class Cart{
    const KEY = 'cart';

    public function items(){
        return session(self::KEY, []);
    }

    public function add(Menu $menu, int $qty, array $choiceIds, $note){
        $items = $this->items();
        $items[] = [
            'menu_id' => $menu->id,
            'qty' => $qty,
            'choice_ids' => $choiceIds,
            'note' => $note
        ];
        session([self::KEY => $items]);
    }

    public function update(int $index, int $qty){
        $items = $this->items();
        if (isset($items[$index])){
            $items[$index]['qty'] = $qty;
            session([self::KEY => $items]);
        }
    }

    public function remove(int $index){
        $items = $this->items();
        unset($items[$index]);
        session([self::KEY => array_values($items)]);
    }

    public function clear(){
        session()->forget([self::KEY, 'promo_code']);
    }

    public function lines(){
        $lines = [];

        foreach ($this->items() as $i => $item){
            $menu = Menu::find($item['menu_id']);
            if(!$menu) continue;

            $choices = OptionChoice::whereIn('id', $item['choice_ids'])->get();
            $unitPrice = $menu->price + $choices->sum('extra_price');

            $lines[] = [
                'index' => $i,
                'menu' => $menu,
                'choices' => $choices,
                'qty' => $item['qty'],
                'note' => $item['note'],
                'unitPrice' => $unitPrice,
                'subtotal' => $unitPrice * $item['qty']
            ];
        }

        return $lines;
    }

    public function stockIssues(){
        $issues = [];
        $grouped = collect($this->lines())->groupBy(fn ($line) => $line['menu']->id);
        
        foreach($grouped as $lines) {
            $menu = $lines->first()['menu'];
            $qty = $lines->sum('qty');
            if($qty > $menu->stock){
                $issues[] = "Stok {$menu->name} tersisa {$menu->stock}, di keranjang terdapat {$qty}";
            }
        }

        return $issues;
    }

    public function subtotal(){
        return collect($this->lines())->sum('subtotal');
    }

    public function promo(){
        $code = session('promo_code');
        return $code ? Promo::where('code', $code)->first() : null;
    }

    public function discount(int $subtotal, int $deliveryFee = 0){
        $promo = $this->promo();
        if (!$promo || $subtotal < $promo->min_spend) return 0;

        return match($promo->type){
            'percent' => intdiv($subtotal * (int) $promo->value, 100),
            'free_shipping' => $deliveryFee,
            default => 0
        };
    }
}