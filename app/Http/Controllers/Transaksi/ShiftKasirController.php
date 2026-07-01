<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ShiftKasir;
use App\Models\Cabang;
use Carbon\Carbon;

class ShiftKasirController extends Controller
{
    public function index()
    {
        // Cari shift kasir yang masih buka untuk user login saat ini
        $activeShift = ShiftKasir::where('user_id', auth()->id())
            ->where('status', 'buka')
            ->first();

        // Ambil riwayat shift
        $riwayatShift = ShiftKasir::where('user_id', auth()->id())
            ->orderBy('id', 'desc')
            ->paginate(5);
            
        $cabangs = Cabang::all();

        return Inertia::render('Transaksi/ShiftKasir/Index', [
            'activeShift' => $activeShift,
            'riwayatShift' => $riwayatShift,
            'cabangs' => $cabangs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'modal_awal' => 'required|numeric|min:0',
            'cabang_id' => 'required|exists:cabang,id',
        ]);

        // Pastikan tidak ada shift aktif
        $activeShift = ShiftKasir::where('user_id', auth()->id())
            ->where('status', 'buka')
            ->first();

        if ($activeShift) {
            return redirect()->back()->withErrors(['error' => 'Anda masih memiliki shift yang belum ditutup.']);
        }

        ShiftKasir::create([
            'user_id' => auth()->id(),
            'cabang_id' => $request->cabang_id,
            'waktu_buka' => Carbon::now(),
            'modal_awal' => $request->modal_awal,
            'total_pemasukan' => 0,
            'total_pengeluaran' => 0,
            'selisih' => 0,
            'status' => 'buka'
        ]);

        return redirect()->route('transaksi.kasir.index')->with('success', 'Shift kasir berhasil dibuka, selamat bekerja!');
    }

    public function close(Request $request, $id)
    {
        $shift = ShiftKasir::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        if ($shift->status == 'tutup') {
            return redirect()->back()->withErrors(['error' => 'Shift ini sudah ditutup.']);
        }

        $request->validate([
            'uang_fisik' => 'required|numeric|min:0'
        ]);

        // Hitung seharusnya uang di laci
        $uangSeharusnya = $shift->modal_awal + $shift->total_pemasukan - $shift->total_pengeluaran;
        $selisih = $request->uang_fisik - $uangSeharusnya;

        $shift->update([
            'waktu_tutup' => Carbon::now(),
            'status' => 'tutup',
            'selisih' => $selisih
        ]);

        return redirect()->route('transaksi.shift-kasir.index')->with('success', 'Shift kasir berhasil ditutup.');
    }
}
