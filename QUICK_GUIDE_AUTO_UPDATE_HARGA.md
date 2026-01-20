# ⚡ Quick Guide: Auto-Update Harga Biaya Bahan

## 🎯 Apa yang Berubah?

Sekarang sistem **otomatis update** harga biaya bahan dan BOM saat Anda melakukan pembelian!

## 🔄 Cara Kerja (Sederhana)

```
Beli Bahan → Harga Berubah → Biaya Bahan Update Otomatis → BOM Update → Harga Jual Aman
```

## 📝 Contoh Kasus

### Sebelum (Manual - Ribet!)

1. Beli tepung Rp 50.000/kg
2. Harga tepung di sistem masih Rp 45.000/kg
3. Biaya bahan produk masih pakai harga lama
4. **RUGI!** Jual produk dengan harga yang tidak sesuai biaya aktual
5. Harus manual update biaya bahan satu-satu
6. Harus klik "Recalculate" berkali-kali

### Sekarang (Otomatis - Mudah!)

1. Beli tepung Rp 50.000/kg
2. ✅ Harga tepung otomatis update ke Rp 50.000/kg
3. ✅ Biaya bahan produk otomatis update
4. ✅ BOM otomatis update
5. ✅ Harga jual bisa disesuaikan
6. **AMAN!** Tidak ada kerugian

## 🚀 Langkah Penggunaan

### 1. Lakukan Pembelian Seperti Biasa

```
Menu: Transaksi → Pembelian → Tambah Pembelian

Isi form:
- Vendor: PT. Supplier Tepung
- Bahan: Tepung Terigu
- Jumlah: 10 kg
- Harga: Rp 50.000/kg
- Total: Rp 500.000

Klik: Simpan
```

### 2. Sistem Otomatis Bekerja

```
⏳ Processing...

✅ Pembelian tersimpan
✅ Stok tepung bertambah 10 kg
✅ Harga tepung update ke Rp 50.000/kg
✅ BOM produk yang pakai tepung ter-update
✅ Biaya bahan produk ter-update

🎉 Selesai! (Semua otomatis)
```

### 3. Cek Hasil Update

```
Menu: Master Data → Biaya Bahan

Lihat produk yang pakai tepung:
- Roti Tawar: Biaya bahan Rp 150.000 → Rp 160.000 ✅
- Roti Manis: Biaya bahan Rp 120.000 → Rp 125.000 ✅
- Kue Kering: Biaya bahan Rp 80.000 → Rp 85.000 ✅

Semua ter-update otomatis!
```

## 📊 Monitoring

### Cek Log Update

```
Menu: Master Data → Biaya Bahan → Detail Produk

Akan muncul log:
"Biaya bahan ter-update otomatis karena harga Tepung Terigu berubah dari Rp 45.000 → Rp 50.000"
```

### Cek Produk Terpengaruh

```
Saat pembelian, sistem akan log:
- Bahan: Tepung Terigu
- Harga Lama: Rp 45.000
- Harga Baru: Rp 50.000
- Produk Terpengaruh: 3 produk
  1. Roti Tawar
  2. Roti Manis
  3. Kue Kering
```

## ⚠️ Penting!

### Harga Jual Tidak Auto-Update

Sistem **TIDAK** otomatis update harga jual, karena:
- Harga jual tergantung strategi bisnis
- Mungkin ada promo/diskon
- Perlu persetujuan manajemen

**Anda harus manual adjust harga jual** setelah biaya bahan berubah.

### Cara Adjust Harga Jual

```
Menu: Master Data → Produk → Edit Produk

Lihat:
- Biaya Bahan: Rp 160.000 (ter-update otomatis)
- Harga Jual: Rp 200.000 (masih harga lama)
- Margin: 25%

Hitung harga jual baru:
Rp 160.000 + 25% = Rp 200.000

Update:
- Harga Jual: Rp 200.000 → Rp 200.000 (atau sesuaikan)

Klik: Simpan
```

## 🎯 Tips

### 1. Cek Biaya Bahan Setelah Pembelian

Setelah pembelian, selalu cek:
```
Menu: Master Data → Biaya Bahan
```

Pastikan biaya bahan ter-update sesuai.

### 2. Review Harga Jual Berkala

Minimal 1x seminggu, review:
```
Menu: Master Data → Produk

Filter: Produk dengan margin < 20%
```

Adjust harga jual jika biaya bahan naik.

### 3. Monitor Harga Bahan

```
Menu: Master Data → Bahan Baku/Pendukung

Lihat kolom "Harga Satuan"
- Warna hijau: Harga stabil
- Warna kuning: Harga naik < 10%
- Warna merah: Harga naik > 10%
```

## 🔍 Troubleshooting

### Q: Biaya bahan tidak update setelah pembelian?

**A:** Coba:
1. Refresh halaman (F5)
2. Cek log error di sistem
3. Hubungi admin

### Q: Harga update tapi salah perhitungan?

**A:** Cek:
1. Satuan pembelian sudah benar?
2. Konversi satuan sudah benar?
3. Jumlah pembelian sudah benar?

### Q: Mau rollback harga lama?

**A:** Tidak bisa otomatis, harus manual:
1. Edit bahan baku/pendukung
2. Ubah harga satuan ke harga lama
3. Sistem akan auto-update lagi

## 📞 Bantuan

Jika ada masalah:
1. Cek dokumentasi lengkap: `SISTEM_AUTO_UPDATE_BIAYA_BAHAN.md`
2. Cek log sistem: `storage/logs/laravel.log`
3. Hubungi admin sistem

## ✅ Checklist Harian

- [ ] Cek pembelian hari ini
- [ ] Cek biaya bahan ter-update
- [ ] Review harga jual produk
- [ ] Adjust harga jual jika perlu
- [ ] Monitor margin keuntungan

## 🎉 Keuntungan

✅ Hemat waktu (tidak perlu manual update)
✅ Akurat (harga selalu terbaru)
✅ Aman (tidak ada kerugian)
✅ Transparan (log lengkap)
✅ Mudah (otomatis!)

---

**Sistem siap digunakan!** 🚀

Selamat menggunakan fitur auto-update harga biaya bahan!
