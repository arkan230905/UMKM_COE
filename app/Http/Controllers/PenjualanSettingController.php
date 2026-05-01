<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaketMenu;
use App\Models\PaketMenuDetail;
use App\Models\OngkirSetting;
use App\Models\Produk;

class PenjualanSettingController extends Controller
{
    // Load data for the settings modal
    public function index()
    {
        $paketMenus = PaketMenu::with('details.produk')->orderBy('created_at', 'desc')->get();
        $ongkirSettings = OngkirSetting::orderBy('jarak_min')->get();
        $produks = Produk::orderBy('nama_produk')->get(['id', 'nama_produk', 'harga_jual']);

        return response()->json([
            'paket_menus' => $paketMenus,
            'ongkir_settings' => $ongkirSettings,
            'produks' => $produks,
        ]);
    }

    // Load page for paket menu management
    public function paketMenuPage()
    {
        $paketMenus = PaketMenu::with('details.produk')->orderBy('created_at', 'desc')->get();
        $produks = Produk::orderBy('nama_produk')->get(['id', 'nama_produk', 'harga_jual']);

        return view('transaksi.penjualan.paket-menu', compact('paketMenus', 'produks'));
    }

    // ── PAKET MENU ──────────────────────────────────────────────────

    public function storePaket(Request $request)
    {
        $request->validate([
            'nama_paket'  => 'required|string|max:255',
            'harga_paket' => 'required|numeric|min:0',
            'status'      => 'required|in:aktif,nonaktif',
            'items'       => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.jumlah'    => 'required|numeric|min:0.01',
        ]);

        // Calculate harga_normal from sum of product prices
        $hargaNormal = 0;
        foreach ($request->items as $item) {
            $produk = Produk::find($item['produk_id']);
            $hargaNormal += ($produk->harga_jual ?? 0) * $item['jumlah'];
        }

        $paket = PaketMenu::create([
            'nama_paket'   => $request->nama_paket,
            'harga_normal' => $hargaNormal,
            'harga_paket'  => $request->harga_paket,
            'status'       => $request->status,
        ]);

        foreach ($request->items as $item) {
            PaketMenuDetail::create([
                'paket_menu_id' => $paket->id,
                'produk_id'     => $item['produk_id'],
                'jumlah'        => $item['jumlah'],
            ]);
        }

        // ── Otomatis buat Produk dengan kategori Paket ──────────────
        try {
            $kategoriPaket = \App\Models\KategoriProduk::where('kode_kategori', 'PKT')->first();
            $produkBaru = Produk::create([
                'nama_produk'       => $request->nama_paket,
                'kategori_id'       => $kategoriPaket?->id,
                'harga_jual'        => $request->harga_paket,
                'deskripsi'         => 'Paket menu: ' . $request->nama_paket,
                'stok'              => 0,
                'is_unlimited_stok' => true,
            ]);
            if (\Schema::hasColumn('paket_menus', 'produk_id')) {
                $paket->update(['produk_id' => $produkBaru->id]);
            }
        } catch (\Exception $e) {
            \Log::warning('Gagal membuat produk otomatis untuk paket: ' . $e->getMessage());
        }
        // ────────────────────────────────────────────────────────────

        return response()->json(['success' => true, 'message' => 'Paket menu berhasil ditambahkan', 'data' => $paket->load('details.produk')]);
    }

