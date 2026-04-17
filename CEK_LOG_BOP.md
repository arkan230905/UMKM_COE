# Cara Cek Log BOP Update

## Langkah 1: Edit dan Simpan BOP
1. Buka halaman Edit BOP Proses
2. Isi beberapa komponen dengan nilai > 0
3. Klik "Simpan Perubahan"
4. Jika masih error, lanjut ke langkah 2

## Langkah 2: Cek Log Laravel

### Cara 1: Via File Explorer
1. Buka folder: `storage/logs/`
2. Buka file: `laravel.log` (file terbaru)
3. Scroll ke bawah (log terbaru ada di bawah)
4. Cari baris yang mengandung: `BOP Update - Raw Request Data`

### Cara 2: Via Command Line
```bash
# Lihat 100 baris terakhir
tail -n 100 storage/logs/laravel.log

# Atau filter hanya BOP Update
grep "BOP Update" storage/logs/laravel.log | tail -n 50
```

### Cara 3: Via PowerShell (Windows)
```powershell
# Lihat 100 baris terakhir
Get-Content storage/logs/laravel.log -Tail 100

# Atau filter hanya BOP Update
Select-String -Path storage/logs/laravel.log -Pattern "BOP Update" | Select-Object -Last 20
```

## Langkah 3: Kirim Log ke Saya

Copy bagian log yang mengandung:
- `BOP Update - Raw Request Data`
- `BOP Update - Validated Data`
- `BOP Update - Checking component`
- `BOP Update - Valid Components`

Contoh log yang dicari:
```
[2026-04-17 12:34:56] local.INFO: BOP Update - Raw Request Data: {"all_data":{"komponen_bop":[...]}}
[2026-04-17 12:34:56] local.INFO: BOP Update - Validated Data: {"komponen_bop":[...]}
[2026-04-17 12:34:56] local.INFO: BOP Update - Checking component: {...}
[2026-04-17 12:34:56] local.INFO: BOP Update - Valid Components: {"count":0,"components":[]}
```

## Langkah 4: Alternatif - Screenshot Form

Jika sulit cek log, kirim screenshot:
1. Form BOP yang sudah diisi (sebelum klik Simpan)
2. Error message yang muncul
3. Browser Console (F12 → Console tab)

## Yang Perlu Dicek dari Log:

### 1. Raw Request Data
Apakah data komponen_bop terkirim dengan benar?
```json
"komponen_bop": [
  {"component": "Listrik Mixer", "rate_per_hour": "1000"},
  {"component": "Rutin", "rate_per_hour": "500"}
]
```

### 2. Valid Components Count
Berapa jumlah komponen yang valid (rate > 0)?
```json
"count": 2  // Harusnya > 0 jika ada yang diisi
```

### 3. Component Check
Apakah setiap komponen di-check dengan benar?
```json
"hasComponent": true,
"hasRate": true,
"rate_value": 1000
```

## Kemungkinan Masalah:

### Masalah A: Data Tidak Terkirim
Jika log menunjukkan `komponen_bop: []` atau `null`:
- Form tidak mengirim data dengan benar
- JavaScript mungkin mengubah data sebelum submit

### Masalah B: Rate Selalu 0
Jika log menunjukkan `rate_value: 0` padahal sudah diisi:
- Input field tidak terbaca
- Nama field tidak match

### Masalah C: Validation Gagal
Jika tidak ada log sama sekali:
- Validation gagal sebelum masuk controller
- Cek error di browser console

---

**Setelah dapat log, kirim ke saya untuk analisis lebih lanjut!**
