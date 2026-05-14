# Perbaikan Logic Retur Pembelian

## Masalah yang Diperbaiki

Sebelumnya, sistem menampilkan error "Quantity retur harus diisi" untuk semua item, padahal user seharusnya bisa memilih hanya item tertentu yang ingin diretur. Item dengan qty retur kosong atau 0 seharusnya tidak masuk ke data retur.

## Solusi yang Diterapkan

### 1. File yang Diubah

#### A. `app/Http/Controllers/ReturController.php`

**Perubahan di Method `storePembelian()`:**

1. **Validasi Request (Line ~237-260)**
   - **SEBELUM:** `'items.*.qty' => 'required|numeric|min:0.01|max:999999'`
   - **SESUDAH:** `'items.*.qty' => 'nullable|numeric|min:0|max:999999'`
   - **Alasan:** Qty tidak wajib diisi (nullable), dan min diubah ke 0 agar user bisa mengosongkan atau mengisi 0 untuk item yang tidak diretur

2. **Filter Items (Line ~289-300)**
   ```php
   // IMPORTANT: Only process items with qty > 0
   // Items with empty or 0 qty will be excluded from retur
   $items = collect($request->items)->filter(function($item) {
       $qty = isset($item['qty']) ? (float)$item['qty'] : 0;
       return $qty > 0; // Only include items with qty greater than 0
   });
   ```
   - Filter hanya memproses item dengan qty > 0
   - Item dengan qty kosong atau 0 akan diabaikan

3. **Error Message yang Lebih Jelas (Line ~302-306)**
   ```php
   if ($items->isEmpty()) {
       return redirect()->back()
           ->withErrors(['items' => 'Minimal satu item harus diisi qty retur lebih dari 0. Contoh: Jagung qty retur 5, Ketan qty retur 0 (tidak diretur).'])
           ->withInput();
   }
   ```

#### B. `resources/views/transaksi/retur-pembelian/create.blade.php`

**Perubahan di View:**

1. **Panduan Pengisian yang Lebih Lengkap (Line ~70-78)**
   - Ditambahkan instruksi: "Item yang Tidak Diretur: Biarkan qty retur kosong atau 0"
   - Ditambahkan contoh kasus: "Jika hanya ingin retur Jagung 5 kg, isi qty retur Jagung = 5, biarkan Ketan kosong/0"

2. **Input Qty Retur (Line ~215-222)**
   - **SEBELUM:** `min="0.01"` dan `placeholder="Masukkan qty > 0"`
   - **SESUDAH:** `min="0"` dan `placeholder="0 = tidak diretur"`
   - **Alasan:** User bisa mengisi 0 atau mengosongkan untuk item yang tidak diretur

### 2. Logic Baru yang Diterapkan

#### Alur Kerja Retur:

```
1. User mengisi form retur
   ├─ Pilih jenis retur (tukar_barang / refund)
   ├─ Isi alasan retur
   └─ Isi qty retur untuk item yang ingin diretur

2. Validasi Laravel (Controller)
   ├─ Jenis retur: required
   ├─ Alasan: required, min 3 karakter
   └─ Items qty: nullable, numeric, min 0

3. Filter Items (Controller)
   ├─ Loop semua items dari form
   ├─ Ambil hanya item dengan qty > 0
   └─ Item dengan qty = 0 atau kosong diabaikan

4. Validasi Lanjutan (Controller)
   ├─ Minimal harus ada 1 item dengan qty > 0
   ├─ Qty retur tidak boleh > qty pembelian
   └─ Untuk tukar_barang: cek stok tersedia

5. Simpan ke Database
   ├─ Buat record PurchaseReturn
   ├─ Loop hanya item yang lolos filter (qty > 0)
   ├─ Buat record PurchaseReturnItem untuk setiap item
   ├─ Update stok sesuai jenis retur
   └─ Buat jurnal akuntansi
```

#### Rumus Perhitungan:

```
Subtotal per Item = Qty Retur × Harga Satuan
Total Retur = Σ(Subtotal Item yang qty > 0)
PPN = Total Retur × PPN%
Grand Total = Total Retur + PPN
```

### 3. Contoh Kasus: Jagung dan Ketan

