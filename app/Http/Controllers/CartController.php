<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Promo;
use App\Services\Cart;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function add(Request $request, Cart $cart){
        $data = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'qty' => 'required|integer|min:1',
            'choices' => 'nullable|array',
            'choices.*' => 'integer',
            'note' => 'nullable|string|max:255'
        ]);

        $menu = Menu::with('optionGroups.choices')->findOrFail($data['menu_id']);
        $chosen = collect($data['choices'] ?? [])->map(fn ($id) => (int) $id)->unique();

        $validIds = $menu->optionGroups->flatMap(fn ($g) => $g->choices->pluck('id'));
        if ($chosen->diff($validIds)->isNotEmpty()){
            throw ValidationException::withMessages(['choices' => 'Pilihan tidak valid']);
        }

        foreach ($menu->optionGroups as $group){
            $count = $group->choices->pluck('id')->intersect($chosen)->count();
            if($group->is_required && $count === 0){
                throw ValidationException::withMessages(['choices' => "Pilih {$group->name} dulu"]);
            }

            if(! $group->is_multiple && $count > 1){
                throw ValidationException::withMessages(['choices' => "{$group->name} hanya boleh 1 pilihan"]);
            }
        }

        $inCart = collect($cart->items())->where('menu_id', $menu->id)->sum('qty');

        if($inCart + $data['qty'] > $menu->stock){
            throw ValidationException::withMessages([
                'qty' => "Stok {$menu->name} tersisa {$menu->stock}, di keranjang sudah {$inCart}"
            ]);
        }

        $cart->add($menu, $data['qty'], $chosen->values()->all(), $data['note'] ?? null);

        if($request->boolean('buy_now')){
            return redirect()->route('checkout.show');
        }

        return back()->with('status', "{$menu->name} berhasil masuk keranjang");
    }

    public function update(Request $request, Cart $cart, $index) {
        $data = $request->validate(['qty' => 'required|integer|min:1']);
        $items = $cart->items();
        abort_unless(isset($items[$index]), 404);

        $menu = Menu::findOrFail($items[$index]['menu_id']);
        $others = collect($items)->except([$index])->where('menu_id', $menu->id)->sum('qty');
        if($others + $data['qty'] > $menu->stock){
            throw ValidationException::withMessages(['qty' => "Stok {$menu->name} tersisa {$menu->stock}"]);
        }

        $cart->update((int) $index, $data['qty']);
        return back();
    }

    public function remove(Cart $cart, $index){
        $cart->remove((int) $index);
        return back();
    }

    public function promo(Request $request, Cart $cart){
        $request->validate(['code' => 'required|string']);
        $promo = Promo::where('code', strtoupper($request->code))->first();
        if(!$promo){
            throw ValidationException::withMessages(['code' => 'Kode promo tidak valid']);
        }

        if ($cart->subtotal() < $promo->min_spend){
            $min = number_format($promo->min_spend, 0, ',', '.');
            throw ValidationException::withMessages(['code' => "Belanja minimal Rp{$min}"]);
        }
        
        session(['promo_code' => $promo->code]);
        return back()->with('status', 'Promo berhasil dipasang');
    }
}
