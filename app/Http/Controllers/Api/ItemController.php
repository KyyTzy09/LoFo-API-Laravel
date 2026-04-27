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
     * Fitur: Cari semua Item, berdasarkan nama, berdasarkan status
     */
    public function index(Request $request)
    {
        $query = Item::query();

        // Filter berdasarkan nama jika parameter 'name' ada
        if ($request->has('name')) {
            $query->where('item_name', 'like', '%' . $request->name . '%');
        }

        // Filter berdasarkan status jika parameter 'status' ada
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Data item berhasil diambil',
            'data' => $items
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Fitur: Tambah Item baru dengan validasi
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'item_name' => 'required|string|max:255',
            'item_info' => 'nullable|string',
            'status' => 'required|in:lost,found',
            'last_seen_location' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Handle upload gambar jika ada
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('items', $imageName, 'public');
                $validated['image'] = 'items/' . $imageName;
            }

            // Set last_seen_at ke waktu sekarang
            $validated['last_seen_at'] = now();

            // Buat item baru
            $item = Item::create($validated);

            // Generate QR Code dengan URL item
            $qrUrl = route('api.items.show', ['itemId' => $item->itemId]);
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($qrUrl)
                ->build();

            // Simpan QR Code
            $qrFileName = 'qrcodes/' . $item->itemId . '.png';
            Storage::disk('public')->put($qrFileName, $result->getString());
            $item->qr_url = 'storage/' . $qrFileName;
            $item->save();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil ditambahkan',
                'data' => $item
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * Fitur: Cari item berdasarkan Id
     */
    public function show(string $itemId)
    {
        $item = Item::find($itemId);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data item berhasil diambil',
            'data' => $item
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Fitur: Update data item
     */
    public function update(Request $request, string $itemId)
    {
        $item = Item::find($itemId);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak ditemukan'
            ], 404);
        }

        // Validasi input
        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'item_name' => 'sometimes|required|string|max:255',
            'item_info' => 'nullable|string',
            'status' => 'sometimes|required|in:lost,found',
            'last_seen_location' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Handle upload gambar baru jika ada
            if ($request->hasFile('image')) {
                // Hapus gambar lama jika ada
                if ($item->image) {
                    Storage::disk('public')->delete($item->image);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('items', $imageName, 'public');
                $validated['image'] = 'items/' . $imageName;
            }

            // Update last_seen_at jika ada perubahan status atau lokasi
            if ($request->has('status') || $request->has('last_seen_location')) {
                $validated['last_seen_at'] = now();
            }

            // Update item
            $item->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil diupdate',
                'data' => $item
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * Fitur: Hapus data item
     */
    public function destroy(string $itemId)
    {
        $item = Item::find($itemId);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak ditemukan'
            ], 404);
        }

        try {
            // Hapus gambar jika ada
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            // Hapus QR Code jika ada
            if ($item->qr_url) {
                // Extract path dari URL
                $qrPath = str_replace('storage/', '', $item->qr_url);
                Storage::disk('public')->delete($qrPath);
            }

            // Hapus item dari database
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus item',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
