<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // ✅ Ambil Semua Kategori
    public function index()
    {
        $category = Category::all();

        return response()->json([
            'message' => 'Berhasil mengambil semua kategori.',
            'data' => $category
        ]);
    }

    // ✅ Tambah Kategori
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category = Category::create(['name' => $validated['name']]);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => $category
        ], 201);
    }

    // ✅ Ambil Satu Kategori
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil kategori.',
            'data' => $category
        ]);
    }

    // ✅ Update Kategori
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category->update(['name' => $validated['name']]);

        return response()->json([
            'message' => 'Kategori berhasil diupdate.',
            'data' => $category
        ]);
    }

    // ✅ Hapus Kategori
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
