# Kebijakan Jabatan - User Membuat Sendiri

**Date**: 6 Mei 2026  
**Decision**: Jabatan TIDAK di-seed otomatis  
**Reason**: Setiap bisnis punya struktur jabatan yang berbeda

---

## 🎯 KEPUTUSAN

**Jabatan TIDAK akan di-seed otomatis** saat user baru register.

### Alasan:

1. **Setiap bisnis berbeda**
   - UMKM A mungkin hanya butuh 3 jabatan
   - UMKM B mungkin butuh 15 jabatan
   - Struktur organisasi setiap perusahaan unik

2. **Nama jabatan berbeda**
   - Ada yang pakai "Operator", ada yang "Karyawan Produksi"
   - Ada yang pakai "Supervisor", ada yang "Kepala Produksi"
   - Setiap perusahaan punya istilah sendiri

3. **Gaji berbeda**
   - Gaji di Jakarta berbeda dengan di daerah
   - Setiap perusahaan punya budget berbeda
   - Tidak bisa di-generalisir

4. **Kategori berbeda**
   - Tidak semua perusahaan pakai BTKL/BTKTL
   - Ada yang pakai sistem lain
   - Lebih fleksibel jika user buat sendiri

---

## ✅ YANG TETAP DI-SEED OTOMATIS

### 1. COA (Chart of Accounts) - 51 akun ✅

**Alasan**: COA adalah standar akuntansi yang universal
- Aset, Kewajiban, Modal, Pendapatan, Biaya
- Struktur COA relatif sama untuk semua bisnis
- User bisa tambah/edit sesuai kebutuhan

### 2. Satuan (Units) - 16 satuan ✅

**Alasan**: Satuan adalah standar pengukuran yang universal
- KG, Liter, PCS, dll adalah standar
- Semua bisnis butuh satuan dasar ini
- User bisa tambah satuan khusus jika perlu

### 3. Jabatan - TIDAK ❌

**Alasan**: Jabatan sangat spesifik per bisnis
- Struktur organisasi berbeda-beda
- Nama jabatan berbeda-beda
- Gaji berbeda-beda
- **User harus buat sendiri sesuai kebutuhan**

---

## 📋 IMPLEMENTASI

### File: `app/Listeners/CreateDefaultUserData.php`

```php
public function handle(UserRegistered $event): void
{
    // ✅ Create default COA (51 accounts)
    $coaSeeder = new DefaultCoaSeeder();
    $coaSeeder->run($event->user->id);
    
    // ✅ Create default Satuan (16 units)
    $satuanSeeder = new DefaultSatuanSeeder();
    $satuanSeeder->run($event->user->id);
    
    // ❌ Jabatan TIDAK di-seed otomatis
    // User harus membuat Jabatan sendiri
}
```

---

## 🗑️ HAPUS DATA JABATAN YANG SUDAH ADA

Jika Anda ingin menghapus semua data Jabatan yang sudah ter-seed:

```bash
php delete_all_jabatan.php
```

Script ini akan:
1. Tampilkan jumlah jabatan yang akan dihapus
2. Minta konfirmasi (ketik "yes")
3. Hapus semua data jabatan
4. Verify database bersih

---

## 📖 PANDUAN UNTUK USER

### Cara Membuat Jabatan

1. Login ke sistem
2. Buka menu: **Master Data > Jabatan**
3. Klik tombol **"Tambah Jabatan"**
4. Isi form:
   - Kode Jabatan (contoh: BT001)
   - Nama Jabatan (contoh: Operator Produksi)
   - Kategori: BTKL atau BTKTL
   - Gaji/Tarif sesuai kebutuhan
5. Simpan

### Contoh Jabatan BTKL (Biaya Tenaga Kerja Langsung)

Untuk tenaga kerja yang terlibat langsung dalam produksi:
- Operator Produksi
- Perbumbuan
- Pengemasan
- Penggorengan
- dll

**Dibayar per jam** (tarif_per_jam)

### Contoh Jabatan BTKTL (Biaya Tenaga Kerja Tidak Langsung)

Untuk tenaga kerja pendukung:
- Supervisor
- Admin
- Kasir
- Quality Control
- Gudang
- Security
- dll

**Dibayar per bulan** (gaji_pokok)

---

## 🔄 JIKA INGIN AKTIFKAN KEMBALI AUTO-SEED

Jika di masa depan Anda ingin aktifkan kembali auto-seed Jabatan:

### Edit: `app/Listeners/CreateDefaultUserData.php`

```php
public function handle(UserRegistered $event): void
{
    // Create default COA
    $coaSeeder = new DefaultCoaSeeder();
    $coaSeeder->run($event->user->id);
    
    // Create default Satuan
    $satuanSeeder = new DefaultSatuanSeeder();
    $satuanSeeder->run($event->user->id);
    
    // ✅ UNCOMMENT untuk aktifkan auto-seed Jabatan
    $jabatanSeeder = new \Database\Seeders\DefaultJabatanSeeder();
    $jabatanSeeder->run($event->user->id);
}
```

---

## 📊 PERBANDINGAN

### Sebelum (Auto-Seed Jabatan)

**Kelebihan**:
- User langsung bisa pakai
- Tidak perlu setup manual

**Kekurangan**:
- Jabatan mungkin tidak sesuai
- Gaji tidak sesuai
- User harus edit/hapus yang tidak perlu
- Membingungkan user

### Sesudah (User Buat Sendiri)

**Kelebihan**:
- User buat sesuai kebutuhan
- Nama jabatan sesuai
- Gaji sesuai budget
- Lebih fleksibel
- User lebih paham sistemnya

**Kekurangan**:
- User harus setup manual
- Butuh waktu lebih lama di awal

---

## ✅ KESIMPULAN

**Keputusan Final**: Jabatan TIDAK di-seed otomatis

**Alasan Utama**:
1. Setiap bisnis punya struktur organisasi yang berbeda
2. Nama jabatan dan gaji sangat bervariasi
3. Lebih baik user buat sendiri sesuai kebutuhan
4. COA dan Satuan cukup sebagai data awal

**Yang Tetap Auto-Seed**:
- ✅ COA (51 accounts) - Standar akuntansi
- ✅ Satuan (16 units) - Standar pengukuran
- ❌ Jabatan - User buat sendiri

---

**STATUS**: ✅ IMPLEMENTED

**READY TO PUSH**: YES

---

Generated: 6 Mei 2026  
By: Kiro AI Assistant  
For: UMKM COE Multi-Tenant System
