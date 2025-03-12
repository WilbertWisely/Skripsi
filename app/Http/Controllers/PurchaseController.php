<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorepurchaseRequest;
use App\Http\Requests\UpdatepurchaseRequest;
use App\Models\purchase;

class PurchaseController extends Controller
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
    public function store(StorepurchaseRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(purchase $purchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatepurchaseRequest $request, purchase $purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(purchase $purchase)
    {
        //
    }
}
