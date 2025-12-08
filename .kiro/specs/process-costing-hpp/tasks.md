# Implementation Plan

## Phase 1: Database & Models

- [x] 1. Create database migrations for Process Costing tables
  - [x] 1.1 Create migration for `proses_produksis` table
    - Fields: id, kode_proses, nama_proses, deskripsi, tarif_btkl, satuan_btkl, is_active
    - _Requirements: 1.1_
  - [x] 1.2 Create migration for `komponen_bops` table
    - Fields: id, kode_komponen, nama_komponen, satuan, tarif_per_satuan, is_active
    - _Requirements: 2.1_
  - [x] 1.3 Create migration for `proses_bops` table (default BOP per process)
    - Fields: id, proses_produksi_id, komponen_bop_id, kuantitas_default
    - _Requirements: 2.2_
  - [x] 1.4 Create migration for `bom_proses` table
    - Fields: id, bom_id, proses_produksi_id, urutan, durasi, satuan_durasi, biaya_btkl, biaya_bop, catatan
    - _Requirements: 3.2_
  - [x] 1.5 Create migration for `bom_proses_bops` table
    - Fields: id, bom_proses_id, komponen_bop_id, kuantitas, tarif, total_biaya
    - _Requirements: 3.2_
  - [x] 1.6 Create migration to add total_bbb and total_hpp columns to boms table
    - _Requirements: 3.4_

- [x] 2. Create Eloquent Models
  - [x] 2.1 Create ProsesProduksi model with relationships and validation
    - Relationships: hasMany(ProsesBop), hasMany(BomProses)
    - _Requirements: 1.1, 1.3, 1.4_
  - [ ] 2.2 Write property test for process deletion constraint
    - **Property 5: Process Deletion Constraint**
    - **Validates: Requirements 1.4**
  - [x] 2.3 Create KomponenBop model with relationships
    - Relationships: hasMany(ProsesBop), hasMany(BomProsesBop)
    - _Requirements: 2.1_
  - [x] 2.4 Create ProsesBop model (pivot with extra fields)
    - Relationships: belongsTo(ProsesProduksi), belongsTo(KomponenBop)
    - _Requirements: 2.2_
  - [x] 2.5 Create BomProses model with cost calculation
    - Relationships: belongsTo(Bom), belongsTo(ProsesProduksi), hasMany(BomProsesBop)
    - Auto-calculate biaya_btkl and biaya_bop on save
    - _Requirements: 3.2, 3.3_
  - [ ] 2.6 Write property test for BTKL calculation
    - **Property 2: BTKL Calculation per Process**
    - **Validates: Requirements 3.3**
  - [x] 2.7 Create BomProsesBop model
    - Relationships: belongsTo(BomProses), belongsTo(KomponenBop)
    - Auto-calculate total_biaya on save
    - _Requirements: 3.2_
  - [ ] 2.8 Write property test for BOP calculation
    - **Property 3: BOP Calculation per Process**
    - **Validates: Requirements 2.2, 3.3**

- [ ] 3. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Phase 2: Update BOM Model & Service

- [x] 4. Update existing Bom model for Process Costing
  - [x] 4.1 Add relationships to BomProses in Bom model
    - hasMany(BomProses)
    - _Requirements: 3.2_
  - [x] 4.2 Update hitungTotalBiaya() method to use process-based calculation
    - Remove percentage-based calculation (60% BTKL, 40% BOP)
    - Calculate from actual process costs
    - _Requirements: 3.4_
  - [ ] 4.3 Write property test for HPP formula
    - **Property 1: HPP Calculation Formula**
    - **Validates: Requirements 3.4**
  - [x] 4.4 Update updateProductPrice() method
    - Use new HPP calculation
    - _Requirements: 3.5_
  - [ ] 4.5 Write property test for product HPP update
    - **Property 8: Product HPP Update**
    - **Validates: Requirements 3.5**

- [ ] 5. Update BomCalculationService
  - [ ] 5.1 Add calculateTotalBBB() method
    - Sum of all bom_details total_harga
    - _Requirements: 3.1_
  - [ ] 5.2 Write property test for BBB calculation
    - **Property 7: BBB Total Calculation**
    - **Validates: Requirements 3.1**
  - [ ] 5.3 Add calculateTotalBTKL() method
    - Sum of all bom_proses biaya_btkl
    - _Requirements: 3.3_
  - [ ] 5.4 Add calculateTotalBOP() method
    - Sum of all bom_proses biaya_bop
    - _Requirements: 3.3_
  - [ ] 5.5 Add calculateHPP() method returning breakdown
    - Return: total_bbb, total_btkl, total_bop, hpp, breakdown
    - _Requirements: 3.4_
  - [x] 5.6 Add recalculateOnRateChange() method for cascade updates
    - Recalculate all affected BOMs when rates change
    - _Requirements: 4.1, 4.2, 4.3_
  - [ ] 5.7 Write property test for cascade recalculation
    - **Property 4: Cascade Recalculation on Rate Change**
    - **Validates: Requirements 4.1, 4.2, 4.3**

