<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storesales_itemRequest;
use App\Http\Requests\Updatesales_itemRequest;
use App\Models\sales_item;

class SalesItemController extends Controller
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
    public function store(Storesales_itemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(sales_item $sales_item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(sales_item $sales_item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updatesales_itemRequest $request, sales_item $sales_item)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(sales_item $sales_item)
    {
        //
    }
}