#### Skenario:
**Pembelian:**
- Jagung: 20 kg × Rp 15.000 = Rp 300.000
- Ketan: 15 kg × Rp 18.000 = Rp 270.000

**Retur:**
- User hanya ingin retur Jagung 5 kg
- Ketan tidak diretur

#### Cara Pengisian Form:

| No | Item   | Qty Dibeli | Qty Retur | Satuan | Harga Satuan | Subtotal    |
|----|--------|------------|-----------|--------|--------------|-------------|
| 1  | Jagung | 20 kg      | **5**     | kg     | Rp 15.000    | Rp 75.000   |
| 2  | Ketan  | 15 kg      | **0** atau **kosong** | kg | Rp 18.000 | Rp 0 |

#### Hasil yang Tersimpan:

**Tabel `purchase_returns`:**
```
id: 1
return_number: RTR-2026-0001
pembelian_id: 7
jenis_retur: refund
total_return_amount: 75000
```

**Tabel `purchase_return_items`:**
```
id: 1
purchase_return_id: 1
bahan_baku_id: 5 (Jagung)
quantity: 5
unit_price: 15000
subtotal: 75000
```

**Ketan TIDAK masuk ke tabel `purchase_return_items`** karena qty retur = 0

#### Update Stok:

**Jika Jenis Retur = Refund:**
- Stok Jagung berkurang 5 kg (barang rusak keluar dari sistem)
- Stok Ketan tidak berubah

**Jika Jenis Retur = Tukar Barang:**
- Stok Jagung berkurang 5 kg (barang rusak)
- Stok Jagung bertambah 5 kg (barang baru pengganti)
- Net: Stok Jagung tidak berubah, tapi ada 2 stock movement
- Stok Ketan tidak berubah

#### Jurnal Akuntansi (Refund):

```
Debit:  Hutang Usaha         Rp 75.000
Credit: Persediaan Bahan Baku Rp 75.000
```

### 4. Validasi yang Diterapkan

#### A. Validasi Laravel (Backend)

1. **Jenis Retur:** Required, harus "tukar_barang" atau "refund"
2. **Alasan:** Required, min 3 karakter, max 500 karakter
3. **Items Qty:** Nullable, numeric, min 0, max 999999
4. **Minimal 1 Item:** Setelah filter, minimal harus ada 1 item dengan qty > 0
5. **Qty Max:** Qty retur tidak boleh > qty pembelian
6. **Stok Tersedia:** Untuk tukar_barang, cek stok saat ini harus >= qty retur

#### B. Validasi JavaScript (Frontend)

1. **Jenis Retur:** Harus dipilih sebelum submit
2. **Alasan:** Harus diisi sebelum submit
3. **Minimal 1 Item:** Minimal 1 item harus memiliki qty > 0
4. **Real-time Calculation:** Subtotal dan total dihitung otomatis saat user input qty

### 5. Cara Testing

#### Test Case 1: Retur Hanya 1 Item (Jagung)

**Input:**
- Jenis Retur: Refund
- Alasan: Barang rusak
- Jagung qty retur: 5
- Ketan qty retur: 0 (atau kosong)

**Expected Result:**
- ✅ Form berhasil submit
- ✅ Hanya Jagung yang masuk ke tabel `purchase_return_items`
- ✅ Ketan tidak masuk ke tabel `purchase_return_items`
- ✅ Total retur = Rp 75.000 (5 × 15.000)
- ✅ Stok Jagung berkurang 5 kg
- ✅ Stok Ketan tidak berubah

#### Test Case 2: Retur Kedua Item

**Input:**
- Jenis Retur: Tukar Barang
- Alasan: Barang rusak
- Jagung qty retur: 5
- Ketan qty retur: 3

**Expected Result:**
- ✅ Form berhasil submit
- ✅ Jagung dan Ketan masuk ke tabel `purchase_return_items`
- ✅ Total retur = Rp 129.000 (5×15.000 + 3×18.000)
- ✅ Stok Jagung: -5 kg (rusak) +5 kg (baru) = 0 net change
- ✅ Stok Ketan: -3 kg (rusak) +3 kg (baru) = 0 net change

#### Test Case 3: Semua Item Qty = 0

**Input:**
- Jenis Retur: Refund
- Alasan: Barang rusak
- Jagung qty retur: 0
- Ketan qty retur: 0

