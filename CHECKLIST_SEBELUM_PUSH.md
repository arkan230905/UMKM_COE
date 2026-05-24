# ✅ Checklist Sebelum Push ke Repository

## Untuk Developer yang Melakukan Perubahan COA

Sebelum melakukan `git push`, pastikan hal-hal berikut:

---

## 1. ✅ Verifikasi Perubahan File

### File yang Diubah:
- [ ] `database/seeders/DatabaseSeeder.php` - Sudah mengganti ke `AyamKetumbarCoaSeeder::class`

### File yang Harus Ada:
- [ ] `database/seeders/AyamKetumbarCoaSeeder.php` - File seeder COA Ayam Ketumbar
- [ ] `PANDUAN_SETUP_COA_AYAM_KETUMBAR.md` - Panduan lengkap untuk tim
- [ ] `QUICK_SETUP.md` - Panduan cepat
- [ ] `CHECKLIST_SEBELUM_PUSH.md` - File ini

---

## 2. ✅ Test di Local

Pastikan sudah test di local environment:

```bash
# Test reset database
php artisan migrate:fresh --seed
```

- [ ] Migrasi berjalan tanpa error
- [ ] Seeder berjalan tanpa error
- [ ] COA Ayam Ketumbar berhasil dibuat
- [ ] Aplikasi bisa diakses dan berfungsi normal

---

## 3. ✅ Verifikasi COA di Database

Cek database Anda, pastikan:

- [ ] Tabel `coas` terisi dengan akun-akun Ayam Ketumbar
- [ ] Ada akun untuk Bahan Baku Ayam (kode 1141-1144)
- [ ] Ada akun untuk Bahan Pendukung (kode 1150-1157)
- [ ] Ada akun untuk Produk Jadi (kode 1161-1162)
- [ ] Ada akun Biaya Produksi (BBB, BTKL, BOP)

Query untuk cek:
```sql
SELECT kode_akun, nama_akun, tipe_akun 
FROM coas 
WHERE user_id = 1 
ORDER BY kode_akun;
```

---

## 4. ✅ Git Commit

### Commit Message yang Baik:
```bash
git add .
git commit -m "feat: Ganti COA dari Jasuke ke Ayam Ketumbar

- Update DatabaseSeeder untuk menggunakan AyamKetumbarCoaSeeder
- Tambah dokumentasi setup untuk tim
- COA disesuaikan untuk usaha produksi ayam goreng
- Includes: BBB ayam, bahan pendukung, produk jadi, biaya produksi"
```

---

## 5. ✅ Komunikasi dengan Tim

Sebelum push, pastikan:

- [ ] Informasikan tim di grup chat/Slack/Discord
- [ ] Jelaskan bahwa ada perubahan besar di database
- [ ] Berikan link ke dokumentasi (PANDUAN_SETUP_COA_AYAM_KETUMBAR.md)
- [ ] Ingatkan untuk backup data lokal mereka

### Template Pesan untuk Tim:

```
🚨 PERHATIAN TIM! 🚨

Saya baru saja push perubahan BESAR ke repository:

📌 Perubahan: COA diganti dari Jasuke ke Ayam Ketumbar

⚠️ WAJIB DILAKUKAN setelah git pull:
1. Backup database lokal Anda (jika ada data penting)
2. Jalankan: php artisan migrate:fresh --seed
3. Database akan di-reset dan diisi dengan COA baru

📖 Panduan lengkap: Baca file PANDUAN_SETUP_COA_AYAM_KETUMBAR.md
🚀 Panduan cepat: Baca file QUICK_SETUP.md

❓ Ada pertanyaan? Silakan tanya di grup ini.
```

---

## 6. ✅ Push ke Repository

```bash
# Push ke branch utama (atau branch development)
git push origin main
```

Atau jika menggunakan branch lain:
```bash
git push origin nama-branch-anda
```

---

## 7. ✅ Setelah Push

- [ ] Cek di GitHub/GitLab bahwa commit berhasil
- [ ] Pastikan file dokumentasi terlihat di repository
- [ ] Monitor grup chat untuk pertanyaan dari tim
- [ ] Siap membantu jika ada yang mengalami masalah

---

## 🆘 Jika Ada Masalah

### Rollback Jika Diperlukan:
```bash
# Lihat commit history
git log --oneline

# Rollback ke commit sebelumnya
git revert HEAD

# Atau reset (hati-hati!)
git reset --hard HEAD~1
git push -f origin main
```

---

## 📝 Catatan Penting

- ✅ Perubahan ini **TIDAK** mempengaruhi production (jika ada)
- ✅ Setiap developer harus setup ulang database lokal mereka
- ✅ Data production (jika ada) **TIDAK** akan terpengaruh
- ⚠️ Jangan jalankan `migrate:fresh` di production!

---

**Dibuat:** 25 Mei 2026
**Oleh:** [Nama Anda]
