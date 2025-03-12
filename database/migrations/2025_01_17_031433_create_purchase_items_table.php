<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseItemsTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('stock_id'); // This is the key linking purchase items to stock
            $table->decimal('qty', 10, 2); // Quantity purchased
            $table->decimal('price_per_unit', 10, 2); // Price per unit for this purchase
            $table->decimal('total_price', 10, 2); // Total price for this item
            $table->timestamps();

            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade'); // Foreign key for stock
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_items');
    }
}
