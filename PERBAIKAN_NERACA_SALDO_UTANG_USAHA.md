# Perbaikan Logika Neraca Saldo untuk Akun Utang Usaha dan Akun Kredit Normal

## Masalah yang Diperbaiki

Neraca Saldo perlu menampilkan saldo akhir dengan benar untuk akun-akun bersaldo normal kredit (Kewajiban, Modal, Pendapatan), terutama akun Utang Usaha.

### Contoh Kasus Utang Usaha:
**Akun 210 - Utang Usaha periode Mei 2026:**
- Total Debit: Rp 1.490.000 (pembayaran utang)
- Total Kredit: Rp 1.589.000 (pembelian kredit)
- Saldo Akhir = 0 + 1.490.000 - 1.589.000 = **-Rp 99.000**

**Yang Diharapkan di Neraca Saldo:**
- Debit: Rp 0
- Kredit: **Rp 99.000** (nilai absolut, tanpa tanda negatif)

## Logika yang Diterapkan

### Formula Universal untuk Semua Akun:
```
Saldo Akhir = Saldo Awal + Total Debit - Total Kredit
```

### Interpretasi Saldo Akhir untuk Penempatan di Neraca Saldo:

#### 1. AKUN NORMAL DEBIT (Aset, Beban):
- **Saldo Akhir > 0** → Tampil di kolom **DEBIT** (normal)
  - Contoh: Kas Rp 500.000 → Debit Rp 500.000
- **Saldo Akhir < 0** → Tampil di kolom **KREDIT** dengan nilai absolut (abnormal)
  - Contoh: Kas -Rp 500.000 (overdraft) → Kredit Rp 500.000

#### 2. AKUN NORMAL KREDIT (Kewajiban, Modal, Pendapatan):
- **Saldo Akhir > 0** → Tampil di kolom **KREDIT** (abnormal - lebih bayar)
  - Contoh: Utang Usaha Rp 99.000 (lebih bayar) → Kredit Rp 99.000
- **Saldo Akhir < 0** → Tampil di kolom **KREDIT** dengan nilai absolut (normal)
  - Contoh: Utang Usaha -Rp 99.000 (ada utang) → Kredit Rp 99.000 ✓

### Aturan Penting:
1. **Neraca Saldo TIDAK BOLEH menampilkan nilai negatif**
2. Semua nilai harus **positif** dan ditempatkan di kolom yang tepat
3. Untuk akun kredit normal, baik saldo positif maupun negatif **tetap tampil di kolom KREDIT**
4. Untuk akun debit normal, baik saldo positif maupun negatif **tampil sesuai tanda**

## Implementasi

### File: `app/Services/TrialBalanceService.php`

**Method: `mapToTrialBalanceColumns()`**

```php
private function mapToTrialBalanceColumns($saldoAkhir, $coa)
{
    $debit = 0;
    $kredit = 0;

    // Jika saldo = 0, tidak perlu ditampilkan
    if (abs($saldoAkhir) < 0.01) {
        return ['debit' => 0, 'kredit' => 0];
    }

    $isDebitNormal = $this->isDebitNormalAccount($coa);

    if ($saldoAkhir > 0) {
        // Saldo positif
        if ($isDebitNormal) {
            // Akun normal debit dengan saldo positif → tampil di DEBIT (normal)
            $debit = $saldoAkhir;
        } else {
            // Akun normal kredit dengan saldo positif → tampil di KREDIT
            $kredit = $saldoAkhir;
        }
    } else {
        // Saldo negatif - ambil nilai absolut
        $nilaiAbsolut = abs($saldoAkhir);
        
        if ($isDebitNormal) {
            // Akun normal debit dengan saldo negatif → tampil di KREDIT (abnormal)
            $kredit = $nilaiAbsolut;
        } else {
            // Akun normal kredit dengan saldo negatif → tampil di KREDIT (normal)
            $kredit = $nilaiAbsolut;
        }
    }

    return [
        'debit' => $debit,
        'kredit' => $kredit
    ];
}
```

## Test Cases dan Hasil

### Test #1: Kas (Aset) - Saldo Positif
- Saldo Akhir: Rp 500.000
- **Expected:** Debit Rp 500.000, Kredit Rp 0
- **Actual:** Debit Rp 500.000, Kredit Rp 0
- ✅ **PASS**

### Test #2: Kas (Aset) - Saldo Negatif (Overdraft)
- Saldo Akhir: -Rp 500.000
- **Expected:** Debit Rp 0, Kredit Rp 500.000
- **Actual:** Debit Rp 0, Kredit Rp 500.000
- ✅ **PASS**

### Test #3: Utang Usaha (Kewajiban) - Saldo Negatif (Ada Utang)
- Total Debit: Rp 1.490.000
- Total Kredit: Rp 1.589.000
- Saldo Akhir: -Rp 99.000
- **Expected:** Debit Rp 0, Kredit Rp 99.000
- **Actual:** Debit Rp 0, Kredit Rp 99.000
- ✅ **PASS** ← **Kasus utama yang diperbaiki**

