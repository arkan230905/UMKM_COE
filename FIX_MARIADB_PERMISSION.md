# Fix MariaDB Permission Error

## Error
```
SQLSTATE[HY000] [1130] Host 'localhost' is not allowed to connect to this MariaDB server
```

## Penyebab
User `root` tidak memiliki permission untuk connect dari `localhost` atau `127.0.0.1`.

## Solusi

### Opsi 1: Otomatis (Recommended)

1. **Klik kanan** file `fix_mariadb_auto.bat`
2. Pilih **"Run as administrator"**
3. Script akan otomatis:
   - Stop MySQL
   - Edit my.ini
   - Fix permission
   - Restore my.ini
   - Restart MySQL
   - Test connection

### Opsi 2: Manual

#### Step 1: Stop MySQL
1. Buka **XAMPP Control Panel**
2. Klik **Stop** pada MySQL/MariaDB

#### Step 2: Edit Konfigurasi
1. Buka file: `C:\xampp\mysql\bin\my.ini`
2. Cari bagian `[mysqld]`
3. Tambahkan baris baru:
   ```
   skip-grant-tables
   ```
4. **Save** file

#### Step 3: Start MySQL
1. Kembali ke **XAMPP Control Panel**
2. Klik **Start** pada MySQL/MariaDB

#### Step 4: Fix Permission
Jalankan command:
```bash
php fix_mariadb_permission.php
```

#### Step 5: Restore Konfigurasi
1. Buka lagi file: `C:\xampp\mysql\bin\my.ini`
2. **HAPUS** baris: `skip-grant-tables`
3. **Save** file

#### Step 6: Restart MySQL
1. Kembali ke **XAMPP Control Panel**
2. Klik **Stop** pada MySQL/MariaDB
3. Klik **Start** lagi

#### Step 7: Test Connection
```bash
php artisan config:clear
php artisan db:show
```

## Jika Masih Error

### Cek User Permission di Database
```bash
C:\xampp\mysql\bin\mysql.exe -h 127.0.0.1 -u root -e "SELECT user, host FROM mysql.user WHERE user='root';"
```

Seharusnya muncul:
```
+------+-----------+
| user | host      |
+------+-----------+
| root | localhost |
| root | 127.0.0.1 |
| root | ::1       |
+------+-----------+
```

### Alternatif: Reinstall XAMPP
Jika semua cara di atas gagal, backup database dulu lalu reinstall XAMPP.

## Backup Database (Sebelum Reinstall)
```bash
C:\xampp\mysql\bin\mysqldump.exe -h 127.0.0.1 -u root eadt_umkm > backup_eadt_umkm.sql
```

## Restore Database (Setelah Reinstall)
```bash
C:\xampp\mysql\bin\mysql.exe -u root eadt_umkm < backup_eadt_umkm.sql
```
