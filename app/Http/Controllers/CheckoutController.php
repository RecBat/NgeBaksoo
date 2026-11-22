<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\SalesChannel;
use App\Services\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    const DELIVERY_FEE = 10000;

    public function show(Cart $cart){
        $lines = $cart->lines();
        if(empty($lines)){
            return redirect()->route('menu');
        }
        
        return view('checkout.show', [
            'lines' => $lines,
            'subtotal' => $cart->subtotal(),
            'promo' => $cart->promo(),
            'deliveryFee' => self::DELIVERY_FEE,
            'issues' => $cart->stockIssues()
        ]);
    }

    public function store(Request $request, Cart $cart){
        $data = $request->validate([
            'cust_name' => 'required|string|max:255',
            'cust_phone' => 'required|string|max:20',
            'type' => 'required|in:pickup,delivery,dine_in',
            'method' => 'required|in:qris,cash,transfer'
        ]);

        if(empty($cart->items())){
            return redirect()->route('menu');
        }

        if(empty($cart->lines())){
            return redirect()->route('menu');
        }

        $channel = SalesChannel::where('name', 'Langsung')->firstOrFail();
        $order = DB::transaction(function() use ($cart, $data, $channel) {
            $lines = $cart->lines();

            $needs = collect($lines)->groupBy(fn ($line) => $line['menu']->id)
                ->map(fn($group) => $group->sum('qty'));
            
            $menus = Menu::whereIn('id', $needs->keys())->lockForUpdate()->get()->keyBy('id');

            foreach ($needs as $menuId => $qty){
                $menu = $menus[$menuId] ?? null;
                if(!$menu || $menu->stock < $qty){
                    $name = $menu->name ?? 'Menu';
                    $left = $menu->stock ?? 0;
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$name} tersisa {$left}, di keranjang terdapat {$qty}"
                    ]);
                }
            }

            $subtotal = collect($lines)->sum('subtotal');
            $fee = $data['type'] === 'delivery' ? self::DELIVERY_FEE : 0;
            $discount = $cart->discount($subtotal, $fee);
            $promo = $discount > 0 ? $cart->promo() : null;

            $order = Order::create([
                'sales_channel_id' => $channel->id,
                'promo_id' => $promo?->id,
                'cust_name' => $data['cust_name'],
                'cust_phone' => $data['cust_phone'],
                'type' => $data['type'],
                'status' => 'new',
                'total' => $subtotal + $fee - $discount
            ]);

            foreach ($lines as $line){
                $detail = $order->details()->create([
                    'menu_id' => $line['menu']->id,
                    'qty' => $line['qty'],
                    'price' => $line['unitPrice'],
                    'note' => $line['note']
                ]);

                foreach ($line['choices'] as $choice){
                    $detail->orderCustomizations()->create([
                        'option_choice_id' => $choice->id,
                        'name' => $choice->name,
                        'extra_price' => $choice->extra_price
                    ]);
                }
            }

            $order->payment()->create([
                'method' => $data['method'],
                'status' => 'paid'
            ]);

            foreach ($needs as $menuId => $qty){
                $menus[$menuId]->decrement('stock', $qty);
            }

            return $order;
        });

        $cart->clear();
        return redirect()->route('order.show', $order);
    }
}
