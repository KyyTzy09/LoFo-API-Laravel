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
        $items = Item::all();
        return response()->json([
            'message' => 'Daftar barang',
            'data' => $items
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userId = $request->user()->id;
        $data = $request->validate([
            'item_name' => 'required',
            'item_info' => 'nullable',
            'status' => 'required|in:TERSEDIA,HILANG',
            'image' => 'image|mimes:jpg,png,jpeg|max:2048'
        ]);

    if ($userId) {
        $data['user_id'] = $userId;
    }

    // upload gambar
    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('item', 'public');
    }

    $item = Item::create($data);

    // generate QR (isi: URL atau ID barang)
    $qrPath = 'qr/qr-'.$item->itemId.'.png';

    $result = Builder::create()
    ->writer(new PngWriter())
    ->data($item->itemId)
    ->size(300)
    ->build();

    Storage::disk('public')->put($qrPath, $result->getString());

    $item->update([
        'qr_url' => $qrPath
    ]);

    return response()->json([
        'message' => 'Barang berhasil ditambahkan',
        'data' => $item
    ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $itemId)
    {
        $item = Item::find($itemId);
        if (!$item) {
            return response()->json([
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'message' => 'Detail barang',
            'data' => $item
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $itemId)
    {
        $item = Item::find($itemId);
        if (!$item) {
            return response()->json([
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        $data = $request->validate([
            'item_name' => 'required',
            'item_info' => 'nullable',
            'status' => 'required|in:TERSEDIA,HILANG',
            'image' => 'image|mimes:jpg,png,jpeg|max:2048'
        ]);

        // upload gambar
        if ($request->hasFile('image')) {
            // hapus gambar lama jika ada
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $request->file('image')->store('item', 'public');
        }

        $item->update($data);

        return response()->json([
            'message' => 'Barang berhasil diperbarui',
            'data' => $item
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $itemId)
    {
        $item = Item::find($itemId);
        if (!$item) {
            return response()->json([
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        // hapus gambar jika ada
        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return response()->json([
            'message' => 'Barang berhasil dihapus'
        ]);
    }
}
