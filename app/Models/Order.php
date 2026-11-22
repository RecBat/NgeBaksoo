<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = ['order_code', 'sales_channel_id', 'promo_id',
                            'cust_name', 'cust_phone', 'type', 'status', 'total'];
                            
    public function salesChannel(){
        return $this->belongsTo(SalesChannel::class);
    }

    public function promo() {
        return $this->belongsTo(Promo::class);
    }

    public function details() {
        return $this->hasMany(OrderDetail::class);
    }

    public function payment(){
        return $this->hasOne(Payment::class);
    }

    public function reviews(){
        return $this->hasMany(Review::class);
    }

    public static function booted(){
        static::creating(function ($order) {
            $order->order_code ??= 'NBO' . strtoupper(Str::random(6));
        });
    }

    public function getRouteKeyName(){
        return 'order_code';
    }
}
