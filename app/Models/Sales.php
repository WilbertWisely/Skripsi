<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sales extends Model
{
    protected $fillable = [
        'transaction_date',
        'user_id',
        'payment_method_name',
        'total_price',
        'discount', 
        'total_price_after_discount',
    ];

    public function salesItems()
    {
        return $this->hasMany(SalesItem::class);
    }

    public function salesTreatments()
    {
        return $this->hasMany(SalesTreatments::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Calculate total price including discount
    public function calculateTotalPrice()
    {
        $itemsTotal = $this->salesItems->sum(function ($item) {
            return $item->qty * $item->price;
        });

        $treatmentsTotal = $this->salesTreatments->sum(function ($treatment) {
            return $treatment->quantity * $treatment->unit_price;
        });

        $totalPrice = $itemsTotal + $treatmentsTotal;

        // Apply discount if exists
        if ($this->discount) {
            $totalPrice -= $this->discount;
        }

        return $totalPrice;
    }

    protected static function booted()
    {
        static::saving(function ($sales) {
            $sales->total_price = $sales->calculateTotalPrice();
            $sales->total_price_after_discount = $sales->total_price;
        });

        // Stock validation before creating
        static::creating(function ($sales) {
            foreach ($sales->salesItems as $item) {
                $stock = Stock::find($item->stock_id);
                if (!$stock || $item->qty > $stock->qty) {
                    throw new \Exception("Insufficient stock for {$stock->item_name}. Available: {$stock->qty}, Requested: {$item->qty}");
                }
            }
        });

        // Deduct stock on sale creation
        static::created(function ($sales) {
            DB::beginTransaction();
            try {
                foreach ($sales->salesItems as $item) {
                    $stock = Stock::find($item->stock_id);
                    $stock->decrement('qty', $item->qty);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        });

        // Restore stock on sale deletion
        static::deleted(function ($sales) {
            DB::beginTransaction();
            try {
                foreach ($sales->salesItems as $item) {
                    $stock = Stock::find($item->stock_id);
                    $stock->increment('qty', $item->qty);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        });
    }

    // Adding sales items
    public function addSalesItems(array $items)
    {
        foreach ($items as $itemData) {
            $this->salesItems()->create([
                'stock_id' => $itemData['stock_id'],
                'qty' => $itemData['qty'],
                'price' => $itemData['price'],
            ]);
        }
    }

    // Adding sales treatments
    public function addSalesTreatments(array $treatments)
    {
        foreach ($treatments as $treatmentData) {
            $this->salesTreatments()->create([
                'treatment_id' => $treatmentData['treatment_id'],
                'quantity' => $treatmentData['quantity'],
                'unit_price' => $treatmentData['unit_price'],
            ]);
        }
    }
}
