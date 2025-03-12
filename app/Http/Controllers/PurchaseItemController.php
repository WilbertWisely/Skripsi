<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storepurchase_itemRequest;
use App\Http\Requests\Updatepurchase_itemRequest;
use App\Models\purchase_item;

class PurchaseItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Storepurchase_itemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(purchase_item $purchase_item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(purchase_item $purchase_item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updatepurchase_itemRequest $request, purchase_item $purchase_item)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(purchase_item $purchase_item)
    {
        //
    }
}
