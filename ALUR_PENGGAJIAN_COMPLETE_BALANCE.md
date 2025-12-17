# Alur Lengkap Penggajian dengan Balance Check Sesuai Controller

## 1. Setup COA untuk Penggajian

```mermaid
flowchart TD
    A[Start Setup COA] --> B[Input Kode Akun]
    B --> C{Validasi Kode}
    C -->|Unique| D[Input Nama Akun]
    C -->|Duplicate| E[Error: Kode Sudah Ada]
    D --> F[Pilih Tipe Akun]
    F --> G[Set Saldo Normal]
    G --> H[Save COA]
    H --> I{COA Complete?}
    I -->|No| B
    I -->|Yes| J[COA Ready]
    
    subgraph "Akun Penggajian Required"
        K[5101 - Beban Gaji Pokok]
        L[5102 - Beban Tunjangan]
        M[5103 - Beban Lembur]
        N[2101 - Hutang Gaji]
        O[1101 - Kas]
        P[1102 - Bank]
    end
    
    J --> K
```

## 2. Alur Klasifikasi Tenaga Kerja (Sesuai Controller)

```mermaid
flowchart TD
    A[Input Klasifikasi Tenaga Kerja] --> B[Nama Klasifikasi]
    B --> C[Kategori: btkl/btktl]
    C --> D[Input Gaji]
    D --> E[Input Tunjangan]
    E --> F[Input Asuransi]
    F --> G[Input Tarif]
    G --> H[Normalize Money Values]
    H --> I[Generate Kode Jabatan]
    I --> J[Validate Data]
    J --> K[Save Jabatan]
    K --> L[Save Tunjangan Tambahan]
    L --> M[Success]
    
    subgraph "Validasi Controller"
        N[nama: required]
        O[kategori: required|in:btkl,btktl]
        P[tunjangan: nullable|numeric|min:0]
        Q[asuransi: nullable|numeric|min:0]
        R[gaji: nullable|numeric|min:0]
        S[tarif: nullable|numeric|min:0]
    end
    
    H --> N
```

## 3. Alur Pegawai dengan Inherit dari Klasifikasi

```mermaid
flowchart TD
    A[Input Data Pegawai] --> B[Kode Pegawai: Auto]
    B --> C[Nama Pegawai]
    C --> D[Email Unique]
    D --> E[No Telp]
    E --> F[Alamat]
    F --> G[Jenis Kelamin: L/P]
    G --> H[Pilih Klasifikasi Tenaga Kerja]
    H --> I[Inherit Gaji dari Klasifikasi]
    I --> J[Input Bank Info]
    J --> K[Validate Data]
    K --> L[Save Pegawai]
    
    subgraph "Inherit Values"
        M[Gaji: dari klasifikasi.gaji]
        N[Tunjangan: dari klasifikasi.tunjangan]
        O[Asuransi: dari klasifikasi.asuransi]
        P[Tarif: dari klasifikasi.tarif]
        Q[Jenis Pegawai: dari klasifikasi.kategori]
    end
    
    I --> M
```

## 4. Alur Presensi dengan Perhitungan

```mermaid
flowchart TD
    A[Input Presensi] --> B[Select Pegawai]
    B --> C[Tanggal Presensi]
    C --> D[Jam Masuk]
    D --> E[Jam Keluar]
    E --> F[Status: Hadir/Absen/Izin/Sakit]
    F --> G{Validate Time}
    G -->|Valid| H[Calculate Jam Kerja]
    G -->|Invalid| I[Error: Jam Tidak Valid]
    H --> J[Check Lembur]
    J --> K[Calculate Lembur Hours]
    K --> L[Save Presensi]
    L --> M[Update Summary]
    
    subgraph "Perhitungan Jam"
        N[Jam Kerja = Jam Keluar - Jam Masuk]
        O[Jam Istirahat = 1 jam]
        P[Jam Efektif = Jam Kerja - Jam Istirahat]
        Q[Lembur = Max(0, Jam Efektif - 8)]
        R[Upah Lembur = Lembur x Tarif per Jam]
    end
    
    H --> N
```

## 5. Alur Penggajian dengan Jurnal Otomatis

