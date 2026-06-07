# Quick Start Guide - Production Interface

## ✅ Everything is Ready!

Your production interface has been completely redesigned and is ready to use.

---

## What Changed?

### Before:
- Single list showing all productions mixed together
- Hard to distinguish templates from executions
- Manual process to create new production

### After:
- **Two separate tabs:**
  1. **Data Produk** - Products ready to produce (templates)
  2. **Riwayat Produksi** - Production history (executions)
- **One-click production** - "Mulai Produksi Hari Ini" button
- **Smart stock checking** - Shows green/red badges
- **Clear status indicators** - Draft, Dalam Proses, Selesai

---

## How to Use

### 1️⃣ Navigate to Production Page
```
URL: /transaksi/produksi
```

### 2️⃣ View Data Produk Tab (Default)
- Shows products you've setup for production
- Each product shows:
  - ✅ Green badge = Stock sufficient, can produce
  - ❌ Red badge = Stock insufficient, need to restock
- Hover over red badge to see what's missing

### 3️⃣ Start Production
- Click **"Mulai Produksi Hari Ini"** button
- System creates new production automatically
- Redirects you to "Riwayat Produksi" tab
- New production appears with "Draft" status

### 4️⃣ Execute Production
- In "Riwayat Produksi" tab, find your draft production
- Click **"Mulai"** button
- System validates stock and starts production:
  - ✅ Reduces bahan baku stock
  - ✅ Reduces bahan pendukung stock
  - ✅ Increases finished goods stock
  - ✅ Changes status to "Dalam Proses"

### 5️⃣ Manage Processes
- Click **"Kelola"** button
- Manage individual production steps
- Mark processes as complete
- When all done → Status becomes "Selesai"

---

## Visual Guide

```
┌─────────────────────────────────────────────────────┐
│  📦 Data Produk (5)  |  📜 Riwayat Produksi (23)   │
└─────────────────────────────────────────────────────┘

TAB 1: Data Produk
┌───────────────────────────────────────────────────┐
│ Produk          │ Qty/Hari │ Stock    │ Aksi      │
├───────────────────────────────────────────────────┤
│ Sambal Ijo      │ 100 pcs  │ ✅ Cukup │ 🚀 Mulai  │
│ Sambal Merah    │ 150 pcs  │ ❌ Kurang │ 🚫 Block │
│ Kecap Manis     │ 200 btl  │ ✅ Cukup │ 🚀 Mulai  │
└───────────────────────────────────────────────────┘

TAB 2: Riwayat Produksi
┌──────────────────────────────────────────────────────┐
│ Tanggal  │ Produk      │ Qty    │ Status   │ Aksi   │
├──────────────────────────────────────────────────────┤
│ 08/06/26 │ Sambal Ijo  │ 100    │ Draft    │ Mulai  │
│ 07/06/26 │ Kecap Manis │ 200    │ Proses   │ Kelola │
│ 06/06/26 │ Sambal Merah│ 150    │ Selesai  │ Detail │
└──────────────────────────────────────────────────────┘
```

---

## Stock Status Explained

### ✅ Green "Cukup" Badge
- All materials available in sufficient quantity
- Can start production immediately
- "Mulai Produksi" button is ENABLED

### ❌ Red "Kurang" Badge
- One or more materials insufficient
- Cannot start production
- "Stok Kurang" button is DISABLED
- Hover to see which materials are short

**Example Tooltip:**
```
Stok Kurang:
- Cabe Merah: butuh 5 kg, tersedia 2 kg
- Air: butuh 10 liter, tersedia 8 liter
```

---

## Production Status Flow

```
1. DRAFT (Siap Produksi) [Blue Badge]
   ↓ Click "Mulai" button
   ↓ Stock validation passes
   ↓ Stock reduced automatically
   
2. DALAM PROSES (In Progress) [Primary Badge]
   ↓ Click "Kelola" button
   ↓ Complete all processes
   ↓ Mark as done
   
3. SELESAI (Completed) [Green Badge]
   ✅ Production finished
   ✅ Journal entries created
   ✅ Stock updated
```

---

## Tips & Tricks

### 💡 Tip 1: Check Stock Before Clicking
Look at the stock badge before clicking "Mulai Produksi":
- Green = Ready to go!
- Red = Need to restock first

### 💡 Tip 2: Use Filters in Riwayat Tab
Filter by:
- Date range (start & end date)
- Product name
- Status (draft, dalam_proses, selesai)

