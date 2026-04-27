<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Http;

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
        $user = $request->user();
        // Validasi input
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_info' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $validated['user_id'] = $user->userId;

        try {
            // Handle upload gambar jika ada
            if ($request->hasFile('image')) {
                $image = $request->file('image');

                $cloudName = env('CLOUDINARY_CLOUD_NAME');
                $apiKey = env('CLOUDINARY_API_KEY');
                $apiSecret = env('CLOUDINARY_API_SECRET');

                $response = Http::asMultipart()
                    ->withBasicAuth($apiKey, $apiSecret)
                    ->post(
                        "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload",
                        [
                            "file" => fopen($image->getRealPath(), "r"),
                            "folder" => "items",
                        ],
                    );

                if ($response->successful()) {
                    $validated['image'] = $response->json('secure_url');
                } else {
                    throw new \Exception('Gagal upload gambar ke Cloudinary: ' . $response->body());
                }
            }

            // Buat item baru
            $item = Item::create($validated);

            // Generate QR Code dengan URL item
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($item->itemId)
                ->build();

            // Simpan QR Code ke Cloudinary
            $cloudName = env('CLOUDINARY_CLOUD_NAME');
            $apiKey = env('CLOUDINARY_API_KEY');
            $apiSecret = env('CLOUDINARY_API_SECRET');

            $tempQrPath = sys_get_temp_dir() . '/' . uniqid() . '_qr.png';
            file_put_contents($tempQrPath, $result->getString());

            $qrResponse = Http::asMultipart()
                ->withBasicAuth($apiKey, $apiSecret)
                ->post(
                    "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload",
                    [
                        "file" => fopen($tempQrPath, "r"),
                        "folder" => "qrcodes",
                    ],
                );

            if ($qrResponse->successful()) {
                $item->qr_url = $qrResponse->json('secure_url');
            } else {
                throw new \Exception('Gagal upload QR Code ke Cloudinary: ' . $qrResponse->body());
            }
            $item->save();

            // Hapus file temporary
            @unlink($tempQrPath);

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
                // Hapus gambar lama dari Cloudinary jika diperlukan
                if ($item->image) {
                    $cloudName = env('CLOUDINARY_CLOUD_NAME');
                    $apiKey = env('CLOUDINARY_API_KEY');
                    $apiSecret = env('CLOUDINARY_API_SECRET');

                    // Mendapatkan public_id dari url (misalnya: https://res.cloudinary.com/cloudname/image/upload/v1234567/public_id.jpg)
                    $urlParts = explode('/', $item->image);
                    $fileWithExt = end($urlParts);
                    $publicId = explode('.', $fileWithExt)[0];

                    $response = Http::withBasicAuth($apiKey, $apiSecret)
                        ->asForm()
                        ->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy", [
                            'public_id' => $publicId,
                        ]);
                }

                $image = $request->file('image');
                $cloudName = env('CLOUDINARY_CLOUD_NAME');
                $apiKey = env('CLOUDINARY_API_KEY');
                $apiSecret = env('CLOUDINARY_API_SECRET');

                $response = Http::asMultipart()
                    ->withBasicAuth($apiKey, $apiSecret)
                    ->post(
                        "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload",
                        [
                            "file" => fopen($image->getRealPath(), "r"),
                            "folder" => "items",
                        ],
                    );

                if ($response->successful()) {
                    $validated['image'] = $response->json('secure_url');
                } else {
                    throw new \Exception('Gagal upload gambar baru ke Cloudinary: ' . $response->body());
                }
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