```mermaid
flowchart TD
    A[Proses Penggajian] --> B[Select Periode]
    B --> C[Get All Pegawai]
    C --> D[Loop Each Pegawai]
    D --> E[Get Presensi Data]
    E --> F[Calculate Gaji Pokok]
    F --> G[Calculate Tunjangan]
    G --> H[Calculate Lembur]
    H --> I[Calculate Potongan]
    I --> J[Total Gaji = Pokok + Tunjangan + Lembur - Potongan]
    J --> K[Create Penggajian Record]
    K --> L[Generate Journal Entry]
    L --> M[Validate Balance]
    M --> N{Balance OK?}
    N -->|Yes| O[Post Journal]
    N -->|No| P[Fix Error]
    P --> L
    O --> Q[Update Buku Besar]
    Q --> R{More Pegawai?}
    R -->|Yes| D
    R -->|No| S[Generate Laporan]
    
    subgraph "Journal Entry Example"
        T[DEBIT 5101: Gaji Pokok]
        U[DEBIT 5102: Tunjangan]
        V[DEBIT 5103: Lembur]
        W[CREDIT 2101: Hutang Gaji]
        X[CREDIT 1101: Kas Potongan]
        Y[Total DEBIT = Total CREDIT]
    end
    
    L --> T
```

## 6. Balance Check Detail

```mermaid
flowchart TD
    A[Start Balance Check] --> B[Calculate Total Debit]
    B --> C[Calculate Total Credit]
    C --> D[Compare Totals]
    D --> E{Debit = Credit?}
    E -->|Yes| F[Balance Valid ✓]
    E -->|No| G[Calculate Difference]
    G --> H[Show Error Details]
    H --> I[Fix Transaction]
    I --> B
    
    subgraph "Example Calculation"
        J[DEBIT: 4.000.000 + 500.000 + 200.000 = 4.700.000]
        K[CREDIT: 3.700.000 + 1.000.000 = 4.700.000]
        L[DIFFERENCE: 0]
        M[STATUS: BALANCED]
    end
    
    F --> J
```

## 7. Alur Buku Besar Update

```mermaid
flowchart TD
    A[Journal Posted] --> B[Get Journal Lines]
    B --> C[Loop Each Line]
    C --> D[Find COA Account]
    D --> E[Get Current Balance]
    E --> F[Update Balance]
    F --> G[Save New Balance]
    G --> H[Log Transaction]
    H --> I{More Lines?}
    I -->|Yes| C
    I -->|No| J[Generate Buku Besar Report]
    
    subgraph "Balance Update Rules"
        K[DEBIT: Balance + Amount]
        L[CREDIT: Balance - Amount]
        M[Asset/Expense: Normal DEBIT]
        N[Liability/Equity/Revenue: Normal CREDIT]
    end
    
    F --> K
```

## 8. Alur Laporan dan Slip Gaji

```mermaid
flowchart TD
    A[Penggajian Complete] --> B[Generate Slip Gaji]
    B --> C[Format Slip HTML]
    C --> D[Calculate Take Home Pay]
    D --> E[Add Bank Info]
    E --> F[Export to PDF]
    F --> G[Send to Employee]
    G --> H[Generate Monthly Report]
    H --> I[Calculate Summary]
    I --> J[Export Reports]
    
    subgraph "Slip Gaji Components"
        K[Employee Info]
        L[Period Info]
        M[Income Details]
        N[Deduction Details]
        O[Net Pay]
        P[Bank Transfer Info]
    end
    
    C --> K
```

## 9. Integrasi Complete Flow dengan Balance Check

```mermaid
flowchart TD
    A[Start] --> B[Setup COA]
    B --> C[Setup Klasifikasi Tenaga Kerja]
    C --> D[Setup Pegawai]
    D --> E[Input Presensi]
    E --> F[Proses Penggajian]
    F --> G[Generate Journal]
    G --> H[Validate Balance]
    H --> I{Balance OK?}
    I -->|Yes| J[Post Journal]
    I -->|No| K[Error Handling]
    K --> G
    J --> L[Update Buku Besar]
    L --> M[Generate Laporan]
    M --> N[Generate Slip Gaji]
    N --> O[End]
    
    subgraph "Critical Balance Points"
        P[Journal Creation]
        Q[Journal Posting]
        R[Buku Besar Update]
        S[Report Generation]
    end
    
    G --> P
    J --> Q
    L --> R
    M --> S
```

## 10. Contoh Transaksi Real dengan Angka