    public function updatePaket(Request $request, $id)
    {
        $paket = PaketMenu::findOrFail($id);

        $request->validate([
            'nama_paket'  => 'required|string|max:255',
            'harga_paket' => 'required|numeric|min:0',
            'status'      => 'required|in:aktif,nonaktif',
            'items'       => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.jumlah'    => 'required|numeric|min:0.01',
        ]);

        $hargaNormal = 0;
        foreach ($request->items as $item) {
            $produk = Produk::find($item['produk_id']);
            $hargaNormal += ($produk->harga_jual ?? 0) * $item['jumlah'];
        }

        $paket->update([
            'nama_paket'   => $request->nama_paket,
            'harga_normal' => $hargaNormal,
            'harga_paket'  => $request->harga_paket,
            'status'       => $request->status,
        ]);

        $paket->details()->delete();
        foreach ($request->items as $item) {
            PaketMenuDetail::create([
                'paket_menu_id' => $paket->id,
                'produk_id'     => $item['produk_id'],
                'jumlah'        => $item['jumlah'],
            ]);
        }

        // ── Otomatis update Produk yang terhubung ───────────────────
        try {
            $kategoriPaket = \App\Models\KategoriProduk::where('kode_kategori', 'PKT')->first();
            $produkIdField = \Schema::hasColumn('paket_menus', 'produk_id');
            if ($produkIdField && $paket->produk_id && $produkTerhubung = Produk::find($paket->produk_id)) {
                $produkTerhubung->update([
                    'nama_produk' => $request->nama_paket,
                    'kategori_id' => $kategoriPaket?->id,
                    'harga_jual'  => $request->harga_paket,
                    'deskripsi'   => 'Paket menu: ' . $request->nama_paket,
                ]);
            } else {
                $produkBaru = Produk::create([
                    'nama_produk'       => $request->nama_paket,
                    'kategori_id'       => $kategoriPaket?->id,
                    'harga_jual'        => $request->harga_paket,
                    'deskripsi'         => 'Paket menu: ' . $request->nama_paket,
                    'stok'              => 0,
                    'is_unlimited_stok' => true,
                ]);
                if ($produkIdField) {
                    $paket->update(['produk_id' => $produkBaru->id]);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Gagal update produk otomatis untuk paket: ' . $e->getMessage());
        }
        // ────────────────────────────────────────────────────────────

        return response()->json(['success' => true, 'message' => 'Paket menu berhasil diperbarui', 'data' => $paket->load('details.produk')]);
    }

    public function destroyPaket($id)
    {
        $paket = PaketMenu::findOrFail($id);

        // ── Hapus juga Produk yang terhubung ────────────────────────
        try {
            if (\Schema::hasColumn('paket_menus', 'produk_id') && $paket->produk_id) {
                Produk::where('id', $paket->produk_id)->delete();
            }
        } catch (\Exception $e) {
            \Log::warning('Gagal hapus produk terhubung paket: ' . $e->getMessage());
        }
        // ────────────────────────────────────────────────────────────

        $paket->delete();
        return response()->json(['success' => true, 'message' => 'Paket menu berhasil dihapus']);
    }

    // ── ONGKIR SETTING ──────────────────────────────────────────────

    public function storeOngkir(Request $request)
    {
        $request->validate([
            'jarak_min' => 'required|numeric|min:0',
            'jarak_max' => 'nullable|numeric|gt:jarak_min',
            'harga_ongkir' => 'required|numeric|min:0',
            'status' => 'required|boolean',
        ]);

        $ongkir = OngkirSetting::create($request->only([
            'jarak_min', 'jarak_max', 'harga_ongkir', 'status',
        ]));

        return response()->json(['success' => true, 'message' => 'Range ongkir berhasil ditambahkan', 'data' => $ongkir]);
    }

    public function updateOngkir(Request $request, $id)
    {
        $ongkir = OngkirSetting::findOrFail($id);

        $request->validate([
            'jarak_min' => 'required|numeric|min:0',
            'jarak_max' => 'nullable|numeric|gt:jarak_min',
            'harga_ongkir' => 'required|numeric|min:0',
            'status' => 'required|boolean',
        ]);

        $ongkir->update($request->only([
            'jarak_min', 'jarak_max', 'harga_ongkir', 'status',
        ]));

        return response()->json(['success' => true, 'message' => 'Range ongkir berhasil diperbarui', 'data' => $ongkir]);
    }

    public function destroyOngkir($id)
    {
        OngkirSetting::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Range ongkir berhasil dihapus']);
    }
}
