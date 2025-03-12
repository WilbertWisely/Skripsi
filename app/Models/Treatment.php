<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Treatment extends Model
{
    protected $fillable = ['name', 'category', 'treatment_price'];

    public function salesItems()
    {
        return $this->hasMany(SalesItem::class);
    }

    /**
     * Reset the AUTO_INCREMENT after deletion (SQLite specific)
     */
    public static function resetAutoIncrement()
    {
        if (DB::getDriverName() === 'sqlite') {
            // PRAGMA statement to reset auto-increment counter
            DB::statement('DELETE FROM sqlite_sequence WHERE name = "treatments"');
            DB::statement('VACUUM');
        }
    }
}
