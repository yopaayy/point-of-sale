<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->input('sort', 'id');
        $sortDir = $request->input('dir', 'desc');

        // Validasi kolom yang bisa di-sort
        if (!in_array($sortField, ['id', 'nama', 'created_at'])) {
            $sortField = 'id';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $kategori = Kategori::query()
            ->when($request->search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%");
            })
            ->orderBy($sortField, $sortDir)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Master/Kategori/Index', [
            'kategori' => $kategori,
            'filters' => $request->only(['search', 'sort', 'dir'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:kategori,nama'
        ]);

        Kategori::create($validated);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Kategori $kategori)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:kategori,nama,' . $kategori->id
        ]);

        $kategori->update($validated);

        return redirect()->back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori)
    {
        try {
            $kategori->delete();
            return redirect()->back()
                ->with('flash.banner', 'Kategori berhasil dihapus.')
                ->with('flash.bannerStyle', 'success');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->back()
                    ->with('flash.banner', 'Gagal menghapus! Kategori ini sedang digunakan oleh Produk.')
                    ->with('flash.bannerStyle', 'danger');
            }
            throw $e;
        }
    }
}