### Test #4: Utang Usaha (Kewajiban) - Saldo Positif (Lebih Bayar)
- Total Debit: Rp 1.589.000
- Total Kredit: Rp 1.490.000
- Saldo Akhir: Rp 99.000
- **Expected:** Debit Rp 0, Kredit Rp 99.000
- **Actual:** Debit Rp 0, Kredit Rp 99.000
- ✅ **PASS**

### Test #5: Modal - Saldo Negatif (Normal)
- Total Kredit: Rp 5.000.000
- Saldo Akhir: -Rp 5.000.000
- **Expected:** Debit Rp 0, Kredit Rp 5.000.000
- **Actual:** Debit Rp 0, Kredit Rp 5.000.000
- ✅ **PASS**

### Test #6: Pendapatan Penjualan - Saldo Negatif (Normal)
- Total Kredit: Rp 10.000.000
- Saldo Akhir: -Rp 10.000.000
- **Expected:** Debit Rp 0, Kredit Rp 10.000.000
- **Actual:** Debit Rp 0, Kredit Rp 10.000.000
- ✅ **PASS**

### Test #7: Beban Gaji - Saldo Positif (Normal)
- Total Debit: Rp 3.000.000
- Saldo Akhir: Rp 3.000.000
- **Expected:** Debit Rp 3.000.000, Kredit Rp 0
- **Actual:** Debit Rp 3.000.000, Kredit Rp 0
- ✅ **PASS**

### Test #8: Persediaan Bahan Baku (Aset) - Saldo Positif
- Total Debit: Rp 1.250.000
- Saldo Akhir: Rp 1.250.000
- **Expected:** Debit Rp 1.250.000, Kredit Rp 0
- **Actual:** Debit Rp 1.250.000, Kredit Rp 0
- ✅ **PASS**

## Hasil Test:
```
Total Test: 8
Passed: 8
Failed: 0

✅ SEMUA TEST BERHASIL!
```

## Ringkasan Logika

### Tabel Keputusan:

| Tipe Akun | Saldo Normal | Saldo Akhir | Tampil di Kolom | Nilai |
|-----------|--------------|-------------|-----------------|-------|
| Aset | Debit | Positif | Debit | Saldo Akhir |
| Aset | Debit | Negatif | Kredit | abs(Saldo Akhir) |
| Kewajiban | Kredit | Positif | Kredit | Saldo Akhir |
| Kewajiban | Kredit | Negatif | Kredit | abs(Saldo Akhir) |
| Modal | Kredit | Positif | Kredit | Saldo Akhir |
| Modal | Kredit | Negatif | Kredit | abs(Saldo Akhir) |
| Pendapatan | Kredit | Positif | Kredit | Saldo Akhir |
| Pendapatan | Kredit | Negatif | Kredit | abs(Saldo Akhir) |
| Beban | Debit | Positif | Debit | Saldo Akhir |
| Beban | Debit | Negatif | Kredit | abs(Saldo Akhir) |

### Poin Penting:
1. **Akun Kredit Normal** (Kewajiban, Modal, Pendapatan):
   - Baik saldo positif maupun negatif → **SELALU tampil di kolom KREDIT**
   - Nilai yang ditampilkan adalah **nilai absolut** (tanpa tanda negatif)

2. **Akun Debit Normal** (Aset, Beban):
   - Saldo positif → tampil di kolom **DEBIT**
   - Saldo negatif → tampil di kolom **KREDIT** (abnormal)
   - Nilai yang ditampilkan adalah **nilai absolut**

3. **Tidak ada nilai negatif** yang ditampilkan di Neraca Saldo

## File yang Diubah

1. **app/Services/TrialBalanceService.php**
   - Method: `mapToTrialBalanceColumns()`
   - Perubahan: Perbaiki logika penempatan untuk akun kredit normal

## Verifikasi

### Cara Memverifikasi:
1. Buka **Buku Besar** untuk akun Utang Usaha (210)
2. Catat Total Debit dan Total Kredit
3. Hitung: Saldo Akhir = Saldo Awal + Total Debit - Total Kredit
4. Buka **Neraca Saldo** untuk periode yang sama
5. Verifikasi:
   - Jika Saldo Akhir negatif → harus tampil di kolom **Kredit** dengan nilai absolut
   - Jika Saldo Akhir positif → harus tampil di kolom **Kredit** dengan nilai positif
   - **TIDAK BOLEH** ada nilai negatif yang ditampilkan

### Script Test:
```bash
php test_neraca_saldo_logic.php
```

## Kesimpulan

Perbaikan ini memastikan bahwa:
1. ✅ Akun Utang Usaha dengan saldo negatif tampil di kolom **Kredit** (bukan Debit)
2. ✅ Semua akun kredit normal (Kewajiban, Modal, Pendapatan) tampil di kolom **Kredit**
3. ✅ Tidak ada nilai negatif yang ditampilkan di Neraca Saldo
4. ✅ Nilai yang ditampilkan adalah **nilai absolut** dari saldo akhir
5. ✅ Logika konsisten untuk semua jenis akun

**Formula tetap universal:**
```
Saldo Akhir = Saldo Awal + Total Debit - Total Kredit
```

**Penempatan di Neraca Saldo:**
- Akun Kredit Normal → **SELALU di kolom KREDIT** (nilai absolut)
- Akun Debit Normal → Di kolom **DEBIT** jika positif, **KREDIT** jika negatif (nilai absolut)