**Expected Result:**
- ❌ Form gagal submit
- ❌ Error: "Minimal satu item harus diisi qty retur lebih dari 0"

#### Test Case 4: Qty Retur > Qty Pembelian

**Input:**
- Jenis Retur: Refund
- Alasan: Barang rusak
- Jagung qty retur: 25 (lebih dari 20 yang dibeli)

**Expected Result:**
- ❌ Form gagal submit
- ❌ Error: "Qty retur (25) tidak boleh melebihi qty pembelian (20) untuk item: Jagung"

#### Test Case 5: Tukar Barang Tanpa Stok

**Input:**
- Jenis Retur: Tukar Barang
- Alasan: Barang rusak
- Jagung qty retur: 5
- Stok Jagung saat ini: 2 kg (kurang dari 5)

**Expected Result:**
- ❌ Form gagal submit
- ❌ Error: "Stok tidak mencukupi untuk retur tukar barang. Item: Jagung, Stok saat ini: 2, Qty retur: 5"

### 6. Debugging

Jika ada masalah, cek log Laravel:

```bash
tail -f storage/logs/laravel.log
```

**Log yang Dicatat:**

1. **Form Submission:**
   ```
   === RETUR FORM SUBMISSION START ===
   request_data: [...]
   user_id: 4
   ```

2. **Validation:**
   ```
   Validation passed
   validated_data: [...]
   ```

3. **Items Filtering:**
   ```
   Items filtered
   original_count: 2
   filtered_count: 1
   filtered_items: [
     {index: 0, pembelian_detail_id: 13, qty: 5},
   ]
   ```

4. **Success:**
   ```
   === RETUR SUCCESSFULLY SAVED ===
   return_id: 1
   return_number: RTR-2026-0001
   total_amount: 75000
   items_count: 1
   ```

### 7. Penyebab Masalah Sebelumnya

**Masalah:**
- Validasi Laravel memaksa semua item harus diisi qty (`'items.*.qty' => 'required'`)
- User tidak bisa mengosongkan atau mengisi 0 untuk item yang tidak diretur

**Solusi:**
- Ubah validasi menjadi `nullable` dan `min:0`
- Filter items di controller untuk hanya memproses item dengan qty > 0
- Update UI untuk memberikan instruksi yang jelas

### 8. Fitur yang Tidak Berubah

✅ Perhitungan subtotal dan total tetap akurat
✅ Update stok tetap berfungsi sesuai jenis retur
✅ Jurnal akuntansi tetap dibuat dengan benar
✅ Validasi qty max tetap berfungsi
✅ Validasi stok untuk tukar_barang tetap berfungsi
✅ Laporan retur tetap menampilkan data yang benar

### 9. Catatan Penting

1. **Item dengan qty = 0 atau kosong TIDAK akan tersimpan** di tabel `purchase_return_items`
2. **Minimal harus ada 1 item dengan qty > 0** untuk bisa submit form
3. **Qty retur tidak boleh melebihi qty pembelian** untuk setiap item
4. **Untuk tukar_barang, stok harus mencukupi** untuk item yang diretur
5. **Total retur dihitung hanya dari item yang qty > 0**

### 10. Commit Message

```
fix: Perbaiki logic retur pembelian - hanya proses item dengan qty > 0

- Ubah validasi qty dari required menjadi nullable
- Filter items untuk hanya memproses qty > 0
- Item dengan qty = 0 atau kosong tidak masuk ke data retur
- Update panduan pengisian di view
- Tambah contoh kasus Jagung dan Ketan di instruksi
- Perbaiki error message untuk lebih jelas
```

## Status: ✅ SELESAI

Semua requirement telah diimplementasikan:
- ✅ User bisa memilih item tertentu yang ingin diretur
- ✅ Item dengan qty retur = 0 atau kosong tidak masuk ke data retur
- ✅ Validasi qty retur (min 0, max qty pembelian)
- ✅ Minimal 1 item harus diisi qty > 0
- ✅ Subtotal dan total dihitung hanya untuk item yang diretur
- ✅ Update stok sesuai jenis retur
- ✅ Jurnal akuntansi dibuat dengan benar
- ✅ Tidak mengubah migration/database
