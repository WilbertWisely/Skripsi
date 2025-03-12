<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesItem extends Model
{
    protected $fillable = ['sales_id', 'stock_id', 'qty', 'price'];

    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function getPriceAttribute()
    {
        return $this->stock->sell_price;  
    }

    public function getTotalPriceAttribute()
    {
        return $this->qty * $this->price; 
    }
}

