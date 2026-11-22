<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionGroup extends Model
{
    protected $fillable = ['menu_id', 'name', 'is_required', 'is_multiple'];

    public function menu(){
        return $this->belongsTo(Menu::class);
    }

    public function choices(){
        return $this->hasMany(OptionChoice::class);
    }

    protected function casts() {
        return [
            'is_required' => 'boolean',
            'is_multiple' => 'boolean'
        ];
    }
}
