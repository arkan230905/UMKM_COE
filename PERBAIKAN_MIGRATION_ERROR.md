# PERBAIKAN ERROR MIGRATION

## Masalah yang Terjadi:
```
2026_04_09_214439_fix_journal_lines_column_name .... 32.90ms FAIL

ErrorException 
Trying to access array offset on null
at vendor\laravel\framework\src\Illuminate\Database\Schema\Grammars\MySqlGrammar.php:362
```

## Root Cause Analysis:

### 1. Migration `fix_journal_lines_column_name`
**Masalah:** Migration mencoba rename kolom `account_id` ke `coa_id`, tetapi kolom `account_id` tidak ada di tabel `journal_lines`.

**Struktur Tabel Aktual:**
```
Columns in journal_lines table:
- id
- journal_entry_id
- coa_id          ← Kolom sudah ada
- debit
- credit
- memo
- created_at
- updated_at
```

**Kesimpulan:** Kolom `coa_id` sudah ada, migration tidak diperlukan.

### 2. Migration `add_keterangan_to_stock_movements_table`
**Masalah:** Migration mencoba menambahkan kolom `keterangan` yang sudah ada di tabel `stock_movements`.

**Error:**
```
SQLSTATE[42S21]: Column already exists: 1060 Duplicate column name 'keterangan'
```

## Solusi yang Diimplementasikan:

### 1. Perbaikan Migration Journal Lines
**File:** `database/migrations/2026_04_09_214439_fix_journal_lines_column_name.php`

```php
public function up(): void
{
    // Check if account_id column exists before trying to rename
    if (Schema::hasColumn('journal_lines', 'account_id')) {
        Schema::table('journal_lines', function (Blueprint $table) {
            // Rename account_id to coa_id to match the model
            $table->renameColumn('account_id', 'coa_id');
        });
    } else {
        // Column already renamed or doesn't exist, skip
        // This migration has already been applied or is not needed
    }
}

public function down(): void
{
    // Check if coa_id column exists before trying to rename back
    if (Schema::hasColumn('journal_lines', 'coa_id')) {
        Schema::table('journal_lines', function (Blueprint $table) {
            // Rename back to account_id
            $table->renameColumn('coa_id', 'account_id');
        });
    }
}
```

### 2. Perbaikan Migration Stock Movements
**File:** `database/migrations/2026_04_10_154234_add_keterangan_to_stock_movements_table.php`

```php
public function up(): void
{
    // Check if keterangan column doesn't exist before adding
    if (!Schema::hasColumn('stock_movements', 'keterangan')) {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('ref_id');
        });
    }
}

public function down(): void
{
    // Check if keterangan column exists before dropping
    if (Schema::hasColumn('stock_movements', 'keterangan')) {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
}
```

## Prinsip Perbaikan:

### 1. Defensive Programming
- **Selalu check** apakah kolom exists sebelum add/rename/drop
- **Gunakan `Schema::hasColumn()`** untuk validasi
- **Avoid assumptions** tentang struktur database

### 2. Safe Migration Pattern
```php
// BEFORE (Unsafe)
$table->renameColumn('old_name', 'new_name');

// AFTER (Safe)
if (Schema::hasColumn('table_name', 'old_name')) {
    $table->renameColumn('old_name', 'new_name');
}
```

### 3. Idempotent Migrations
- Migration dapat dijalankan berulang kali tanpa error
- Tidak mengubah struktur jika sudah sesuai
- Rollback yang aman

## Hasil Perbaikan:

### ✅ Migration Berhasil:
```
INFO  Running migrations.

2026_04_09_214439_fix_journal_lines_column_name .... 33.25ms DONE
2026_04_10_000001_update_stock_movements_for_retur  208.62ms DONE
2026_04_10_154234_add_keterangan_to_stock_movements_table  37.38ms DONE
```

### ✅ Database Integrity:
- Tidak ada kolom duplikat
- Tidak ada error saat migration
- Struktur tabel sesuai ekspektasi

## Best Practices untuk Migration:

### 1. Always Check Before Modify
```php
// Check column exists before rename
if (Schema::hasColumn('table', 'old_column')) {
    $table->renameColumn('old_column', 'new_column');
}

// Check column doesn't exist before add
if (!Schema::hasColumn('table', 'new_column')) {
    $table->string('new_column')->nullable();
}

// Check column exists before drop
if (Schema::hasColumn('table', 'column')) {
    $table->dropColumn('column');
}
```

### 2. Handle Edge Cases
```php
// Check table exists
if (Schema::hasTable('table_name')) {
    // Perform operations
}

// Check index exists before drop
if (Schema::hasIndex('table_name', 'index_name')) {
    $table->dropIndex('index_name');
}
```

### 3. Meaningful Migration Names
- `fix_journal_lines_column_name` ✅
- `add_keterangan_to_stock_movements` ✅
- `migration_123` ❌

## Summary:

✅ **MASALAH TERPECAHKAN:**
1. ✅ Migration error karena kolom tidak ada - diperbaiki dengan check
2. ✅ Duplicate column error - diperbaiki dengan validasi
3. ✅ Database migration berjalan lancar
4. ✅ Struktur database tetap konsisten

**LESSON LEARNED:**
- Selalu validasi struktur database sebelum modify
- Gunakan defensive programming dalam migration
- Test migration di environment yang sama dengan production
- Buat migration yang idempotent dan safe