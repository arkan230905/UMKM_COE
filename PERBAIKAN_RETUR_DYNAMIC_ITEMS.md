# Perbaikan Retur: Dynamic Items (Produk/Bahan Baku)

## ✅ Yang Sudah Diperbaiki

### 1. View Create (`resources/views/transaksi/retur/create.blade.php`)

#### A. Header Table Dynamic
```blade
<th width="50%" id="headerItemName">PRODUK</th>
```
- Header akan berubah dari "PRODUK" ke "BAHAN BAKU" saat pilih tipe retur

#### B. JavaScript Function `changeReturType()`
```javascript
function changeReturType() {
    const type = document.getElementById('type').value;
    const headerItemName = document.getElementById('headerItemName');
    
    // Update header
    if (type === 'sale') {
        headerItemName.textContent = 'PRODUK';
    } else {
        headerItemName.textContent = 'BAHAN BAKU';
    }
    
    // Update all select options
    selects.forEach(select => {
        if (type === 'sale') {
            // Tampilkan list produk
            produks.forEach(item => {
                option.textContent = item.nama_produk;
            });
        } else {
            // Tampilkan list bahan baku
            bahanBakus.forEach(item => {
                option.textContent = item.nama_bahan;
            });
        }
    });
}
```

### 2. Controller (`app/Http/Controllers/ReturController.php`)

#### A. Validasi Dynamic
```php
// Validasi berdasarkan tipe retur
if ($request->type === 'sale') {
    // Retur penjualan: validasi produk_id ada di tabel produks
    foreach ($request->details as $detail) {
        if (!Produk::find($detail['produk_id'])) {
            return back()->withErrors(['details' => 'Produk tidak ditemukan']);
        }
    }
} else {
    // Retur pembelian: validasi produk_id (sebenarnya bahan_baku_id)
    foreach ($request->details as $detail) {
        if (!\App\Models\BahanBaku::find($detail['produk_id'])) {
            return back()->withErrors(['details' => 'Bahan baku tidak ditemukan']);
        }
    }
}
```

### 3. View Index (`resources/views/transaksi/retur/index.blade.php`)

#### A. Display Dynamic Items
```blade
@foreach($retur->details as $d)
    @php
        // Untuk retur penjualan: tampilkan produk
        // Untuk retur pembelian: tampilkan bahan baku
        if ($retur->type === 'sale') {
            $itemName = $d->produk->nama_produk ?? '-';
        } else {
            $bahanBaku = \App\Models\BahanBaku::find($d->produk_id);
            $itemName = $bahanBaku->nama_bahan ?? '-';
        }
    @endphp
    <div>{{ $itemName }} <span class="text-muted">x {{ $d->qty }}</span></div>
@endforeach
```

## 🔄 Alur Lengkap

### Retur Penjualan (Sale):
```
1. User pilih "Retur Penjualan"
2. Header berubah: "PRODUK"
3. Dropdown menampilkan: List Produk
4. User pilih produk + qty
5. Submit → Validasi produk_id di tabel produks
6. Simpan ke retur_details dengan produk_id
7. Di index: Tampilkan nama produk
```

### Retur Pembelian (Purchase):
```
1. User pilih "Retur Pembelian"
2. Header berubah: "BAHAN BAKU"
3. Dropdown menampilkan: List Bahan Baku
4. User pilih bahan baku + qty
5. Submit → Validasi produk_id (sebenarnya bahan_baku_id) di tabel bahan_bakus
6. Simpan ke retur_details dengan produk_id (yang isinya bahan_baku_id)
7. Di index: Tampilkan nama bahan baku
```

## 📊 Struktur Data

### Tabel: retur_details
```
- id
- retur_id
- produk_id  ← Untuk sale: produk_id, untuk purchase: bahan_baku_id
- qty
- harga_satuan_asal
```

**Note:** Kolom `produk_id` digunakan untuk menyimpan:
- ID Produk (jika retur penjualan)
- ID Bahan Baku (jika retur pembelian)

## 🎯 Testing

### Test Retur Penjualan:
1. Buka `/transaksi/retur/create`
2. Pilih "Retur Penjualan"
3. Pastikan header = "PRODUK"
4. Pastikan dropdown menampilkan list produk
5. Pilih produk + qty
6. Submit
7. Cek di index: Nama produk muncul dengan benar

### Test Retur Pembelian:
1. Buka `/transaksi/retur/create`
2. Pilih "Retur Pembelian"
3. Pastikan header = "BAHAN BAKU"
4. Pastikan dropdown menampilkan list bahan baku
5. Pilih bahan baku + qty
6. Submit
7. Cek di index: Nama bahan baku muncul dengan benar

## ✅ Checklist

- [x] View create: Header dynamic
- [x] View create: Dropdown dynamic (produk/bahan baku)
- [x] View create: JavaScript changeReturType()
- [x] Controller: Validasi dynamic
- [x] View index: Display dynamic items
- [x] No diagnostics errors
- [ ] Test retur penjualan
- [ ] Test retur pembelian
- [ ] Verify data di database

## 🚀 Next Steps

1. Test create retur penjualan dengan produk
2. Test create retur pembelian dengan bahan baku
3. Verify di index menampilkan nama yang benar
4. Implement posting logic untuk update stok
5. Implement jurnal untuk retur

---

**Status:** ✅ Dynamic Items Complete
**Impact:** Retur sekarang bisa handle produk dan bahan baku dengan benar
