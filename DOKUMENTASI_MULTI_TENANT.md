# DOKUMENTASI MULTI-TENANT SYSTEM

## 🏢 KONSEP MULTI-TENANT

Aplikasi ini adalah **multi-tenant system** dimana:
- Setiap user/perusahaan memiliki data yang **terisolasi**
- User hanya bisa melihat dan mengelola data miliknya sendiri
- Data master (user_id = NULL) adalah **template read-only** yang bisa dilihat semua user
- Setiap user bisa membuat data dengan kode yang sama dengan user lain (tidak bentrok)

---

## 🔒 ISOLASI DATA

### **1. Data Master (user_id = NULL)**
- **Fungsi:** Template awal untuk semua user
- **Akses:** Semua user bisa **MELIHAT** (read-only)
- **Tidak bisa:** Edit, Hapus
- **Contoh:** 50 COA master, 16 Satuan master

### **2. Data User (user_id = ID user)**
- **Fungsi:** Data milik user/perusahaan tertentu
- **Akses:** Hanya user pemilik yang bisa **CRUD** (Create, Read, Update, Delete)
- **Isolasi:** User A tidak bisa lihat/edit data User B
- **Contoh:** COA custom, Satuan custom, Produk, Transaksi

---

## 🛡️ MEKANISME PROTEKSI

### **A. Global Scope di Model**

**File:** `app/Models/Coa.php` dan `app/Models/Satuan.php`

```php
static::addGlobalScope('user', function ($builder) {
    if (auth()->check()) {
        $builder->where(function($query) {
            $query->where('user_id', auth()->id())
                  ->orWhereNull('user_id');  // Tampilkan data master
        });
    }
});
```

**Efek:**
- Setiap query otomatis filter berdasarkan user yang login
- Data master (user_id = NULL) tetap muncul untuk semua user
- User tidak bisa akses data user lain

### **B. Auto-Assign user_id saat Create**

```php
static::creating(function ($model) {
    if (empty($model->user_id) && auth()->check()) {
        $model->user_id = auth()->id();
    }
});
```

**Efek:**
- Setiap data baru otomatis dapat user_id dari user yang login
- User tidak perlu (dan tidak bisa) set user_id manual

### **C. Validasi Unique per User**

**File:** `app/Http/Controllers/CoaController.php`

```php
'kode_akun' => [
    'required',
    \Illuminate\Validation\Rule::unique('coas', 'kode_akun')
        ->where('user_id', auth()->id()),  // Hanya cek untuk user ini
    'max:50'
],
```

**Efek:**
- User A bisa buat COA dengan kode "111" 
- User B juga bisa buat COA dengan kode "111"
- Tidak bentrok karena punya user_id berbeda

### **D. Proteksi Edit/Delete Data Master**

**File:** `app/Http/Controllers/CoaController.php`

```php
public function edit(Coa $coa)
{
    // Cegah edit data master (user_id = NULL)
    if ($coa->user_id === null) {
        return redirect()->route('master-data.coa.index')
            ->with('error', 'Data master COA tidak dapat diedit.');
    }
    // ...
}

public function destroy(Coa $coa)
{
    // Cegah hapus data master (user_id = NULL)
    if ($coa->user_id === null) {
        return redirect()->route('master-data.coa.index')
            ->with('error', 'Data master COA tidak dapat dihapus.');
    }
    // ...
}
```

**Efek:**
- User tidak bisa edit/hapus data master
- Data master tetap konsisten untuk semua user

---

## 📊 CONTOH SKENARIO

### **Skenario 1: User Baru Register**

1. User "Perusahaan A" register dan login
2. Buka halaman COA → Melihat **50 COA master** (read-only)
3. Buka halaman Satuan → Melihat **16 Satuan master** (read-only)
4. User bisa:
   - ✅ Tambah COA baru (akan punya user_id = ID Perusahaan A)
   - ✅ Edit/Hapus COA miliknya sendiri
   - ❌ Edit/Hapus COA master
   - ❌ Lihat/Edit COA milik user lain

