# Perbaikan Form Create BOM

## Masalah yang Ditemukan

1. **Tabel kosong**: Tidak ada baris bahan baku yang muncul saat halaman pertama kali dibuka
2. **Tombol "Tambah Baris" tidak berfungsi**: Klik tombol tidak menambah baris baru
3. **JavaScript tidak berjalan**: Event listener tidak ter-setup dengan baik

## Penyebab

1. JavaScript mencoba menambah baris pertama dengan `tambahBaris()` di `DOMContentLoaded`, tapi seharusnya baris pertama sudah ada di HTML
2. Event listener tidak di-attach ke elemen yang sudah ada
3. Fungsi `tambahBaris()` terlalu kompleks dan tidak ter-debug dengan baik

## Solusi yang Diterapkan

### 1. Menambahkan Baris Pertama di HTML

**Sebelum**: Tbody kosong, mengandalkan JavaScript untuk menambah baris pertama
```html
<tbody id="bomTableBody">
    <!-- Rows will be added dynamically -->
</tbody>
```

**Sesudah**: Baris pertama sudah ada di HTML
```html
<tbody id="bomTableBody">
    <!-- Baris pertama -->
    <tr data-row-id="1">
        <td>
            <select name="bahan_baku_id[]" class="form-select form-select-sm bahan-select" required>
                <option value="">-- Pilih Bahan Baku --</option>
                @foreach($bahanBakus as $bahan)
                    ...
                @endforeach
            </select>
        </td>
        ...
    </tr>
</tbody>
```

### 2. Memperbaiki Event Listener

**Menambahkan event listener untuk baris pertama di DOMContentLoaded**:
```javascript
document.addEventListener('DOMContentLoaded', function() {
    const firstRow = document.querySelector('#bomTableBody tr[data-row-id="1"]');
    if (firstRow) {
        const bahanSelect = firstRow.querySelector('.bahan-select');
        const jumlahInput = firstRow.querySelector('.jumlah-input');
        const satuanSelect = firstRow.querySelector('.satuan-select');
        
        bahanSelect.addEventListener('change', function() {
            updateHargaSatuan(this);
        });
        
        jumlahInput.addEventListener('input', function() {
            hitungSubtotal(this);
        });
        
        satuanSelect.addEventListener('change', function() {
            hitungSubtotal(jumlahInput);
        });
    }
});
```

### 3. Menyederhanakan Fungsi tambahBaris()

**Perubahan**:
- Menambahkan console.log untuk debugging
- Langsung attach event listener setelah membuat elemen
- Tidak menggunakan inline onclick, tapi addEventListener
- Menambahkan validasi dan error handling

```javascript
function tambahBaris() {
    rowCount++;
    console.log('Menambah baris ke-' + rowCount);
    
    const tbody = document.getElementById('bomTableBody');
    if (!tbody) {
        console.error('Tbody tidak ditemukan!');
        return;
    }
    
    const row = document.createElement('tr');
    row.dataset.rowId = rowCount;
    
    // ... create row HTML ...
    
    tbody.appendChild(row);
    
    // Setup event listeners untuk baris baru
    const bahanSelect = row.querySelector('.bahan-select');
    const jumlahInput = row.querySelector('.jumlah-input');
    const satuanSelect = row.querySelector('.satuan-select');
    const btnHapus = row.querySelector('.btn-hapus');
    
    bahanSelect.addEventListener('change', function() {
        updateHargaSatuan(this);
    });
    
    jumlahInput.addEventListener('input', function() {
        hitungSubtotal(this);
    });
    
    satuanSelect.addEventListener('change', function() {
        hitungSubtotal(jumlahInput);
    });
    
    btnHapus.addEventListener('click', function() {
        // ... handle delete ...
    });
    
    hitungTotal();
}
```

### 4. Menambahkan Console.log untuk Debugging

Menambahkan console.log di berbagai tempat untuk memudahkan debugging:
- Saat DOM loaded
- Saat tombol tambah baris diklik
- Saat baris berhasil ditambahkan
- Saat terjadi error

## Hasil Setelah Perbaikan

✅ **Baris pertama muncul** saat halaman dibuka
✅ **Tombol "Tambah Baris" berfungsi** - menambah baris baru dengan benar
✅ **Event listener berfungsi** - perubahan bahan baku, jumlah, dan satuan langsung update harga dan subtotal
✅ **Tombol hapus berfungsi** - bisa hapus baris (minimal 1 baris harus tetap ada)
✅ **Perhitungan otomatis** - Total Bahan Baku, BTKL, BOP, dan Total HPP dihitung otomatis

## Testing

1. Buka halaman create BOM
2. Pilih produk
3. Lihat baris pertama sudah muncul dengan dropdown bahan baku
4. Pilih bahan baku → Harga satuan dan keterangan konversi muncul
5. Ubah jumlah → Subtotal update otomatis
6. Ubah satuan → Harga satuan dan subtotal update otomatis
7. Klik "Tambah Baris" → Baris baru muncul
8. Isi baris kedua → Perhitungan total update otomatis
9. Klik tombol hapus → Baris terhapus (jika lebih dari 1 baris)
10. Klik "Simpan BOM" → Data tersimpan ke database

## Debugging

Jika masih ada masalah, buka Console Browser (F12) dan lihat:
- "DOM loaded, initializing..." → DOM sudah siap
- "Tombol tambah baris diklik" → Tombol berfungsi
- "Menambah baris ke-X" → Fungsi tambahBaris() dipanggil
- "Baris berhasil ditambahkan" → Baris berhasil ditambahkan ke tabel

Jika ada error, akan muncul di console dengan warna merah.
