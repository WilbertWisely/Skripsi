<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTreatments extends Model
{
    protected $fillable = [
        'sales_id',
        'treatment_id',
        'quantity',
        'unit_price',
    ];

    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function getUnitPriceAttribute()
    {
        return $this->treatment->treatment_price;  
    }

    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->unit_price;  
    }
}


