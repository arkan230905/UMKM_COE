# BPMN - Proses Produksi

## Diagram BPMN Proses Produksi

```mermaid
graph TB
    Start([Mulai Produksi]) --> Input[Input Data Produksi<br/>- Pilih Produk<br/>- Tanggal<br/>- Qty Produksi]
    
    Input --> ValidateBOM{Produk Punya<br/>BOM & Detail?}
    ValidateBOM -->|Tidak| ErrorBOM[Error: BOM Belum Ada<br/>Harus Buat BOM Dulu]
    ErrorBOM --> End1([Selesai])
    
    ValidateBOM -->|Ya| GetBOM[Ambil Data BOM<br/>& Detail Bahan Baku]
    
    GetBOM --> CheckStock{Cek Stok<br/>Semua Bahan<br/>Cukup?}
    
    CheckStock -->|Tidak| ErrorStock[Error: Stok Tidak Cukup<br/>Tampilkan Bahan yang Kurang]
    ErrorStock --> End2([Selesai])
    
    CheckStock -->|Ya| StartTrans[Mulai Database<br/>Transaction]
    
    StartTrans --> CreateProduksi[Buat Record Produksi<br/>- produk_id<br/>- tanggal<br/>- qty_produksi]
    
    CreateProduksi --> LoopBahan[Loop Setiap Bahan<br/>di BOM]
    
    LoopBahan --> ConvertUnit[Konversi Satuan<br/>Resep ke Satuan Base]
    
    ConvertUnit --> ConsumeFIFO[Consume Stock FIFO<br/>- Kurangi StockLayer<br/>- Hitung FIFO Cost]
    
    ConsumeFIFO --> UpdateStokBahan[Update Stok<br/>Bahan Baku Master]
    
    UpdateStokBahan --> CreateDetail[Buat ProduksiDetail<br/>- bahan_baku_id<br/>- qty_resep<br/>- qty_konversi<br/>- harga_satuan<br/>- subtotal]
    
    CreateDetail --> NextBahan{Ada Bahan<br/>Lagi?}
    NextBahan -->|Ya| LoopBahan
    
    NextBahan -->|Tidak| HitungBiaya[Hitung Total Biaya<br/>- Total Bahan<br/>- BTKL 20%<br/>- BOP 10%<br/>- Total Biaya]
    
    HitungBiaya --> UpdateProduksi[Update Record Produksi<br/>dengan Total Biaya]
    
    UpdateProduksi --> HitungUnitCost[Hitung Unit Cost<br/>= Total Biaya / Qty]
    
    HitungUnitCost --> AddStockLayer[Tambah StockLayer<br/>Produk Jadi<br/>- type: product<br/>- unit_cost]
    
    AddStockLayer --> UpdateStokProduk[Update Stok<br/>Produk Master<br/>+= qty_produksi]
    
    UpdateStokProduk --> JurnalMaterial[Posting Jurnal 1<br/>Dr. WIP 1105<br/>Cr. Persediaan BB 1104<br/>Nilai: FIFO Cost]
    
    JurnalMaterial --> JurnalBTKLBOP[Posting Jurnal 2<br/>Dr. WIP 1105<br/>Cr. Hutang Gaji 2103<br/>Cr. Hutang BOP 2104]
    
    JurnalBTKLBOP --> JurnalFinish[Posting Jurnal 3<br/>Dr. Persediaan BJ 1107<br/>Cr. WIP 1105<br/>Nilai: Total Biaya]
    
    JurnalFinish --> Commit[Commit Transaction]
    
    Commit --> Success[Redirect ke Detail<br/>Produksi dengan<br/>Pesan Sukses]
    
    Success --> End3([Selesai])
    
    style Start fill:#90EE90
    style End1 fill:#FFB6C1
    style End2 fill:#FFB6C1
    style End3 fill:#90EE90
    style ErrorBOM fill:#FF6B6B
    style ErrorStock fill:#FF6B6B
    style Success fill:#4CAF50
    style ValidateBOM fill:#FFD700
    style CheckStock fill:#FFD700
    style NextBahan fill:#FFD700
```

## Diagram BPMN Proses Hapus Produksi

```mermaid
graph TB
    Start([Mulai Hapus]) --> FindProduksi[Cari Data Produksi<br/>by ID]
    
    FindProduksi --> StartTrans[Mulai Database<br/>Transaction]
    
    StartTrans --> DeleteJurnal1[Hapus Jurnal<br/>production_material]
    
    DeleteJurnal1 --> DeleteJurnal2[Hapus Jurnal<br/>production_labor_overhead]
    
    DeleteJurnal2 --> DeleteJurnal3[Hapus Jurnal<br/>production_finish]
    
    DeleteJurnal3 --> DeleteDetails[Hapus Semua<br/>ProduksiDetail]
    
    DeleteDetails --> DeleteHeader[Hapus Header<br/>Produksi]
    
    DeleteHeader --> Commit[Commit Transaction]
    
    Commit --> Redirect[Redirect ke Index<br/>dengan Pesan Sukses]
    
    Redirect --> End([Selesai])
    
    style Start fill:#90EE90
    style End fill:#90EE90
    style Redirect fill:#4CAF50
```

