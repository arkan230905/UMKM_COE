# 🔒 Multi-Tenant Security Guidelines

## ⚠️ CRITICAL: Prevent Data Leakage

Sistem ini adalah **multi-tenant application** dimana setiap user/owner hanya boleh melihat data mereka sendiri.

---

## ✅ Checklist untuk SETIAP Model yang Buat

### 1. **Database Schema**
- [ ] Pastikan table punya column `user_id` (BigInteger unsigned, nullable)
- [ ] Tambah foreign key: `$table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');`
- [ ] Migration example:
```php
Schema::table('table_name', function (Blueprint $table) {
    if (!Schema::hasColumn('table_name', 'user_id')) {
        $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
    }
});
```

### 2. **Model Setup**
- [ ] Tambah `use MultiTenantModel;` trait ke model
- [ ] Tambah `'user_id'` ke `$fillable` array
- [ ] Model auto-filter setiap query by user_id

Example:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantModel;

class YourModel extends Model
{
    use MultiTenantModel;

    protected $fillable = ['user_id', 'nama', 'deskripsi'];
}
```

### 3. **Controller - Read Operations**
- [ ] JANGAN query tanpa filter user_id
- [ ] ❌ WRONG: `YourModel::all();`
- [ ] ✅ RIGHT: `YourModel::where('user_id', auth()->id())->get();` 
- ✅ BEST: Gunakan trait `MultiTenantModel` - otomatis ter-filter

### 4. **Controller - Create/Update Operations**
- [ ] Pastikan `user_id` diset saat create
- [ ] Verify ownership saat update/delete

Example:
```php
// CREATE - user_id otomatis diset di model boot()
public function store(Request $request) {
    $model = YourModel::create($request->validated());
    return redirect()->with('success', 'Data created');
}

// UPDATE - Verify ownership
public function update(Request $request, $id) {
    $model = YourModel::where('user_id', auth()->id())->findOrFail($id);
    $model->update($request->validated());
    return redirect()->with('success', 'Data updated');
}

// DELETE - Verify ownership
public function destroy($id) {
    $model = YourModel::where('user_id', auth()->id())->findOrFail($id);
    $model->delete();
    return redirect()->with('success', 'Data deleted');
}
```

### 5. **API Responses**
- [ ] Jangan return data user lain meskipun query error
- [ ] Gunakan `findOrFail($id)` untuk throw 404, bukan data leak

### 6. **Relations/Joins**
- [ ] Pastikan join filter by user_id juga
- [ ] Query dengan relationship harus tetap filter

Example:
```php
// ❌ WRONG - tidak filter user_id di relation
$models = YourModel::with('related')->get();

// ✅ RIGHT - filter user_id di query utama
$models = YourModel::where('user_id', auth()->id())
    ->with('related')
    ->get();
```

---

## 📋 Models yang Sudah Diperbaiki

✅ **Pelanggan** - use MultiTenantModel
✅ **PaketMenu** - use MultiTenantModel  
✅ **OngkirSetting** - use MultiTenantModel
✅ **Penjualan** - filter by user_id (manual checked)
✅ **Produk** - filter by user_id (manual checked)

---

## 🔍 Audit Models

Untuk setiap model yang handle user data:
1. Cek apakah punya `user_id` column ✓
2. Cek apakah query ter-filter by `user_id` ✓
3. Cek apakah verify ownership pada delete/update ✓
4. Cek apakah relasi tetap filter user_id ✓

---

## 🚨 Testing Multi-Tenant

### Manual Testing:
1. Login sebagai User A, create data
2. Logout
3. Login sebagai User B
4. Verify data User A TIDAK muncul

### Automated Testing:
```php
test('user cannot see other user data', function() {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    
    $dataA = YourModel::factory()->create(['user_id' => $userA->id]);
    
    $this->actingAs($userB);
    $result = YourModel::find($dataA->id);
    
    $this->assertNull($result); // Should not find
});
```

---

## 🛑 Common Mistakes

1. ❌ Query tanpa filter: `User::find($id)`
   ✅ Benar: `User::where('user_id', auth()->id())->find($id)`

2. ❌ Lupa tambah user_id saat create
   ✅ Model boot() otomatis isi

3. ❌ Relasi tidak filter user_id
   ✅ Filter di query parent

4. ❌ API return semua data
   ✅ Filter by user_id

---

## 📚 Resources

- Trait: `/app/Traits/MultiTenantModel.php`
- Models using trait: Pelanggan, PaketMenu, OngkirSetting
- Check: CRITICAL setiap model baru

---

**Last Updated:** 2026-06-15
**Status:** 🟢 Active Security Policy
