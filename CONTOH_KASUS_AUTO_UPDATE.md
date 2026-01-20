# 📝 Contoh Kasus: Auto-Update Biaya Bahan

## 🎯 Scenario: Harga Tepung Naik

### Data Awal

#### Bahan Baku: Tepung Terigu
```
- Kode: BB-001
- Nama: Tepung Terigu
- Satuan: KG
- Harga: Rp 45.000/kg
- Stok: 50 kg
```

#### Produk: Roti Tawar
```
- Kode: PRD-001
- Nama: Roti Tawar
- Satuan: PCS

BOM (Bill of Materials):
┌─────────────────┬─────────┬────────┬──────────────┬──────────────┐
│ Bahan           │ Jumlah  │ Satuan │ Harga/Satuan │ Total        │
├─────────────────┼─────────┼────────┼──────────────┼──────────────┤
│ Tepung Terigu   │ 2       │ kg     │ Rp 45.000    │ Rp 90.000    │
│ Gula Pasir      │ 0.5     │ kg     │ Rp 20.000    │ Rp 10.000    │
│ Telur           │ 3       │ butir  │ Rp 5.000     │ Rp 15.000    │
│ Ragi            │ 0.1     │ kg     │ Rp 50.000    │ Rp 5.000     │
│ Garam           │ 0.05    │ kg     │ Rp 10.000    │ Rp 500       │
├─────────────────┴─────────┴────────┴──────────────┼──────────────┤
│ TOTAL BIAYA BAHAN                                  │ Rp 120.500   │
└────────────────────────────────────────────────────┴──────────────┘

Harga Jual: Rp 150.000 (Margin: 24.5%)
```

---

## 🛒 Event: Pembelian Bahan Baku

### Transaksi Pembelian
```
Tanggal: 15 Januari 2026
Vendor: PT. Supplier Tepung
Payment: Cash

Detail Pembelian:
┌─────────────────┬─────────┬────────┬──────────────┬──────────────┐
│ Bahan           │ Jumlah  │ Satuan │ Harga/Satuan │ Total        │
├─────────────────┼─────────┼────────┼──────────────┼──────────────┤
│ Tepung Terigu   │ 20      │ kg     │ Rp 50.000    │ Rp 1.000.000 │
└─────────────────┴─────────┴────────┴──────────────┴──────────────┘

Total Pembelian: Rp 1.000.000
```

### Perubahan Harga
```
Tepung Terigu:
- Harga Lama: Rp 45.000/kg
- Harga Baru: Rp 50.000/kg
- Selisih: +Rp 5.000 (+11.1%)
```

---

## ⚙️ Proses Auto-Update

### Step 1: Sistem Update Harga Bahan
```
✅ Pembelian tersimpan
✅ Stok tepung: 50 kg → 70 kg
✅ Harga tepung: Rp 45.000 → Rp 50.000
```

### Step 2: Observer Triggered (Otomatis)
```
🔄 BahanBakuObserver::updated() triggered
   - Detect: harga_satuan berubah
   - Harga Lama: Rp 45.000
   - Harga Baru: Rp 50.000
```

### Step 3: Update BOM Detail
```
✅ BOM Detail Updated
   - Produk: Roti Tawar
   - Bahan: Tepung Terigu
   - Jumlah: 2 kg
   - Harga Lama: Rp 45.000/kg
   - Harga Baru: Rp 50.000/kg
   - Total Lama: Rp 90.000
   - Total Baru: Rp 100.000
```

### Step 4: Recalculate Biaya Bahan
```
💰 Recalculate Biaya Bahan Produk
   
   Roti Tawar - BOM Baru:
   ┌─────────────────┬─────────┬────────┬──────────────┬──────────────┐
   │ Bahan           │ Jumlah  │ Satuan │ Harga/Satuan │ Total        │
   ├─────────────────┼─────────┼────────┼──────────────┼──────────────┤
   │ Tepung Terigu   │ 2       │ kg     │ Rp 50.000 ✅ │ Rp 100.000 ✅│
   │ Gula Pasir      │ 0.5     │ kg     │ Rp 20.000    │ Rp 10.000    │
   │ Telur           │ 3       │ butir  │ Rp 5.000     │ Rp 15.000    │
   │ Ragi            │ 0.1     │ kg     │ Rp 50.000    │ Rp 5.000     │
   │ Garam           │ 0.05    │ kg     │ Rp 10.000    │ Rp 500       │
   ├─────────────────┴─────────┴────────┴──────────────┼──────────────┤
   │ TOTAL BIAYA BAHAN                                  │ Rp 130.500 ✅│
   └────────────────────────────────────────────────────┴──────────────┘
   
   Perubahan:
   - Biaya Bahan Lama: Rp 120.500
   - Biaya Bahan Baru: Rp 130.500
   - Selisih: +Rp 10.000 (+8.3%)
```

### Step 5: Update Produk
```
✅ Produk Updated
   - Produk: Roti Tawar
   - biaya_bahan: Rp 120.500 → Rp 130.500
   - harga_bom: Rp 120.500 → Rp 130.500
   - harga_jual: Rp 150.000 (tidak berubah)
```