- [ ] 6. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Phase 3: Controllers & Routes

- [x] 7. Create ProsesProduksiController
  - [x] 7.1 Implement index, create, store methods
    - CRUD for production processes
    - _Requirements: 1.1, 1.2_
  - [x] 7.2 Implement edit, update, destroy methods
    - Include deletion constraint check
    - _Requirements: 1.3, 1.4_
  - [x] 7.3 Add routes for ProsesProduksi
    - Resource routes under master-data
    - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [x] 8. Create KomponenBopController
  - [x] 8.1 Implement CRUD methods
    - _Requirements: 2.1, 2.3, 2.4_
  - [x] 8.2 Add routes for KomponenBop
    - Resource routes under master-data
    - _Requirements: 2.1_

- [x] 9. Update BomController for Process Costing
  - [x] 9.1 Update create() to load proses_produksis and komponen_bops
    - _Requirements: 3.1, 3.2_
  - [x] 9.2 Update store() to save BOM with processes
    - Save bom_proses and bom_proses_bops
    - Calculate HPP using new method
    - _Requirements: 3.2, 3.3, 3.4, 3.5_
  - [x] 9.3 Update edit() and update() for process editing
    - _Requirements: 3.2_
  - [x] 9.4 Update show() to display process breakdown
    - _Requirements: 3.6_

- [ ] 10. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Phase 4: Views & UI

- [x] 11. Create Master Proses Produksi Views
  - [x] 11.1 Create index.blade.php for process list
    - Display processes with BTKL rates
    - _Requirements: 1.2_
  - [x] 11.2 Create create.blade.php and edit.blade.php forms
    - Form for process name, BTKL rate, satuan
    - _Requirements: 1.1, 1.3_
  - [ ] 11.3 Add default BOP assignment UI
    - Assign komponen_bop to process with default quantity
    - _Requirements: 2.2_

- [x] 12. Create Master Komponen BOP Views
  - [x] 12.1 Create index.blade.php for BOP component list
    - _Requirements: 2.4_
  - [x] 12.2 Create create.blade.php and edit.blade.php forms
    - _Requirements: 2.1, 2.3_

- [x] 13. Update BOM Views for Process Costing
  - [x] 13.1 Update create.blade.php to add process section
    - Section for adding production processes with duration
    - Auto-load default BOP when process selected
    - _Requirements: 3.1, 3.2_
  - [x] 13.2 Add dynamic BOP component editing per process
    - Allow adjusting BOP quantities per process
    - _Requirements: 3.2_
  - [x] 13.3 Update cost summary display
    - Show BBB, BTKL per process, BOP per process, Total HPP
    - _Requirements: 3.4, 3.6_
  - [x] 13.4 Update edit.blade.php with same features
    - _Requirements: 3.2_
  - [x] 13.5 Update show.blade.php with detailed breakdown
    - _Requirements: 3.6_
  - [x] 13.6 Update print.blade.php with process costing format
    - _Requirements: 4.5_

- [x] 14. Add sidebar menu items
  - Add "Proses Produksi" and "Komponen BOP" to Master Data menu
  - _Requirements: 1.2, 2.4_

- [ ] 15. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Phase 5: Reports & Integration

- [ ] 16. Create HPP Report
  - [ ] 16.1 Create LaporanHppController with index and export methods
    - _Requirements: 5.1, 5.2, 5.4_
  - [ ] 16.2 Create report view with filters (date range, product)
    - _Requirements: 5.1, 5.2_
  - [ ] 16.3 Create detail view for drill-down
    - Show process-by-process breakdown
    - _Requirements: 5.3_
  - [ ] 16.4 Implement PDF/Excel export
    - _Requirements: 5.4_

- [ ] 17. Production Order Integration (Optional)
  - [ ] 17.1 Update production order to use BOM as standard cost
    - _Requirements: 6.1_
  - [ ] 17.2 Add actual cost recording fields
    - _Requirements: 6.2, 6.3_
  - [ ] 17.3 Implement variance calculation
    - _Requirements: 6.4, 6.5_
  - [ ] 17.4 Write property test for variance calculation
    - **Property 6: Variance Calculation**
    - **Validates: Requirements 6.4, 6.5**

- [ ] 18. Final Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Phase 6: Data Migration & Cleanup

- [x] 19. Migrate existing BOM data
  - [x] 19.1 Create seeder for default production processes
    - Create common processes: Persiapan, Pengolahan, Pengemasan
    - _Requirements: 1.1_
  - [x] 19.2 Create seeder for default BOP components
    - Create common components: Listrik, Gas, Air, Penyusutan
    - _Requirements: 2.1_
  - [ ] 19.3 Create migration script for existing BOMs
    - Convert percentage-based BTKL/BOP to process-based
    - _Requirements: 3.4_

- [ ] 20. Final testing and documentation
  - [ ] 20.1 Run all property-based tests
    - Verify all 8 properties pass
  - [ ] 20.2 Manual testing of complete flow
    - Create process → Create BOP → Create BOM → Verify HPP
  - [ ] 20.3 Update user documentation
    - Document new Process Costing workflow
