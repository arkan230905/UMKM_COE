<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\Penjualan;
use App\Models\Produk;

/**
 * Service untuk validasi ketersediaan akun COA sebelum jurnal penjualan dibuat.
 *
 * Akun yang dibutuhkan:
 *  1. Kas / Piutang / Bank  (debit – ditentukan dari coa_id atau payment_method)
 *  2. Penjualan             (kredit – Revenue, nama 'Penjualan')
 *  3. PPN Keluaran          (kredit – Liability, nama 'PPN Keluaran')  [jika ada PPN]
 *  4. Pendapatan Lain-lain  (kredit – Revenue, nama LIKE 'Pendapatan Lain%') [jika ada ongkir]
 *  5. Diskon Penjualan      (debit  – Expense/Revenue, nama LIKE '%Diskon%Penjualan%') [jika ada diskon]
 *  6. HPP                   (debit  – Expense, per produk)
 *  7. Persediaan Barang Jadi(kredit – Asset, per produk)
 */
class JournalValidationService
{
    /**
     * Validasi semua akun yang dibutuhkan untuk jurnal penjualan.
     *
     * @param  Penjualan  $penjualan  (harus sudah load relasi details.produk)
     * @return array  [
     *   'valid'    => bool,
     *   'missing'  => array of string (deskripsi akun yang belum ada),
     *   'warnings' => array of string (peringatan non-blocking),
     *   'accounts' => array (akun yang ditemukan, untuk preview jurnal),
     * ]
     */
    public function validate(Penjualan $penjualan): array
    {
        $userId  = $penjualan->user_id ?? auth()->id() ?? null;
        $missing = [];
        $found   = [];

        // ── 1. Akun Debit: Kas / Bank / Piutang ─────────────────────────────
        $debitCoa = $this->findDebitCoa($penjualan, $userId);
        if ($debitCoa) {
            $found['debit'] = $debitCoa;
        } else {
            $label = $this->debitLabel($penjualan->payment_method ?? 'cash');
            $missing[] = [
                'key'   => 'debit',
                'nama'  => $label,
                'tipe'  => 'Asset',
                'pesan' => "Akun \"{$label}\" belum dibuat. Silakan tambahkan akun kas/bank/piutang terlebih dahulu.",
            ];
        }

        // ── 2. Akun Penjualan (Revenue) ──────────────────────────────────────
        $penjualanCoa = $this->findCoa('Penjualan', 'Revenue', $userId);
        if ($penjualanCoa) {
            $found['penjualan'] = $penjualanCoa;
        } else {
            $missing[] = [
                'key'   => 'penjualan',
                'nama'  => 'Penjualan',
                'tipe'  => 'Revenue',
                'pesan' => 'Akun "Penjualan" (Revenue) belum dibuat. Silakan tambahkan terlebih dahulu agar jurnal dapat dibuat dengan benar.',
            ];
        }

        // ── 3. PPN Keluaran (hanya jika ada PPN) ────────────────────────────
        $biayaPPN = (float)($penjualan->biaya_ppn ?? 0);
        if ($biayaPPN > 0) {
            $ppnCoa = $this->findCoa('PPN Keluaran', 'Liability', $userId);
            if ($ppnCoa) {
                $found['ppn_keluaran'] = $ppnCoa;
            } else {
                $missing[] = [
                    'key'   => 'ppn_keluaran',
                    'nama'  => 'PPN Keluaran',
                    'tipe'  => 'Liability',
                    'pesan' => 'Akun "PPN Keluaran" (Liability) belum dibuat. Silakan tambahkan terlebih dahulu.',
                ];
            }
        }

        // ── 4. Pendapatan Lain-lain / Ongkir (hanya jika ada ongkir) ────────
        $biayaOngkir = (float)($penjualan->biaya_ongkir ?? 0);
        if ($biayaOngkir > 0) {
            $ongkirCoa = $this->findOngkirCoa($userId);
            if ($ongkirCoa) {
                $found['pendapatan_lain'] = $ongkirCoa;
            } else {
                $missing[] = [
                    'key'   => 'pendapatan_lain',
                    'nama'  => 'Pendapatan Lain-lain',
                    'tipe'  => 'Revenue',
                    'pesan' => 'Akun "Pendapatan Lain-lain" (Revenue) belum dibuat. Dibutuhkan untuk mencatat pendapatan ongkir.',
                ];
            }
        }

        // ── 5. Diskon Penjualan (hanya jika ada diskon) ──────────────────────
        $totalDiskon = $this->getTotalDiskon($penjualan);
        if ($totalDiskon > 0) {
            $diskonCoa = $this->findDiskonCoa($userId);
            if (!$diskonCoa) {
                // Buat akun Diskon Penjualan otomatis
                $diskonCoa = $this->createDiskonPenjualanCoa($userId);
            }
            if ($diskonCoa) {
                $found['diskon_penjualan'] = $diskonCoa;
            } else {
                $missing[] = [
                    'key'   => 'diskon_penjualan',
                    'nama'  => 'Diskon Penjualan',
                    'tipe'  => 'Expense',
                    'pesan' => 'Akun "Diskon Penjualan" belum dibuat. Dibutuhkan untuk mencatat diskon yang diberikan.',
                ];
            }
        }

        // ── 6 & 7. HPP & Persediaan Barang Jadi (per produk) ────────────────
        $items = $this->getItems($penjualan);
        foreach ($items as $item) {
            $produk     = $item['produk'];
            $namaProduk = $produk->nama_produk;

            // HPP
            $hppCoa = $this->findHppCoa($produk, $userId);
            if ($hppCoa) {
                $found['hpp_' . $produk->id] = $hppCoa;
            } else {
                $missing[] = [
                    'key'   => 'hpp_' . $produk->id,
                    'nama'  => "HPP {$namaProduk}",
                    'tipe'  => 'Expense',
                    'pesan' => "Akun HPP untuk produk \"{$namaProduk}\" belum dibuat atau belum di-mapping. Silakan tambahkan akun \"HPP {$namaProduk}\" atau \"Harga Pokok Penjualan\".",
                ];
            }

            // Persediaan Barang Jadi
            $persediaanCoa = $this->findPersediaanCoa($produk, $userId);
            if ($persediaanCoa) {
                $found['persediaan_' . $produk->id] = $persediaanCoa;
            } else {
                $missing[] = [
                    'key'   => 'persediaan_' . $produk->id,
                    'nama'  => "Persediaan Barang Jadi {$namaProduk}",
                    'tipe'  => 'Asset',
                    'pesan' => "Akun Persediaan Barang Jadi untuk produk \"{$namaProduk}\" belum dibuat atau belum di-mapping. Silakan tambahkan akun \"Persediaan Barang Jadi {$namaProduk}\" atau mapping di master produk.",
                ];
            }
        }

        return [
            'valid'    => empty($missing),
            'missing'  => $missing,
            'warnings' => [],
            'accounts' => $found,
        ];
    }

