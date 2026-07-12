# PERBAIKAN VALIDASI TANGGAL PELUNASAN - TIMEZONE ISSUE

## MASALAH

### ❌ Tanggal Hari Ini Dianggap Masa Depan

**Penyebab:**
Parsing tanggal menggunakan `new Date(tanggalInput.value)` membaca string tanggal dengan interpretasi zona waktu yang berbeda, menyebabkan:
- Input: `"2026-07-10"` (10 Juli 2026)
- Browser membaca: `07/10/2026` sebagai **7 Oktober 2026** (format MM/DD/YYYY)
- Atau: Pergeseran tanggal karena konversi timezone UTC

**Contoh Masalah:**
```javascript
// SALAH - Bisa salah interpretasi
const input = "2026-07-10";
const date = new Date(input);  // Bisa jadi 7 Oktober atau 10 Juli tergantung locale/ti