<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_id', 'stock_id', 'qty', 'price_per_unit', 'total_price'];

    protected $casts = [
        'qty' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}