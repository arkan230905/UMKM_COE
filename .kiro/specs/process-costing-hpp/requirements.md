# Requirements Document

## Introduction

Sistem perhitungan Harga Pokok Produksi (HPP) menggunakan metode Process Costing yang sesuai dengan standar akuntansi biaya. Sistem ini menghitung HPP berdasarkan tiga komponen utama: Biaya Bahan Baku (BBB), Biaya Tenaga Kerja Langsung (BTKL), dan Biaya Overhead Pabrik (BOP). Setiap proses produksi (seperti menggoreng, membumbui, mengemas) memiliki perhitungan biaya tersendiri yang kemudian diakumulasi dalam Bill of Materials (BOM).

## Glossary

- **HPP (Harga Pokok Produksi)**: Total biaya yang dikeluarkan untuk memproduksi satu unit produk, terdiri dari BBB + BTKL + BOP
- **BBB (Biaya Bahan Baku)**: Biaya bahan baku langsung yang digunakan dalam produksi
- **BTKL (Biaya Tenaga Kerja Langsung)**: Biaya tenaga kerja yang terlibat langsung dalam proses produksi
- **BOP (Biaya Overhead Pabrik)**: Biaya tidak langsung seperti listrik, gas, penyusutan mesin, dll
- **Process Costing**: Metode perhitungan biaya produksi berdasarkan proses/tahapan produksi
- **BOM (Bill of Materials)**: Daftar komponen dan biaya yang diperlukan untuk memproduksi satu produk
- **Proses Produksi**: Tahapan dalam pembuatan produk (contoh: menggoreng, membumbui, mengemas)
- **Tarif BTKL**: Biaya tenaga kerja per satuan waktu atau per proses
- **Tarif BOP**: Biaya overhead per satuan waktu atau per proses

## Requirements

### Requirement 1: Master Data Proses Produksi

**User Story:** As a production manager, I want to define production processes with their associated labor and overhead costs, so that I can accurately calculate production costs per process.

#### Acceptance Criteria

1. WHEN a user creates a new production process THEN the System SHALL store the process name, description, default BTKL rate, and default BOP rate
2. WHEN a user views the production process list THEN the System SHALL display all processes with their BTKL and BOP rates
3. WHEN a user edits a production process THEN the System SHALL update the process details and recalculate affected BOMs
4. WHEN a user deletes a production process that is used in a BOM THEN the System SHALL prevent deletion and display an error message
5. IF a required field is empty THEN the System SHALL display a validation error and prevent saving

### Requirement 2: Master Data Komponen BOP

**User Story:** As an accountant, I want to define overhead cost components (electricity, gas, depreciation, etc.), so that I can track and allocate overhead costs accurately.

#### Acceptance Criteria

1. WHEN a user creates a new BOP component THEN the System SHALL store the component name, unit of measure, and rate per unit
2. WHEN a user assigns BOP components to a production process THEN the System SHALL calculate the total BOP for that process
3. WHEN a user updates a BOP component rate THEN the System SHALL recalculate all affected production processes and BOMs
4. WHEN viewing BOP components THEN the System SHALL display the component name, unit, rate, and associated processes

### Requirement 3: BOM dengan Struktur Process Costing

**User Story:** As a production manager, I want to create BOMs that include raw materials, production processes with BTKL and BOP, so that I can calculate accurate production costs.

#### Acceptance Criteria

1. WHEN a user creates a BOM THEN the System SHALL allow adding raw materials (BBB) with quantity and unit price
2. WHEN a user adds a production process to BOM THEN the System SHALL include the process's BTKL and BOP costs
3. WHEN a user specifies process duration or quantity THEN the System SHALL calculate BTKL as (duration × BTKL rate) and BOP as (duration × BOP rate)
4. WHEN all components are added THEN the System SHALL calculate total HPP as (Total BBB + Total BTKL + Total BOP)
5. WHEN a user saves the BOM THEN the System SHALL store all components and update the product's HPP
6. WHEN viewing a BOM THEN the System SHALL display itemized breakdown of BBB, BTKL per process, and BOP per process

### Requirement 4: Perhitungan HPP Otomatis

**User Story:** As an accountant, I want the system to automatically calculate HPP based on BOM components, so that I can ensure accurate product costing.

#### Acceptance Criteria

1. WHEN raw material prices change THEN the System SHALL recalculate BBB and update HPP
2. WHEN BTKL rates change THEN the System SHALL recalculate BTKL component and update HPP
3. WHEN BOP rates change THEN the System SHALL recalculate BOP component and update HPP
4. WHEN viewing product details THEN the System SHALL display HPP breakdown (BBB, BTKL, BOP, Total)
5. WHEN exporting BOM data THEN the System SHALL include complete cost breakdown

### Requirement 5: Laporan Harga Pokok Produksi

**User Story:** As a manager, I want to view HPP reports with detailed cost breakdown, so that I can analyze production costs and make informed decisions.

#### Acceptance Criteria

1. WHEN a user generates HPP report THEN the System SHALL display products with BBB, BTKL, BOP, and total HPP
2. WHEN a user filters by date range THEN the System SHALL show HPP calculations for that period
3. WHEN a user drills down on a product THEN the System SHALL show detailed process-by-process cost breakdown
4. WHEN a user exports the report THEN the System SHALL generate PDF or Excel with complete breakdown

### Requirement 6: Integrasi dengan Produksi

**User Story:** As a production operator, I want to record actual production with real costs, so that I can compare actual vs standard costs.

#### Acceptance Criteria

1. WHEN a production order is created THEN the System SHALL use BOM as the standard cost basis
2. WHEN actual materials are used THEN the System SHALL record actual BBB
3. WHEN actual labor hours are recorded THEN the System SHALL calculate actual BTKL
4. WHEN production is completed THEN the System SHALL compare actual HPP vs standard HPP
5. WHEN variance exists THEN the System SHALL display the variance amount and percentage
