<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function show(Order $order){
        $order->load('details.menu', 'details.orderCustomizations', 'payment', 'salesChannel', 'reviews');
        
        return view('order.show', [
            'order' => $order,
            'statuses' => [
                'new' => 'Pesanan diterima',
                'processing' => 'Pesanan sedang dibuat',
                'ready' => 'Pesanan siap disajikan',
                'done' => 'Pesanan selesai'
            ],
            'reviewedMenuIds' => $order->reviews->pluck('menu_id')
        ]);
    }
}