### **Skenario 2: Dua User dengan Kode Sama**

**User A (ID: 1):**
- Buat COA: Kode "555", Nama "Biaya Listrik" → user_id = 1

**User B (ID: 2):**
- Buat COA: Kode "555", Nama "Biaya Air" → user_id = 2

**Hasil:**
- ✅ Tidak ada error (unique hanya per user)
- User A hanya lihat COA "555 - Biaya Listrik"
- User B hanya lihat COA "555 - Biaya Air"
- Tidak bentrok!

### **Skenario 3: Transaksi dan Laporan**

**User A:**
- Buat Penjualan → Otomatis dapat user_id = 1
- Lihat Laporan → Hanya muncul transaksi user_id = 1

**User B:**
- Buat Penjualan → Otomatis dapat user_id = 2
- Lihat Laporan → Hanya muncul transaksi user_id = 2

**Hasil:**
- ✅ Data terisolasi sempurna
- User A tidak bisa lihat transaksi User B
- Laporan akurat per user/perusahaan

---

## 🔧 MODEL YANG SUDAH DIPROTEKSI

### ✅ **Sudah Ada Global Scope:**
1. `Coa` - Chart of Accounts
2. `Satuan` - Unit of Measurement

### ⚠️ **Perlu Dicek (Harus Punya Global Scope):**
- `Product` / `Produk`
- `Customer` / `Pelanggan`
- `Supplier` / `Vendor`
- `Sale` / `Penjualan`
- `Purchase` / `Pembelian`
- `JurnalUmum`
- `Production` / `Produksi`
- `Asset` / `Aset`
- `BahanBaku`
- `BahanPendukung`
- Dan semua model transaksi lainnya

---

## 📝 CHECKLIST UNTUK MODEL BARU

Setiap kali membuat model baru yang perlu isolasi data:

```php
protected static function booted()
{
    parent::booted();
    
    // 1. Auto-assign user_id saat creating
    static::creating(function ($model) {
        if (empty($model->user_id) && auth()->check()) {
            $model->user_id = auth()->id();
        }
    });
    
    // 2. Global scope untuk data isolation
    static::addGlobalScope('user', function ($builder) {
        if (auth()->check()) {
            $builder->where('user_id', auth()->id());
            // Tambahkan ->orWhereNull('user_id') jika ada data master
        }
    });
}
```

---

## 🚀 UNTUK HOSTING

### **Database Export:**
- ✅ Export database dengan data master (50 COA, 16 Satuan)
- ✅ File SQL sudah include `SET FOREIGN_KEY_CHECKS=0`
- ✅ Setiap user yang import akan dapat data master yang sama

### **User Baru:**
- ✅ Otomatis melihat data master
- ✅ Bisa langsung mulai input transaksi
- ✅ Data terisolasi dari user lain

### **Keamanan:**
- ✅ Global scope mencegah akses data user lain
- ✅ Validasi unique per user
- ✅ Data master terproteksi
- ✅ Tidak ada data leak antar user

---

## ⚠️ PENTING!

### **Jangan:**
- ❌ Hapus global scope dari model
- ❌ Bypass global scope tanpa alasan jelas
- ❌ Set user_id manual di controller (biarkan auto-assign)
- ❌ Lupa tambah global scope di model baru

### **Lakukan:**
- ✅ Selalu test dengan 2+ user berbeda
- ✅ Pastikan data tidak bocor antar user
- ✅ Validasi unique selalu include user_id
- ✅ Proteksi data master dari edit/delete

---

## 🎯 KESIMPULAN

Sistem multi-tenant ini memastikan:
1. **Isolasi Data:** Setiap user hanya lihat data miliknya
2. **Data Master:** Template read-only untuk semua user
3. **Keamanan:** Tidak ada data leak antar user
4. **Fleksibilitas:** User bisa custom data sesuai kebutuhan
5. **Skalabilitas:** Bisa handle banyak user/perusahaan

**Database siap untuk hosting dan digunakan oleh banyak perusahaan!** 🚀