### 💡 Tip 3: Quickly Find Your Production
- Recent productions appear at the top
- Badge counters show total items in each tab
- Use search/filter to narrow down results

### 💡 Tip 4: Track Progress
Production status shows progress:
- "2/5 proses" = 2 out of 5 processes completed
- Current process name displayed

### 💡 Tip 5: Stock Movements
All stock changes are tracked:
- Check `/laporan/stok` for detailed stock history
- See which production used which materials

---

## Common Questions

### Q: What happens when I click "Mulai Produksi Hari Ini"?
**A:** System creates a new production record with the same specifications as your last production for that product. It's like copying a template. Status starts as "Draft" - you still need to click "Mulai" to actually start production.

### Q: When does stock actually reduce?
**A:** Stock reduces when you click the **"Mulai"** button on a draft production, NOT when you click "Mulai Produksi Hari Ini". The first button creates a plan, the second button executes it.

### Q: Can I edit the qty before starting?
**A:** Currently no, but the qty is copied from your last production. If you need different qty, you can create a new production manually via "Tambah Data Produksi Produk" button.

### Q: What if stock runs out after I create the production?
**A:** No problem! The system validates stock again when you click "Mulai". If stock is insufficient at that time, it will show an error and NOT reduce stock.

### Q: Can I delete a draft production?
**A:** Yes, draft productions can be deleted without affecting stock since stock hasn't been reduced yet.

### Q: What's the difference between "Data Produk" and "Riwayat Produksi"?
**A:** 
- **Data Produk** = Master templates (each product appears once)
- **Riwayat Produksi** = Execution history (each production execution appears)

Think of it like recipes vs. cooking history:
- Data Produk = Recipe book (what CAN be made)
- Riwayat = Cooking log (what WAS made)

---

## Keyboard Shortcuts

- `Tab` = Switch between tabs
- `Ctrl + F` = Search/filter
- `Enter` = Submit filter form
- `Esc` = Close dialogs

---

## Troubleshooting

### Problem: "Route not found" error
**Solution:** Run this command:
```bash
php artisan route:clear
```

### Problem: Old interface still showing
**Solution:** Clear view cache:
```bash
php artisan view:clear
```
Then hard refresh browser (Ctrl+Shift+R)

### Problem: Stock badge shows wrong status
**Solution:** Refresh the page to reload stock data from database

### Problem: "Mulai" button doesn't work
**Solution:** 
1. Check production status is "Draft"
2. Check stock is sufficient
3. Check browser console for errors

### Problem: Production not appearing in list
**Solution:**
1. Check you're looking at the right tab
2. Check filters are not hiding it
3. Check pagination (might be on another page)

---

## Getting Help

If you encounter issues:

1. **Check Documentation:**
   - `.kiro/PRODUCTION_INTERFACE_REDESIGN.md` - Implementation details
   - `.kiro/TESTING_GUIDE_PRODUCTION_INTERFACE.md` - Testing instructions
   - `.kiro/COMPLETION_SUMMARY.md` - Feature overview

2. **Check Database:**
   - Verify data exists in `produksis` table
   - Check `user_id` matches your login
   - Verify stock in `bahan_bakus` and `bahan_pendukungs`

3. **Check Logs:**
   - Laravel log: `storage/logs/laravel.log`
   - Browser console: F12 → Console tab

4. **Clear Caches:**
   ```bash
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   ```

---

## What's Next?

After you're comfortable with the basic flow:

1. **Explore Filters** - Filter production history by date/product/status
2. **Monitor Stock** - Watch stock levels in Data Produk tab
3. **Manage Processes** - Use the Kelola feature to track production steps
4. **View Reports** - Check production costs and history
5. **Plan Ahead** - Use stock forecasts to know when to restock

---

## Summary

### ✅ Key Benefits:
- **Faster** - One-click production creation
- **Safer** - Automatic stock validation
- **Clearer** - Separate templates from executions
- **Smarter** - Visual stock indicators

### 🎯 Remember:
1. "Data Produk" = Products you CAN produce
2. "Riwayat Produksi" = Productions you DID execute
3. Green badge = Go ahead
4. Red badge = Restock first
5. Draft → Dalam Proses → Selesai

---

## Ready to Go! 🚀

Everything is set up and ready. Just navigate to:

```
/transaksi/produksi
```

And start producing! If you have any questions, check the documentation files in the `.kiro/` folder.

**Happy producing! 🏭**
