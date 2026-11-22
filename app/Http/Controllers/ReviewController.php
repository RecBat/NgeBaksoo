<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request, Order $order){
        abort_unless($order->status === 'done', 403);

        $data = $request->validate([
            'menu_id' => 'required|integer',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500'
        ]);

        if(! $order->details()->where('menu_id', $data['menu_id'])->exists()){
            throw ValidationException::withMessages(['menu_id' => 'Menu tidak ada dalam pesanan ini']);
        }

        if ($order->reviews()->where('menu_id', $data['menu_id'])->exists()){
            throw ValidationException::withMessages(['menu_id' => 'Menu ini sudah kamu ulas']);
        }

        $order->reviews()->create($data);
        return back()->with('status', 'Terima kasih atas ulasannya');
    }
}
