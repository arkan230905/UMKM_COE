# New Production System - Manual Process Control

## Overview

Sistem produksi telah diperbarui untuk memberikan kontrol penuh kepada user dalam menjalankan setiap proses produksi. Sekarang user harus memulai setiap proses secara manual sesuai urutan yang diinginkan.

## Key Changes

### 1. Production Flow Redesign

**OLD FLOW:**
```
Create Production → Click "Mulai Produksi" → INSTANTLY COMPLETED
- Materials consumed
- Finished goods added immediately
- All journals created at once
- Status: draft → selesai
```

**NEW FLOW:**
```
Create Production → Click "Mulai Produksi" → Manual Process Execution → Complete
- Materials consumed only
- Status: draft → dalam_proses
- User must start each process manually
- Finished goods added only when ALL processes complete
- Status: dalam_proses → selesai
```

### 2. Process Management

- **Process Creation**: Automatically created based on BOM Job BTKL data
- **Manual Start**: Each process must be started by user clicking "Mulai" button
- **Sequential Execution**: Only one process can run at a time
- **User Control**: User decides when to start each process (Perbumbuan, Penggorengan, Pengemasan, etc.)

### 3. Status System

| Status | Description | Actions Available |
|--------|-------------|-------------------|
| `draft` | Production plan created, ready to start | "Mulai Produksi" button |
| `dalam_proses` | Materials consumed, processes ready | "Kelola Proses" button |
| `selesai` | All processes completed, finished goods added | View only |

### 4. Journal Entry Timing

| Journal Type | When Created | Purpose |
|--------------|--------------|---------|
| Material Consumption | When "Mulai Produksi" clicked | Material → WIP |
| Labor & Overhead | When ALL processes completed | BTKL & BOP → WIP |
| Finished Goods | When ALL processes completed | WIP → Finished Goods |

## Files Modified

### Controllers
- `app/Http/Controllers/ProduksiController.php`
  - Modified `mulaiProduksi()` method
  - Added `createMaterialJournals()` method
  - Added `createProductionProcesses()` method
  - Modified `selesaikanProses()` method
  - Added `completeProduction()` method
  - Added `createLaborOverheadJournals()` method

### Models
- `app/Models/ProduksiProses.php`
  - Added new fillable fields: `estimasi_durasi`, `kapasitas_per_jam`, `tarif_per_jam`
  - Added `isBelumDimulai()` method
  - Updated status badge for `belum_dimulai` status

### Views
- `resources/views/transaksi/produksi/proses.blade.php`
  - Updated button logic for `belum_dimulai` status

### Database
- `database/migrations/2026_04_05_000001_add_process_fields_to_produksi_proses_table.php`
  - Added fields for process management

## User Interface Changes

### Production Index Page
- **Draft Status**: Shows "Siap Produksi" badge with green "Mulai Produksi" button
- **In Progress Status**: Shows "Dalam Proses" badge with orange "Kelola Proses" button
- **Completed Status**: Shows "Selesai" badge

### Process Management Page
- **Process List**: Shows all processes with their status
- **Manual Control**: Each process has "Mulai" button when ready
- **Sequential Flow**: Only one process can run at a time
- **Progress Tracking**: Visual progress bar showing completed processes

## Process Status Flow

```
belum_dimulai → sedang_dikerjakan → selesai
     ↓               ↓                ↓
  [Mulai]      [Selesaikan]     [Completed]
```

## Benefits

1. **User Control**: Complete control over when each process starts
2. **Realistic Flow**: Matches actual production workflow
3. **Better Tracking**: Clear visibility of which process is running
4. **Accurate Costing**: Materials consumed upfront, labor/overhead allocated when processes complete
5. **Flexible Scheduling**: User can start processes based on resource availability

## Usage Instructions

### For Users

1. **Create Production Plan**
   - Go to "Transaksi Produksi" → "Tambah Data"
   - Fill in production details
   - Click "Simpan" → Status becomes "Siap Produksi"

2. **Start Production**
   - In production index, click green "Mulai Produksi" button
   - System will:
     - Check material availability
     - Consume materials from inventory
     - Create production processes
     - Change status to "Dalam Proses"

3. **Execute Processes**
   - Click orange "Kelola Proses" button
   - Start each process manually by clicking "Mulai"
   - Only one process can run at a time
   - Click "Selesaikan" when process is done

4. **Complete Production**
   - When all processes are finished:
     - System automatically adds finished goods to inventory
     - Creates remaining journal entries
     - Changes status to "Selesai"

### For Developers

1. **Run Migration**
   ```bash
   php artisan migrate
   ```

2. **Test New Flow**
   - Access `test_new_production_flow.php` via browser
   - Check production status and process details

3. **Verify Journal Entries**
   - Material journals created when production starts
   - Labor/overhead journals created when production completes
   - Finished goods journals created when production completes

## Testing Scripts

1. **`test_new_production_flow.php`** - Check current production status and process details
2. **`fix_production_issues.php`** - Fix stock and journal issues (from previous tasks)
3. **`check_production_status.php`** - Diagnostic tool for production status

## Important Notes

- **Material Consumption**: Happens immediately when "Mulai Produksi" is clicked
- **Process Control**: User has full control over process timing
- **Inventory Impact**: Finished goods only added when ALL processes complete
- **Journal Accuracy**: Journals created at appropriate stages of production
- **Stock Validation**: System checks material availability before starting production

This new system provides the manual process control requested while maintaining accurate cost accounting and inventory management.