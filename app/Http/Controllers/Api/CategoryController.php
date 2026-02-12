<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
        // GET: Ambil Semua Kategori
    public function index()
    {
        $categories = Category::all();
        return response()->json([
            'status' => true,
            'message' => 'List Kategori',
            'data' => $categories
        ], 200);
    }

    // POST: Tambah Kategori
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:categories,name'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = Category::create([
            'name' => $request->name
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Kategori dibuat',
            'data' => $category
        ], 201);
    }

    // GET: Detail 1 Kategori
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['message' => 'Tidak ditemukan'], 404);

        return response()->json(['data' => $category], 200);
    }

    // PUT: Update Kategori
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['message' => 'Tidak ditemukan'], 404);

        $category->update(['name' => $request->name]);

        return response()->json(['message' => 'Berhasil update', 'data' => $category], 200);
    }

    // DELETE: Hapus Kategori
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['message' => 'Tidak ditemukan'], 404);

        $category->delete();
        return response()->json(['message' => 'Berhasil dihapus'], 200);
    }

}
