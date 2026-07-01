<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PengaturanController extends Controller
{
    public function index()
    {
        $pengaturan = Pengaturan::pluck('value', 'key')->toArray();
        
        // Default values if not set
        $defaultSettings = [
            'nama_toko' => 'ALFA-POS MARKET',
            'alamat_toko' => 'Jl. Kebenaran No. 123, Jakarta',
            'telepon_toko' => '08123456789',
            'catatan_struk' => 'Barang yang sudah dibeli\ntidak dapat ditukar/dikembalikan',
            'pajak_persen' => '0',
        ];

        return Inertia::render('Pengaturan/Index', [
            'pengaturan' => array_merge($defaultSettings, $pengaturan)
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama_toko' => 'required|string|max:255',
            'alamat_toko' => 'required|string|max:500',
            'telepon_toko' => 'required|string|max:50',
            'catatan_struk' => 'nullable|string|max:500',
            'pajak_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        foreach ($validated as $key => $value) {
            Pengaturan::updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '']
            );
        }

        return redirect()->back()->with('success', 'Pengaturan toko berhasil diperbarui.');
    }
}