```mermaid
sequenceDiagram
    participant User as User
    participant COA as COA System
    participant Klas as Klasifikasi System
    participant Peg as Pegawai System
    participant Pres as Presensi System
    participant Gaji as Penggajian System
    participant Jurnal as Journal System
    participant BB as Buku Besar
    participant Lapor as Laporan System
    
    User->>COA: Setup Akun 5101,5102,5103,2101,1101,1102
    COA-->>User: COA Ready
    
    User->>Klas: Create Hair Stylist
    Klas->>Klas: nama: "Hair Stylist"
    Klas->>Klas: kategori: "btkl"
    Klas->>Klas: gaji: 4000000
    Klas->>Klas: tunjangan: 500000
    Klas->>Klas: tarif: 25000
    Klas->>Klas: Generate kode: "BT001"
    Klas-->>User: Klasifikasi Saved
    
    User->>Peg: Create Ahmad Rizki
    Peg->>Klas: Get Hair Stylist data
    Klas-->>Peg: gaji:4000000, tunjangan:500000, tarif:25000
    Peg->>Peg: Generate kode: "PGW0001"
    Peg-->>User: Pegawai Saved
    
    User->>Pres: Input presensi Desember
    Pres->>Pres: 22 hari hadir, 8 jam lembur
    Pres-->>User: Presensi Recorded
    
    User->>Gaji: Proses penggajian Desember
    Gaji->>Gaji: Calculate:
    Gaji->>Gaji: Gaji Pokok: 4000000
    Gaji->>Gaji: Tunjangan: 500000
    Gaji->>Gaji: Lembur: 8 x 25000 = 200000
    Gaji->>Gaji: Potongan: 1000000
    Gaji->>Gaji: Total: 3700000
    
    Gaji->>Jurnal: Create journal
    Jurnal->>Jurnal: DEBIT 5101: 4000000
    Jurnal->>Jurnal: DEBIT 5102: 500000
    Jurnal->>Jurnal: DEBIT 5103: 200000
    Jurnal->>Jurnal: CREDIT 2101: 3700000
    Jurnal->>Jurnal: CREDIT 1101: 1000000
    Jurnal->>Jurnal: Total D: 4700000
    Jurnal->>Jurnal: Total C: 4700000
    Jurnal->>Jurnal: Balance: ✓
    Jurnal-->>Gaji: Journal Posted
    
    Gaji->>BB: Update balances
    BB->>BB: 5101: +4000000
    BB->>BB: 5102: +500000
    BB->>BB: 5103: +200000
    BB->>BB: 2101: +3700000
    BB->>BB: 1101: +1000000
    BB-->>Gaji: Buku Besar Updated
    
    Gaji->>Lapor: Generate reports
    Lapor->>Lapor: Slip gaji Ahmad Rizki
    Lapor->>Lapor: Monthly summary
    Lapor-->>User: Reports Ready
```

## 11. Validation Rules Sesuai Controller

```mermaid
flowchart TD
    A[Input Validation] --> B[Klasifikasi Validation]
    B --> C[Pegawai Validation]
    C --> D[Presensi Validation]
    D --> E[Penggajian Validation]
    E --> F[Journal Validation]
    
    subgraph "KlasifikasiTenagaKerjaController"
        G[nama: required|string|max:255]
        H[kategori: required|in:btkl,btktl]
        I[tunjangan: nullable|numeric|min:0]
        J[asuransi: nullable|numeric|min:0]
        K[gaji: nullable|numeric|min:0]
        L[tarif: nullable|numeric|min:0]
    end
    
    subgraph "Money Normalization"
        M[Format 1.234,56 → 1234.56]
        N[Format 1,234.56 → 1234.56]
        O[Remove spaces and commas]
    end
    
    B --> G
    B --> M
```

## 12. Error Handling dan Recovery

```mermaid
flowchart TD
    A[Transaction Error] --> B{Error Type}
    B -->|Validation Error| C[Show Validation Messages]
    B -->|Balance Error| D[Show Balance Details]
    B -->|Database Error| E[Log Error & Rollback]
    
    C --> F[Fix Input Data]
    D --> G[Adjust Journal Lines]
    E --> H[Check Connection]
    
    F --> I[Retry Transaction]
    G --> I
    H --> I
    
    I --> J{Retry Success?}
    J -->|Yes| K[Continue Process]
    J -->|No| L[Escalate to Admin]
    
    subgraph "Recovery Options"
        M[Manual Journal Entry]
        N[Data Correction]
        O[Process Restart]
    end
    
    L --> M
```

## Kesimpulan

Alur ini mengikuti controller yang ada dengan:

1. **KlasifikasiTenagaKerjaController** - Validasi btkl/btktl, normalize money, auto-generate kode
2. **Balance Check** - Setiap jurnal harus balance (Debit = Credit)
3. **COA Integration** - Semua transaksi terlink ke akun yang benar
4. **Audit Trail** - Setiap step tercatat dan dapat dilacak
5. **Error Recovery** - Handle error dengan recovery options
6. **Real Numbers** - Contoh dengan angka aktual untuk demonstrasi

Sistem menjamin integritas data dan compliance akuntansi di setiap step.
