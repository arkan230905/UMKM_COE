# 🔧 DEBUG PENYIMPANAN PENGAJIAN

## 🚨 Langkah-langkah Debug:

### **1. Test Form di Browser:**

1. **Buka halaman:** http://127.0.0.1:8000/transaksi/penggajian/create
2. **Buka Developer Console:** F12 → Console tab
3. **Pilih pegawai** → lihat console log
4. **Isi bonus & potongan**
5. **Klik "Simpan Penggajian"** → lihat console log

### **2. Cek Console Log:**
```
Form data yang akan dikirim:
pegawai_id: 9
tanggal_penggajian: 2026-02-04
coa_kasbank: 1101
gaji_pokok: 0
tarif_per_jam: 45000
tunjangan: 450000
asuransi: 0
total_jam_kerja: 0
jenis_pegawai: btkl
bonus: 500000
potongan: 100000
```

### **3. Cek Error di Browser:**
- **Network tab:** Lihat request yang dikirim
- **Response tab:** Lihat error message dari server
- **Console tab:** Lihat JavaScript errors

### **4. Cek Laravel Log:**
```bash
# Buka file log
storage/logs/laravel.log

# Atau clear log dulu
echo "" > storage/logs/laravel.log
```

### **5. Test Manual dengan Postman/Curl:**
```bash
curl -X POST http://127.0.0.1:8000/transaksi/penggajian \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-CSRF-TOKEN: YOUR_CSRF_TOKEN" \
  -d "pegawai_id=9&tanggal_penggajian=2026-02-04&coa_kasbank=1101&bonus=500000&potongan=100000&gaji_pokok=0&tarif_per_jam=45000&tunjangan=450000&asuransi=0&total_jam_kerja=0&jenis_pegawai=btkl"
```

---

## 🔍 Common Issues & Solutions:

### **Issue 1: CSRF Token Mismatch**
**Error:** "CSRF token mismatch"
**Solution:** Refresh halaman atau clear browser cache

### **Issue 2: Validation Error**
**Error:** "The given data was invalid"
**Solution:** Cek semua required fields terisi

### **Issue 3: Database Connection**
**Error:** "SQLSTATE connection failed"
**Solution:** Cek database connection di .env

### **Issue 4: Permission Error**
**Error:** "Permission denied"
**Solution:** Cek folder permissions storage & bootstrap/cache

---

## 🎯 Quick Test Script:

Tambahkan di controller untuk debug:
```php
public function store(Request $request)
{
    // Debug input
    \Log::info('Request data:', $request->all());
    
    // Debug validation
    try {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            // ... other validations
        ]);
        \Log::info('Validation passed');
    } catch (\Exception $e) {
        \Log::error('Validation failed: ' . $e->getMessage());
        throw $e;
    }
    
    // ... rest of code
}
```

---

## 📋 Test Checklist:

- [ ] Form terbuka tanpa error
- [ ] Pegawai dropdown populated
- [ ] Data pegawai muncul saat dipilih
- [ ] Bonus & potongan terhitung di total
- [ ] Console log menampilkan data form
- [ ] Network request berhasil (200 OK)
- [ ] Laravel log tidak ada error
- [ ] Data tersimpan di database
- [ ] Redirect ke halaman index dengan success message

---

## 🚀 Jika Masih Error:

1. **Screenshot error message**
2. **Copy console log**
3. **Copy Laravel log**
4. **Copy Network request/response**

Kirim ke saya untuk analisis lebih lanjut!
