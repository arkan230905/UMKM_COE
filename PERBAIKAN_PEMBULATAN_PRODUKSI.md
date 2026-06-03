# Perbaikan Pembulatan Qty Produksi Harian

## Tanggal: 2 Juni 2026

### Masalah
Di halaman `/transaksi/produksi/create`, saat menghitung qty produksi harian dari:
- **Nominal produksi sebulan:** 4800
- **Jumlah hari kerja:** 26
- **Hasil yang salah:** 184
- **Hasil yang benar:** 185

**Perhitungan:**
```
4800 ÷ 26 = 184.615384...
```

Karena desimal adalah 0.615 (lebih dari 0.5), seharusnya dibulatkan ke **185**, bukan **184**.

### Penyebab
File `resources/views/transaksi/produksi/create.blade.php` menggunakan fungsi `Math.floor()` yang **selalu membulatkan ke bawah**, bukan pembulatan standar.

```javascript
// ❌ SALAH - Selalu bulatkan ke bawah
const qtyPerHari = Math.floor(jumlahBulanan / hariBulanan);
```

### Solusi
Mengubah `Math.floor()` menjadi `Math.round()` untuk pembulatan standar (matematika):
- Jika desimal < 0.5 → bulatkan ke bawah
- Jika desimal ≥ 0.5 → bulatkan ke atas

```javascript
// ✅ BENAR - Pembulatan standar
const qtyPerHari = Math.round(jumlahBulanan / hariBulanan);
```

### File yang Diubah
**File:** `resources/views/transaksi/produksi/create.blade.php`
- **Line 252:** `Math.floor()` → `Math.round()`

### Contoh Hasil Setelah Perbaikan

| Produksi Bulanan | Hari Kerja | Hasil Lama (floor) | Hasil Baru (round) | Desimal |
|------------------|------------|--------------------|--------------------|---------|
| 4800             | 26         | 184 ❌             | 185 ✅             | 0.615   |
| 5000             | 26         | 192 ❌             | 192 ✅             | 0.308   |
| 3000             | 22         | 136 ❌             | 136 ✅             | 0.364   |
| 2500             | 20         | 125 ✅             | 125 ✅             | 0.000   |

### Konsistensi
File `edit.blade.php` sudah menggunakan `Math.round()` sejak awal, jadi sekarang kedua file (create dan edit) sudah konsisten.

### Testing
Setelah perbaikan:
- ✓ 4800 ÷ 26 = 185 (bukan 184)
- ✓ Pembulatan mengikuti aturan matematika standar
- ✓ Konsisten dengan halaman edit produksi

### Catatan Teknis

**Perbedaan Fungsi Pembulatan JavaScript:**

1. **Math.floor()** - Selalu ke bawah
   ```javascript
   Math.floor(184.1) // 184
   Math.floor(184.9) // 184
   ```

2. **Math.ceil()** - Selalu ke atas
   ```javascript
   Math.ceil(184.1) // 185
   Math.ceil(184.9) // 185
   ```

3. **Math.round()** - Pembulatan standar ✅
   ```javascript
   Math.round(184.4) // 184
   Math.round(184.5) // 185
   Math.round(184.6) // 185
   ```

Untuk perhitungan qty produksi, `Math.round()` adalah pilihan yang paling tepat karena mengikuti aturan pembulatan matematika standar.