## Diagram BPMN Complete Produksi

```mermaid
graph TB
    Start([Mulai Complete]) --> FindProduksi[Cari Data Produksi<br/>by ID]
    
    FindProduksi --> CheckStatus{Status Sudah<br/>Completed?}
    
    CheckStatus -->|Ya| InfoMsg[Redirect dengan<br/>Info: Sudah Selesai]
    InfoMsg --> End1([Selesai])
    
    CheckStatus -->|Tidak| UpdateStatus[Update Status<br/>= 'completed']
    
    UpdateStatus --> SuccessMsg[Redirect dengan<br/>Pesan Sukses]
    
    SuccessMsg --> End2([Selesai])
    
    style Start fill:#90EE90
    style End1 fill:#87CEEB
    style End2 fill:#90EE90
    style CheckStatus fill:#FFD700
    style SuccessMsg fill:#4CAF50
```

## Relasi Database Produksi

```mermaid
erDiagram
    PRODUKSI ||--|| PRODUK : "belongs to"
    PRODUKSI ||--o{ PRODUKSI_DETAIL : "has many"
    PRODUKSI_DETAIL ||--|| BAHAN_BAKU : "belongs to"
    PRODUK ||--o{ BOM : "has many"
    BOM ||--|| BAHAN_BAKU : "belongs to"
    PRODUK ||--o{ STOCK_LAYER : "has many"
    BAHAN_BAKU ||--o{ STOCK_LAYER : "has many"
    PRODUKSI ||--o{ JURNAL_UMUM : "generates"
    
    PRODUKSI {
        int id PK
        int produk_id FK
        date tanggal
        decimal qty_produksi
        decimal total_bahan
        decimal total_btkl
        decimal total_bop
        decimal total_biaya
        string status
        text catatan
    }
    
    PRODUKSI_DETAIL {
        int id PK
        int produksi_id FK
        int bahan_baku_id FK
        decimal qty_resep
        string satuan_resep
        decimal qty_konversi
        decimal harga_satuan
        decimal subtotal
    }
    
    PRODUK {
        int id PK
        string nama_produk
        decimal stok
        decimal harga_jual
        decimal btkl_default
        decimal bop_default
    }
    
    BAHAN_BAKU {
        int id PK
        string nama_bahan
        decimal stok
        decimal harga_satuan
        string satuan
    }
    
    BOM {
        int id PK
        int produk_id FK
        int bahan_baku_id FK
        decimal jumlah
        string satuan_resep
    }
    
    STOCK_LAYER {
        int id PK
        string type
        int item_id
        decimal qty
        decimal unit_cost
        string ref_type
        int ref_id
        date tanggal
    }
    
    JURNAL_UMUM {
        int id PK
        int coa_id FK
        date tanggal
        decimal debit
        decimal kredit
        string tipe_referensi
        int referensi
    }
```

## Flow Chart Lengkap dengan Swimlane

```mermaid
graph TB
    subgraph "User Interface"
        UI1[Form Input Produksi]
        UI2[Tampil Error]
        UI3[Tampil Success]
    end
    
    subgraph "Controller Layer"
        C1[ProduksiController::store]
        C2[Validasi Input]
        C3[Validasi BOM]
        C4[Validasi Stok]
        C5[Process Produksi]
        C6[Return Response]
    end
    
    subgraph "Service Layer"
        S1[StockService::consume]
        S2[StockService::addLayer]
        S3[JournalService::post]
        S4[UnitConverter::convert]
    end
    
    subgraph "Model Layer"
        M1[Produksi Model]
        M2[ProduksiDetail Model]
        M3[Produk Model]
        M4[BahanBaku Model]
        M5[StockLayer Model]
        M6[JurnalUmum Model]
    end
    
    subgraph "Database"
        DB1[(produksis)]
        DB2[(produksi_details)]
        DB3[(produks)]
        DB4[(bahan_bakus)]
        DB5[(stock_layers)]
        DB6[(jurnal_umum)]
    end
    
    UI1 --> C1
    C1 --> C2
    C2 --> C3
    C3 -->|Valid| C4
    C3 -->|Invalid| UI2
    C4 -->|Valid| C5
    C4 -->|Invalid| UI2
    
    C5 --> S4
    C5 --> S1
    C5 --> S2
    C5 --> S3
    
    S1 --> M5
    S2 --> M5
    S3 --> M6
    
    C5 --> M1
    C5 --> M2
    C5 --> M3
    C5 --> M4
    
    M1 --> DB1
    M2 --> DB2
    M3 --> DB3
    M4 --> DB4
    M5 --> DB5
    M6 --> DB6
    
    C5 --> C6
    C6 --> UI3
```

## Sequence Diagram Proses Produksi

