<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SatuanController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->input('sort', 'id');
        $sortDir = $request->input('dir', 'desc');

        if (!in_array($sortField, ['id', 'nama', 'nama_pendek', 'created_at'])) {
            $sortField = 'id';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $satuan = Satuan::query()
            ->when($request->search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%")
                      ->orWhere('nama_pendek', 'like', "%{$search}%");
            })
            ->orderBy($sortField, $sortDir)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Master/Satuan/Index', [
            'satuan' => $satuan,
            'filters' => $request->only(['search', 'sort', 'dir'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:satuan,nama',
            'nama_pendek' => 'nullable|string|max:255'
        ]);

        Satuan::create($validated);

        return redirect()->back()->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function update(Request $request, Satuan $satuan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:satuan,nama,' . $satuan->id,
            'nama_pendek' => 'nullable|string|max:255'
        ]);

        $satuan->update($validated);

        return redirect()->back()->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Satuan $satuan)
    {
        try {
            $satuan->delete();
            return redirect()->back()
                ->with('flash.banner', 'Satuan berhasil dihapus.')
                ->with('flash.bannerStyle', 'success');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->back()
                    ->with('flash.banner', 'Gagal menghapus! Satuan ini sedang digunakan oleh Produk.')
                    ->with('flash.bannerStyle', 'danger');
            }
            throw $e;
        }
    }
}
