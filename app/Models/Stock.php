<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Stock extends Model
{
    protected $table = 'stocks';

    protected $fillable = [
        'item_name',
        'qty',
        'buy_price',
        'sell_price',
        'unit',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'unit' => 'string',
    ];

    public $timestamps = false;

    /**
     * Reset the AUTO_INCREMENT after deletion (SQLite specific)
     */
    public static function resetAutoIncrement()
    {
        if (DB::getDriverName() === 'sqlite') {
            // PRAGMA statement to reset auto-increment counter
            DB::statement('DELETE FROM sqlite_sequence WHERE name = "stocks"');
            DB::statement('VACUUM');
        }
    }
}
