<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    protected $fillable = ['stock_id', 'price_per_unit', 'total_price', 'qty'];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'total_price' => 'decimal:2',
        'qty' => 'decimal:2',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    protected static function booted()
    {
        static::saving(function ($purchase) {
            $purchase->total_price = $purchase->price_per_unit * $purchase->qty;
        });
    
        static::created(function ($purchase) {
            DB::transaction(function () use ($purchase) {
                // Create purchase item
                $purchaseItem = new PurchaseItem([
                    'purchase_id' => $purchase->id,
                    'stock_id' => $purchase->stock_id,
                    'qty' => $purchase->qty,
                    'price_per_unit' => $purchase->price_per_unit,
                    'total_price' => $purchase->total_price,
                ]);
                $purchaseItem->save();
        
                // Update the stock after purchase
                $stock = $purchase->stock;
                if ($stock) {
                    $stock->qty += $purchase->qty;
                    
                    // Calculate new average purchase price
                    $totalPurchaseValue = Purchase::where('stock_id', $stock->id)
                        ->sum(DB::raw('price_per_unit * qty'));
                    $totalPurchaseQty = Purchase::where('stock_id', $stock->id)
                        ->sum('qty');
                    
                    if ($totalPurchaseQty > 0) {
                        $averagePurchasePrice = $totalPurchaseValue / $totalPurchaseQty;
                        $stock->buy_price = $averagePurchasePrice;
                        $stock->sell_price = $averagePurchasePrice * 1.2; // 20% markup
                    }
                    
                    $stock->save();
                }
            });
        });
    
        static::deleted(function ($purchase) {
            DB::transaction(function () use ($purchase) {
                $stock = $purchase->stock;
                
                if ($stock) {
                    // Subtract the quantity purchased
                    $stock->qty -= $purchase->qty;
                    
                    // Recalculate average purchase price after removing this purchase
                    $remainingPurchases = Purchase::where('stock_id', $stock->id)
                        ->where('id', '!=', $purchase->id)
                        ->get();
                    
                    if ($remainingPurchases->count() > 0) {
                        $totalValue = $remainingPurchases->sum(function ($p) {
                            return $p->price_per_unit * $p->qty;
                        });
                        $totalQty = $remainingPurchases->sum('qty');
                        
                        $newAveragePrice = $totalValue / $totalQty;
                        $stock->buy_price = $newAveragePrice;
                        $stock->sell_price = $newAveragePrice * 1.2; // 20% markup
                    } else {
                        // If no purchases remain, use the last calculated average price
                        $lastPurchase = PurchaseItem::where('stock_id', $stock->id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        if ($lastPurchase) {
                            $stock->buy_price = $lastPurchase->price_per_unit;
                            $stock->sell_price = $lastPurchase->price_per_unit * 1.2;
                        }
                    }
                    
                    $stock->save();
                }
                
                // Delete the associated purchase items
                $purchase->purchaseItems()->delete();
            });

            // Reset auto-increment after all records are deleted
            if (Purchase::count() === 0) {
                self::resetAutoIncrement();
            }
        });
    }

    /**
     * Reset the AUTO_INCREMENT for the purchases table
     */
    public static function resetAutoIncrement()
    {
        $driver = DB::getDriverName();
        
        switch ($driver) {
            case 'mysql':
                DB::statement('ALTER TABLE purchases AUTO_INCREMENT = 1');
                break;
            case 'sqlite':
                DB::statement('DELETE FROM sqlite_sequence WHERE name = "purchases"');
                DB::statement('VACUUM');
                break;
            case 'pgsql':
                DB::statement('ALTER SEQUENCE purchases_id_seq RESTART WITH 1');
                break;
        }
    }
}