### Step 6: Logging
```
📝 Log Tersimpan
   [2026-01-15 16:45:30] 🔄 Harga Bahan Baku Berubah
   [2026-01-15 16:45:30] ✅ BOM Detail Updated
   [2026-01-15 16:45:30] 💰 Biaya Bahan Updated
   [2026-01-15 16:45:30] 🎯 Auto Update Complete
```

---

## 📊 Hasil Akhir

### Data Setelah Update

#### Bahan Baku: Tepung Terigu
```
- Kode: BB-001
- Nama: Tepung Terigu
- Satuan: KG
- Harga: Rp 50.000/kg ✅ (naik dari Rp 45.000)
- Stok: 70 kg ✅ (naik dari 50 kg)
```

#### Produk: Roti Tawar
```
- Kode: PRD-001
- Nama: Roti Tawar
- Satuan: PCS

BOM (Bill of Materials):
┌─────────────────┬─────────┬────────┬──────────────┬──────────────┐
│ Bahan           │ Jumlah  │ Satuan │ Harga/Satuan │ Total        │
├─────────────────┼─────────┼────────┼──────────────┼──────────────┤
│ Tepung Terigu   │ 2       │ kg     │ Rp 50.000 ✅ │ Rp 100.000 ✅│
│ Gula Pasir      │ 0.5     │ kg     │ Rp 20.000    │ Rp 10.000    │
│ Telur           │ 3       │ butir  │ Rp 5.000     │ Rp 15.000    │
│ Ragi            │ 0.1     │ kg     │ Rp 50.000    │ Rp 5.000     │
│ Garam           │ 0.05    │ kg     │ Rp 10.000    │ Rp 500       │
├─────────────────┴─────────┴────────┴──────────────┼──────────────┤
│ TOTAL BIAYA BAHAN                                  │ Rp 130.500 ✅│
└────────────────────────────────────────────────────┴──────────────┘

Biaya Bahan: Rp 130.500 ✅ (naik dari Rp 120.500)
Harga Jual: Rp 150.000 (belum disesuaikan)
Margin: 14.9% ⚠️ (turun dari 24.5%)
```

---

## 💡 Rekomendasi

### Analisis Margin
```
Margin Lama: 24.5%
Margin Baru: 14.9%
Penurunan: -9.6%

⚠️ Margin terlalu rendah!
```

### Rekomendasi Harga Jual Baru
```
Opsi 1: Pertahankan Margin 24.5%
Harga Jual Baru = Rp 130.500 × 1.245 = Rp 162.472
Pembulatan: Rp 162.500

Opsi 2: Pertahankan Margin 25%
Harga Jual Baru = Rp 130.500 × 1.25 = Rp 163.125
Pembulatan: Rp 163.000

Opsi 3: Margin Konservatif 30%
Harga Jual Baru = Rp 130.500 × 1.30 = Rp 169.650
Pembulatan: Rp 170.000
```

### Action Required
```
📝 TODO:
1. Review harga jual produk
2. Pilih opsi harga jual baru
3. Update harga jual di sistem
4. Informasikan ke tim sales
5. Update price list
```

---

## 🎯 Kesimpulan

### Tanpa Auto-Update (Sebelum)
```
❌ Harga tepung naik tapi biaya bahan tidak update
❌ Jual Roti Tawar Rp 150.000 dengan biaya Rp 130.500
❌ Margin hanya 14.9% (harusnya 24.5%)
❌ RUGI Rp 10.000 per produk!
❌ Jika jual 100 pcs = RUGI Rp 1.000.000!
```

### Dengan Auto-Update (Sekarang)
```
✅ Harga tepung naik → Biaya bahan otomatis update
✅ Tahu biaya aktual: Rp 130.500
✅ Bisa adjust harga jual: Rp 162.500
✅ Margin tetap 24.5%
✅ AMAN dari kerugian!
```

---

## 📈 Impact Analysis

### Jika Tidak Update Harga Jual
```
Scenario: Jual 100 pcs Roti Tawar

Tanpa Auto-Update:
- Harga Jual: Rp 150.000
- Biaya Aktual: Rp 130.500 (tidak tahu)
- Margin: 14.9%
- Keuntungan: Rp 19.500 × 100 = Rp 1.950.000
- Kerugian Potensial: Rp 10.000 × 100 = Rp 1.000.000

Dengan Auto-Update:
- Harga Jual: Rp 162.500 (disesuaikan)
- Biaya Aktual: Rp 130.500 (tahu)
- Margin: 24.5%
- Keuntungan: Rp 32.000 × 100 = Rp 3.200.000
- Selisih: +Rp 1.250.000 ✅
```

### ROI (Return on Investment)
```
Investasi: 0 (sistem otomatis)
Keuntungan: +Rp 1.250.000 per 100 pcs
ROI: ∞ (infinite)

Waktu Hemat: 10-15 menit per update
Frekuensi: 5-10x per bulan
Total Waktu Hemat: 50-150 menit per bulan
```

---

## ✅ Checklist User

Setelah pembelian dengan harga baru:

- [ ] Cek biaya bahan ter-update otomatis
- [ ] Review margin keuntungan
- [ ] Hitung harga jual baru
- [ ] Update harga jual di sistem
- [ ] Informasikan ke tim sales
- [ ] Update price list
- [ ] Monitor penjualan

---

**Dengan sistem auto-update, Anda selalu tahu biaya aktual dan bisa mengambil keputusan yang tepat!** 🎯
