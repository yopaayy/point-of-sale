<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Merk;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MerkController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->input('sort', 'id');
        $sortDir = $request->input('dir', 'desc');

        if (!in_array($sortField, ['id', 'nama', 'created_at'])) {
            $sortField = 'id';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $merk = Merk::query()
            ->when($request->search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%");
            })
            ->orderBy($sortField, $sortDir)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Master/Merk/Index', [
            'merk' => $merk,
            'filters' => $request->only(['search', 'sort', 'dir'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:merk,nama'
        ]);

        Merk::create($validated);

        return redirect()->back()->with('success', 'Merk berhasil ditambahkan.');
    }

    public function update(Request $request, Merk $merk)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:merk,nama,' . $merk->id
        ]);

        $merk->update($validated);

        return redirect()->back()->with('success', 'Merk berhasil diperbarui.');
    }

    public function destroy(Merk $merk)
    {
        try {
            $merk->delete();
            return redirect()->back()
                ->with('flash.banner', 'Merk berhasil dihapus.')
                ->with('flash.bannerStyle', 'success');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->back()
                    ->with('flash.banner', 'Gagal menghapus! Merk ini sedang digunakan oleh Produk.')
                    ->with('flash.bannerStyle', 'danger');
            }
            throw $e;
        }
    }
}
