# Penggajian Fix: Data dari Kualifikasi dan Presensi

## User Requirement
- **Tunjangan, asuransi, dan gaji pokok/tarif per jam** → ambil dari **kualifikasi (jabatan)**
- **Jumlah jam kerja** → ambil dari **presensi**

## Problem Analysis
The previous system was trying to get data from form input, but the user wants the system to automatically pull data from:
1. **Kualifikasi (Jabatan)** - Employee's job qualification data
2. **Presensi** - Employee's attendance records

## Solution Implemented

### 1. Updated Store Method (`app/Http/Controllers/PenggajianController.php`)

**Key Changes:**
- **STEP 1**: Get data from KUALIFIKASI (JABATAN) - NOT from form
- **STEP 2**: Get jam kerja from PRESENSI - NOT from form  
- **STEP 3**: Only get manual input (bonus, potongan) from form
- **STEP 4**: Calculate totals based on system data

**Data Sources:**
```php
// FROM KUALIFIKASI (JABATAN)
$gajiPokok = $pegawai->jabatanRelasi->gaji_pokok;
$tarifPerJam = $pegawai->jabatanRelasi->tarif_per_jam;
$tunjanganJabatan = $pegawai->jabatanRelasi->tunjangan;
$tunjanganTransport = $pegawai->jabatanRelasi->tunjangan_transport;
$tunjanganKonsumsi = $pegawai->jabatanRelasi->tunjangan_konsumsi;
$asuransi = $pegawai->jabatanRelasi->asuransi;

// FROM PRESENSI
$presensiData = Presensi::where('pegawai_id', $pegawai->id)
    ->whereMonth('tgl_presensi', $month)
    ->whereYear('tgl_presensi', $year)
    ->where('status', 'hadir')
    ->get();

$totalJamKerja = $presensiData->sum('jumlah_jam');
```

### 2. Updated Create Form (`resources/views/transaksi/penggajian/create.blade.php`)

**Key Changes:**
- Removed hidden form fields (data comes from system, not form)
- Made all salary component fields **readonly**
- Added clear labels showing data source:
  - "Dari kualifikasi pegawai" 
  - "Dari data presensi"
- Updated JavaScript to load and display system data

**Form Structure:**
```html
<!-- Data from KUALIFIKASI -->
<input type="text" readonly> 
<small class="text-info">Dari kualifikasi pegawai</small>

<!-- Data from PRESENSI -->
<input type="text" readonly>
<small class="text-info">Dari data presensi</small>
```

### 3. Updated JavaScript Logic

**New Flow:**
1. User selects employee → Load data from kualifikasi API
2. User selects/changes date → Load jam kerja from presensi API  
3. System calculates gaji dasar automatically
4. Only bonus and potongan are manual input
5. Form submission sends minimal data (system calculates the rest)

**Data Loading:**
```javascript
// Load from KUALIFIKASI
fetch(`/pegawai/${pegawaiId}/data`)
  .then(data => {
    // Update display with kualifikasi data
    pegawaiData.tarif = data.tarif;
    pegawaiData.totalTunjangan = data.total_tunjangan;
    pegawaiData.asuransi = data.asuransi;
  });

// Load from PRESENSI  
fetch(`/api/presensi/jam-kerja?pegawai_id=${pegawaiId}&month=${month}&year=${year}`)
  .then(data => {
    // Update jam kerja from presensi
    pegawaiData.jamKerja = data.total_jam;
    // Calculate: Gaji Dasar = Tarif × Jam Kerja
    pegawaiData.gajiDasar = pegawaiData.tarif * pegawaiData.jamKerja;
  });
```

## Data Flow

### For BTKL Employee (Ahmad Suryanto):
1. **Kualifikasi Data**: Tarif per jam, tunjangan, asuransi
2. **Presensi Data**: Total jam kerja bulan ini
3. **Calculation**: Gaji Dasar = Tarif × Jam Kerja
4. **Total**: Gaji Dasar + Tunjangan + Asuransi + Bonus - Potongan

### For BTKTL Employee:
1. **Kualifikasi Data**: Gaji pokok, tunjangan, asuransi  
2. **Presensi Data**: Not needed (fixed salary)
3. **Calculation**: Gaji Dasar = Gaji Pokok
4. **Total**: Gaji Pokok + Tunjangan + Asuransi + Bonus - Potongan

## Expected Results

### Before Fix:
- Form tried to get data from user input
- Data was often 0 or incorrect
- No connection to kualifikasi or presensi

### After Fix:
- **Tarif per jam**: Automatically from kualifikasi (e.g., Rp 18,000)
- **Total jam kerja**: Automatically from presensi (e.g., 160 hours)  
- **Gaji dasar**: Calculated automatically (18,000 × 160 = Rp 2,880,000)
- **Tunjangan**: Automatically from kualifikasi breakdown
- **Asuransi**: Automatically from kualifikasi
- **Total accurate**: All components properly calculated and stored

## Validation

The system now validates:
1. Employee must have kualifikasi (jabatan relation)
2. Presensi data must exist for the selected month
3. All calculations are based on system data, not user input

## Error Handling

- If no kualifikasi: "Pegawai tidak memiliki kualifikasi jabatan"
- If no presensi: Shows 0 jam kerja with warning
- If API fails: Clear error messages to user

## Files Modified

1. `app/Http/Controllers/PenggajianController.php` - Store method completely rewritten
2. `resources/views/transaksi/penggajian/create.blade.php` - Form updated to show system data
3. `check_jabatan_data.php` - Debug script to verify kualifikasi and presensi data

## Testing Instructions

1. **Check Kualifikasi Data**: Run `php check_jabatan_data.php` to verify employee has proper jabatan data
2. **Test Form**: Go to `/transaksi/penggajian/create`, select Ahmad Suryanto, verify all fields populate automatically
3. **Verify Calculation**: Check that gaji dasar = tarif × jam kerja
4. **Test Storage**: Create payroll and verify detail view shows correct values

The system now properly integrates with kualifikasi (jabatan) and presensi data as requested, ensuring accurate and automatic salary calculations.