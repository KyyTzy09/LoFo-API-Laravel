<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(string $itemId)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $itemId)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $itemId)
    {
    }
}