    /**
     * Validasi cepat (hanya cek akun wajib tanpa detail per produk).
     * Digunakan saat real-time check di form penjualan.
     */
    public function validateQuick(int $userId, bool $hasPPN = false, bool $hasOngkir = false, bool $hasDiskon = false, array $produkIds = []): array
    {
        $missing = [];

        // Kas/Bank/Piutang – cek minimal satu ada
        $kasAda = Coa::withoutGlobalScopes()
            ->whereIn('tipe_akun', ['Asset', 'Aset'])
            ->where(function ($q) {
                $q->where('nama_akun', 'like', '%Kas%')
                  ->orWhere('nama_akun', 'like', '%Bank%')
                  ->orWhere('nama_akun', 'like', '%Piutang%');
            })
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->exists();

        if (!$kasAda) {
            $missing[] = ['key' => 'debit', 'nama' => 'Kas / Bank / Piutang', 'tipe' => 'Asset'];
        }

        // Penjualan
        if (!$this->findCoa('Penjualan', 'Revenue', $userId)) {
            $missing[] = ['key' => 'penjualan', 'nama' => 'Penjualan', 'tipe' => 'Revenue'];
        }

        if ($hasPPN && !$this->findCoa('PPN Keluaran', 'Liability', $userId)) {
            $missing[] = ['key' => 'ppn_keluaran', 'nama' => 'PPN Keluaran', 'tipe' => 'Liability'];
        }

        if ($hasOngkir && !$this->findOngkirCoa($userId)) {
            $missing[] = ['key' => 'pendapatan_lain', 'nama' => 'Pendapatan Lain-lain', 'tipe' => 'Revenue'];
        }

        if ($hasDiskon && !$this->findDiskonCoa($userId)) {
            $missing[] = ['key' => 'diskon_penjualan', 'nama' => 'Diskon Penjualan', 'tipe' => 'Expense'];
        }

        foreach ($produkIds as $produkId) {
            $produk = Produk::find($produkId);
            if (!$produk) continue;

            if (!$this->findHppCoa($produk, $userId)) {
                $missing[] = ['key' => 'hpp_' . $produkId, 'nama' => "HPP {$produk->nama_produk}", 'tipe' => 'Expense'];
            }
            if (!$this->findPersediaanCoa($produk, $userId)) {
                $missing[] = ['key' => 'persediaan_' . $produkId, 'nama' => "Persediaan Barang Jadi {$produk->nama_produk}", 'tipe' => 'Asset'];
            }
        }

        return [
            'valid'   => empty($missing),
            'missing' => $missing,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function findDebitCoa(Penjualan $penjualan, ?int $userId): ?Coa
    {
        // Prioritas: coa_id yang dipilih user
        if ($penjualan->coa_id) {
            $coa = Coa::withoutGlobalScopes()->find($penjualan->coa_id);
            if ($coa) return $coa;
        }

        // Fallback berdasarkan payment_method
        $q = Coa::withoutGlobalScopes()->whereIn('tipe_akun', ['Asset', 'Aset']);

        switch ($penjualan->payment_method ?? 'cash') {
            case 'transfer':
                $q->where('nama_akun', 'like', '%Bank%');
                break;
            case 'credit':
                $q->where('nama_akun', 'like', '%Piutang%');
                break;
            default: // cash
                $q->where(function ($q2) {
                    $q2->where('nama_akun', 'Kas')
                       ->orWhere('nama_akun', 'like', '%Kas%');
                });
        }

        if ($userId) {
            $found = (clone $q)->where('user_id', $userId)->first();
            if ($found) return $found;
        }

        return $q->orderBy('id', 'desc')->first();
    }

    private function findCoa(string $namaAkun, string $tipeAkun, ?int $userId): ?Coa
    {
        $q = Coa::withoutGlobalScopes()
            ->where('nama_akun', $namaAkun)
            ->where('tipe_akun', $tipeAkun);

        if ($userId) {
            $found = (clone $q)->where('user_id', $userId)->first();
            if ($found) return $found;
        }

        // Fallback: akun global (user_id null) atau akun manapun
        return $q->orderBy('id', 'desc')->first();
    }

    private function findOngkirCoa(?int $userId): ?Coa
    {
        $q = Coa::withoutGlobalScopes()
            ->where('nama_akun', 'like', 'Pendapatan Lain%')
            ->whereIn('tipe_akun', ['Revenue', 'Pendapatan']);

        if ($userId) {
            $found = (clone $q)->where('user_id', $userId)->first();
            if ($found) return $found;
        }

        return $q->orderBy('id', 'desc')->first();
    }

    private function findDiskonCoa(?int $userId): ?Coa
    {
        $q = Coa::withoutGlobalScopes()
            ->where(function ($q2) {
                $q2->where('nama_akun', 'like', '%Diskon%Penjualan%')
                   ->orWhere('nama_akun', 'like', '%Potongan%Penjualan%')
                   ->orWhere('nama_akun', 'Diskon Penjualan');
            })
            ->whereIn('tipe_akun', ['Expense', 'Beban', 'Revenue', 'Pendapatan']);

        if ($userId) {
            $found = (clone $q)->where('user_id', $userId)->first();
            if ($found) return $found;
        }

        return $q->orderBy('id', 'desc')->first();
    }

    /**
     * Buat akun Diskon Penjualan otomatis jika belum ada.
     */
    private function createDiskonPenjualanCoa(?int $userId): ?Coa
    {
        try {
            // Cari kode yang belum dipakai oleh user ini
            $kode = '431';
            $counter = 1;
            while (Coa::withoutGlobalScopes()
                ->where('kode_akun', $kode)
                ->where(function($q) use ($userId) {
                    if ($userId) $q->where('user_id', $userId);
                    else $q->whereNull('user_id');
                })
                ->exists()) {
                $counter++;
                $kode = '43' . $counter;
            }

            return Coa::create([
                'kode_akun'    => $kode,
                'nama_akun'    => 'Diskon Penjualan',
                'tipe_akun'    => 'Expense',
                'kategori_akun' => '',
                'saldo_normal' => 'debit',
                'keterangan'   => 'Potongan/diskon yang diberikan kepada pelanggan',
                'user_id'      => $userId,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Gagal membuat akun Diskon Penjualan otomatis: ' . $e->getMessage());
            return null;
        }
    }

    private function findHppCoa(Produk $produk, ?int $userId): ?Coa
    {
        $namaProduk = $produk->nama_produk;

        // 1. Spesifik per produk
        $q = Coa::withoutGlobalScopes()
            ->where(function ($q2) use ($namaProduk) {
                $q2->where('nama_akun', 'HPP ' . $namaProduk)
                   ->orWhere('nama_akun', 'Harga Pokok Penjualan ' . $namaProduk)
                   ->orWhere('nama_akun', 'like', '%HPP%' . $namaProduk . '%');
            })
            ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost']);

        if ($userId) {
            $found = (clone $q)->where('user_id', $userId)->first();
            if ($found) return $found;
        }
        $found = $q->first();
        if ($found) return $found;

        // 2. HPP umum
        $qUmum = Coa::withoutGlobalScopes()
            ->where(function ($q2) {
                $q2->where('kode_akun', '56')
                   ->orWhere('nama_akun', 'Harga Pokok Penjualan')
                   ->orWhere('nama_akun', 'HPP');
            })
            ->whereIn('tipe_akun', ['Beban', 'HPP', 'Expense', 'Cost']);

        if ($userId) {
            $found = (clone $qUmum)->where('user_id', $userId)->first();
            if ($found) return $found;
        }

        return $qUmum->first();
    }

    private function findPersediaanCoa(Produk $produk, ?int $userId): ?Coa
    {
        // 1. Dari coa_persediaan_id produk
        if (!empty($produk->coa_persediaan_id)) {
            $coa = Coa::withoutGlobalScopes()->find($produk->coa_persediaan_id);
            if ($coa) return $coa;
        }

        $namaProduk = $produk->nama_produk;

        // 2. Spesifik per produk
        $q = Coa::withoutGlobalScopes()
            ->where(function ($q2) use ($namaProduk) {
                $q2->where('nama_akun', 'Pers. Barang Jadi ' . $namaProduk)
                   ->orWhere('nama_akun', 'Persediaan Barang Jadi ' . $namaProduk)
                   ->orWhere('nama_akun', 'like', '%Barang Jadi%' . $namaProduk . '%');
            })
            ->whereIn('tipe_akun', ['Asset', 'Aset']);

        if ($userId) {
            $found = (clone $q)->where('user_id', $userId)->first();
            if ($found) return $found;
        }
        $found = $q->first();
        if ($found) return $found;

        // 3. Persediaan umum
        $qUmum = Coa::withoutGlobalScopes()
            ->where(function ($q2) {
                $q2->where('kode_akun', '116')
                   ->orWhere('nama_akun', 'Pers. Barang Jadi')
                   ->orWhere('nama_akun', 'Persediaan Barang Jadi');
            })
            ->whereIn('tipe_akun', ['Asset', 'Aset']);

        if ($userId) {
            $found = (clone $qUmum)->where('user_id', $userId)->first();
            if ($found) return $found;
        }

        return $qUmum->first();
    }

    private function getItems(Penjualan $penjualan): array
    {
        $items = [];

        if ($penjualan->details && $penjualan->details->count() > 0) {
            foreach ($penjualan->details as $detail) {
                if ($detail->produk) {
                    $items[] = ['produk' => $detail->produk, 'qty' => (float)($detail->jumlah ?? 0)];
                }
            }
        } elseif ($penjualan->produk) {
            $items[] = ['produk' => $penjualan->produk, 'qty' => (float)($penjualan->jumlah ?? 1)];
        }

        return $items;
    }

    private function getTotalDiskon(Penjualan $penjualan): float
    {
        $total = (float)($penjualan->diskon_nominal ?? 0);

        if ($penjualan->details && $penjualan->details->count() > 0) {
            foreach ($penjualan->details as $d) {
                $dn = (float)($d->diskon_nominal ?? 0);
                // Fallback: hitung dari diskon_persen jika nominal belum tersimpan
                if ($dn == 0 && ($d->diskon_persen ?? 0) > 0) {
                    $dn = round((float)$d->harga_satuan * (float)$d->jumlah * (float)$d->diskon_persen / 100);
                }
                $total += $dn;
            }
        }

        return $total;
    }

    private function debitLabel(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'transfer' => 'Bank',
            'credit'   => 'Piutang Usaha',
            default    => 'Kas',
        };
    }
}