```mermaid
sequenceDiagram
    participant User
    participant Controller
    participant Validator
    participant BOM
    participant Stock
    participant Journal
    participant Database
    
    User->>Controller: Submit Form Produksi
    Controller->>Validator: Validate Input
    Validator-->>Controller: Valid
    
    Controller->>BOM: Check BOM Exists
    BOM-->>Controller: BOM Found
    
    Controller->>Stock: Check Stock Availability
    Stock-->>Controller: Stock Sufficient
    
    Controller->>Database: Begin Transaction
    
    Controller->>Database: Create Produksi Record
    Database-->>Controller: Produksi Created
    
    loop For Each Bahan in BOM
        Controller->>Stock: Convert Unit
        Stock-->>Controller: Converted Qty
        
        Controller->>Stock: Consume FIFO
        Stock-->>Database: Update StockLayer
        
        Controller->>Database: Update Bahan Stok
        Controller->>Database: Create ProduksiDetail
    end
    
    Controller->>Controller: Calculate Total Cost
    Controller->>Database: Update Produksi Total
    
    Controller->>Stock: Add Product Layer
    Stock-->>Database: Create StockLayer
    
    Controller->>Database: Update Product Stok
    
    Controller->>Journal: Post Material Journal
    Journal-->>Database: Create JurnalUmum
    
    Controller->>Journal: Post BTKL/BOP Journal
    Journal-->>Database: Create JurnalUmum
    
    Controller->>Journal: Post Finish Journal
    Journal-->>Database: Create JurnalUmum
    
    Controller->>Database: Commit Transaction
    Database-->>Controller: Success
    
    Controller-->>User: Redirect to Detail
```

## State Diagram Status Produksi

```mermaid
stateDiagram-v2
    [*] --> Draft: Create Produksi
    Draft --> InProgress: Start Production
    InProgress --> Completed: Complete Production
    Completed --> [*]
    
    Draft --> [*]: Delete
    InProgress --> [*]: Delete
    
    note right of Draft
        Status awal saat
        produksi dibuat
    end note
    
    note right of InProgress
        Sedang dalam
        proses produksi
    end note
    
    note right of Completed
        Produksi selesai
        tidak bisa diubah
    end note
```

---

## Penjelasan Alur Proses

### 1. Input & Validasi
- User memilih produk, tanggal, dan qty produksi
- Sistem validasi apakah produk punya BOM dan detail
- Sistem cek stok semua bahan baku cukup atau tidak

### 2. Proses Konsumsi Bahan
- Loop setiap bahan di BOM
- Konversi satuan resep ke satuan base
- Consume stock dengan metode FIFO
- Update stok bahan baku master
- Buat record detail produksi

### 3. Perhitungan Biaya
- Total Bahan = Sum(qty × harga_satuan)
- BTKL = 20% × Total Bahan (atau dari default produk)
- BOP = 10% × Total Bahan (atau dari default produk)
- Total Biaya = Total Bahan + BTKL + BOP
- Unit Cost = Total Biaya / Qty Produksi

### 4. Update Stok Produk
- Tambah StockLayer produk jadi dengan unit cost
- Update stok produk master += qty produksi

### 5. Posting Jurnal (3 Jurnal)
**Jurnal 1: Konsumsi Bahan**
- Dr. WIP (1105) = FIFO Cost
- Cr. Persediaan Bahan Baku (1104) = FIFO Cost

**Jurnal 2: BTKL & BOP**
- Dr. WIP (1105) = BTKL + BOP
- Cr. Hutang Gaji (2103) = BTKL
- Cr. Hutang BOP (2104) = BOP

**Jurnal 3: Selesai Produksi**
- Dr. Persediaan Barang Jadi (1107) = Total Biaya
- Cr. WIP (1105) = Total Biaya

### 6. Commit & Response
- Commit transaction
- Redirect ke halaman detail produksi
- Tampilkan pesan sukses

---

## Relasi Antar Entitas

### Produksi
- **Belongs To**: Produk
- **Has Many**: ProduksiDetail
- **Generates**: JurnalUmum (3 jurnal)
- **Creates**: StockLayer (produk jadi)

### ProduksiDetail
- **Belongs To**: Produksi
- **Belongs To**: BahanBaku

### Produk
- **Has Many**: Produksi
- **Has Many**: BOM
- **Has Many**: StockLayer

### BahanBaku
- **Has Many**: ProduksiDetail
- **Has Many**: BOM
- **Has Many**: StockLayer

### StockLayer
- **Polymorphic**: item (Produk atau BahanBaku)
- **Polymorphic**: ref (Produksi, Pembelian, Penjualan)

### JurnalUmum
- **Belongs To**: COA
- **Polymorphic**: referensi (Produksi, Pembelian, Penjualan, dll)

---

## Kesimpulan

Proses produksi melibatkan:
1. ✅ Validasi BOM dan stok
2. ✅ Konsumsi bahan dengan FIFO
3. ✅ Perhitungan biaya (Bahan + BTKL + BOP)
4. ✅ Update stok produk jadi
5. ✅ Posting 3 jurnal akuntansi
6. ✅ Transaction untuk integritas data

Semua proses terintegrasi dengan:
- Stock Management (FIFO)
- Accounting (Jurnal)
- BOM (Bill of Material)
- Unit Conversion
