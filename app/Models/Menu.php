<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['category_id', 'name', 'description',
                            'price', 'stock', 'image'];
    
    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function optionGroups() {
        return $this->hasMany(OptionGroup::class);
    }

    public function orderDetails() {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews(){
        return $this->hasMany(Review::class);
    }
}
