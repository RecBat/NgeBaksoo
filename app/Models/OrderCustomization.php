<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCustomization extends Model
{
    protected $fillable = ['order_detail_id', 'option_choice_id', 'name', 'extra_price'];

    public function orderDetail(){
        return $this->belongsTo(OrderDetail::class);
    }

    public function optionChoice(){
        return $this->belongsTo(OptionChoice::class);
    }
}
