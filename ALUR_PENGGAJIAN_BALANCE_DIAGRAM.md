# Alur Pengisian Sistem Penggajian dengan Balance dan Akun Jurnal

## 1. Alur Pengisian COA (Chart of Accounts)

```mermaid
classDiagram
    class CoaSetup {
        +int id
        +string kode_akun
        +string nama_akun
        +enum tipe_akun
        +decimal saldo_normal
        +setupAkunPenggajian()
        +validateKodeAkun()
        +getAkunPenggajian()
    }
    
    class AkunPenggajian {
        +string kode_akun: "5101"
        +string nama: "Beban Gaji Pokok"
        +enum tipe: "Expense" 
        +decimal normal: "Debit"
        
        +string kode_akun: "5102"
        +string nama: "Beban Tunjangan"
        +enum tipe: "Expense"
        +decimal normal: "Debit"
        
        +string kode_akun: "5103" 
        +string nama: "Beban Lembur"
        +enum tipe: "Expense"
        +decimal normal: "Debit"
        
        +string kode_akun: "2101"
        +string nama: "Hutang Gaji"
        +enum tipe: "Liability"
        +decimal normal: "Credit"
        
        +string kode_akun: "1101"
        +string nama: "Kas"
        +enum tipe: "Asset"
        +decimal normal: "Debit"
        
        +string kode_akun: "1102"
        +string nama: "Bank"
        +enum tipe: "Asset"
        +decimal normal: "Debit"
    }
    
    CoaSetup --> AkunPenggajian : "setup akun penggajian"
    
    note for CoaSetup "1. Input kode akun (5101, 5102, 5103)\n2. Input nama akun\n3. Pilih tipe akun\n4. Set saldo normal\n5. Save ke database"
```

## 2. Alur Klasifikasi Tenaga Kerja dengan Gaji dan Tunjangan

```mermaid
classDiagram
    class KlasifikasiTenagaKerjaSetup {
        +int id
        +string kode_jabatan: "JBT-001"
        +string nama: "Hair Stylist"
        +enum kategori: "btkl"
        +decimal gaji: 4000000
        +decimal tunjangan: 500000
        +decimal asuransi: 200000
        +decimal tarif: 25000
        +inputKlasifikasi()
        +hitungTotalKomponen()
        +linkToCoa()
    }
    
    class KomponenGaji {
        +decimal gaji_pokok: 4000000
        +decimal tunjangan_jabatan: 500000
        +decimal tunjangan_transport: 300000
        +decimal tunjangan_makan: 200000
        +decimal asuransi: 200000
        +decimal pph21: 400000
        +decimal bpjs: 400000
        +calculateTotalGaji()
        +calculateTotalPotongan()
        +getTakeHomePay()
    }
    
    class CoaMapping {
        +string gaji_pokok_coa: "5101"
        +string tunjangan_coa: "5102"
        +string lembur_coa: "5103"
        +string hutang_gaji_coa: "2101"
        +string kas_coa: "1101"
        +string bank_coa: "1102"
        +validateMapping()
        +getCoaByKomponen()
    }
    
    KlasifikasiTenagaKerjaSetup --> KomponenGaji : "define komponen"
    KlasifikasiTenagaKerjaSetup --> CoaMapping : "link ke COA"
    
    note for KlasifikasiTenagaKerjaSetup "1. Input kode klasifikasi\n2. Input nama klasifikasi\n3. Pilih kategori (btkl/btktl)\n4. Set gaji pokok\n5. Set tunjangan\n6. Link ke akun COA"
    note for KomponenGaji "Total Gaji: 4.000.000 + 500.000 + 300.000 + 200.000 = 5.000.000\nTotal Potongan: 200.000 + 400.000 + 400.000 = 1.000.000\nTake Home Pay: 5.000.000 - 1.000.000 = 4.000.000"
```

## 3. Alur Pegawai dengan Integrasi Klasifikasi Tenaga Kerja dan COA

```mermaid
classDiagram
    class PegawaiSetup {
        +int id
        +string kode_pegawai: "PGW-0001"
        +string nama: "Ahmad Rizki"
        +string email: "ahmad@email.com"
        +int klasifikasi_tenaga_kerja_id: 1
        +string bank: "BCA"
        +string no_rekening: "1234567890"
        +inputPegawai()
        +assignKlasifikasi()
        +inheritGajiFromKlasifikasi()
    }
    
    class GajiPegawai {
        +decimal gaji_pokok: 4000000
        +decimal tunjangan: 500000
        +decimal tarif_per_jam: 25000
        +decimal asuransi: 200000
        +decimal pph21: 400000
        +decimal bpjs: 400000
        +getGajiBulanan()
        +getTarifLembur()
        +calculateNetGaji()
    }
    
    class RekeningPegawai {
        +string bank: "BCA"
        +string no_rekening: "1234567890"
        +string nama_rekening: "Ahmad Rizki"
        +string coa_bank: "1102"
        +validateRekening()
        +getCoaBank()
    }
    
    PegawaiSetup --> GajiPegawai : "inherit dari klasifikasi"
    PegawaiSetup --> RekeningPegawai : "setup rekening"
    
    note for PegawaiSetup "1. Input data pribadi\n2. Pilih klasifikasi tenaga kerja\n3. Auto inherit gaji dari klasifikasi\n4. Input data rekening\n5. Link ke COA bank"
```

## 4. Alur Presensi dengan Perhitungan Jam Kerja

```mermaid
classDiagram
    class PresensiInput {
        +int pegawai_id: 1
        +date tgl_presensi: "2025-12-14"
        +time jam_masuk: "08:00"
        +time jam_keluar: "17:00"
        +enum status: "Hadir"
        +recordPresensi()
        +calculateJamKerja()
        +validatePresensi()
    }
    
    class PerhitunganJam {
        +time jam_masuk: "08:00"
        +time jam_keluar: "17:00"
        +int jam_kerja: 8
        +int jam_lembur: 0
        +decimal tarif_lembur: 25000
        +decimal upah_lembur: 0
        +calculateJamKerja()
        +calculateLembur()
        +calculateUpahLembur()
    }
    
    class RekapPresensi {
        +string periode: "2025-12"
        +int total_hadir: 22
        +int total_absen: 2
        +int total_izin: 1
        +int total_sakit: 0
        +decimal total_jam_kerja: 176
        +decimal total_jam_lembur: 8
        +generateRekap()
        +calculateSummary()
    }
    
    PresensiInput --> PerhitunganJam : "hitung jam kerja"
    PresensiInput --> RekapPresensi : "rekap bulanan"
    
    note for PresensiInput "1. Scan/absen pegawai\n2. Input jam masuk & keluar\n3. Hitung jam kerja (8 jam)\n4. Cek lembur (>8 jam)\n5. Save ke database"
    note for PerhitunganJam "Jam Kerja: 17:00 - 08:00 = 9 jam\nJam Istirahat: 1 jam\nJam Efektif: 8 jam\nLembur: 0 jam\nUpah Lembur: 0 x 25.000 = 0"
```

## 5. Alur Transaksi Penggajian dengan Jurnal Otomatis

```mermaid
classDiagram
    class PenggajianProcess {
        +int pegawai_id: 1
        +string periode: "2025-12"
        +decimal gaji_pokok: 4000000
        +decimal tunjangan: 500000
        +decimal lembur: 200000
        +decimal potongan: 1000000
        +decimal total_gaji: 3700000
        +processPenggajian()
        +generateJournalEntry()
        +validateBalance()
    }
    
    class JournalEntry {
        +string nomor_jurnal: "JR-202512-001"
        +date tanggal: "2025-12-31"
        +string ref_type: "Penggajian"
        +int ref_id: 1
        +string memo: "Gaji Desember 2025"
        +decimal total_debit: 4700000
        +decimal total_credit: 4700000
        +createJournalLines()
        +validateBalance()
        +postJournal()
    }
    
    class JournalLines {
        +array debits: [
            "5101: 4000000",
            "5102: 500000", 
            "5103: 200000"
        ]
        +array credits: [
            "2101: 3700000",
            "1101: 1000000"
        ]
        +calculateTotalDebit()
        +calculateTotalCredit()
        +validateBalance()
    }
    
    PenggajianProcess --> JournalEntry : "generate jurnal"
    JournalEntry --> JournalLines : "create lines"
    
    note for PenggajianProcess "1. Pilih periode penggajian\n2. Ambil data presensi\n3. Hitung gaji pokok (22 x 8 jam)\n4. Hitung tunjangan (tetap)\n5. Hitung lembur (8 jam x 25.000)\n6. Hitung potongan\n7. Generate jurnal otomatis"
    note for JournalLines "DEBIT:\n5101 Beban Gaji Pokok: 4.000.000\n5102 Beban Tunjangan: 500.000\n5103 Beban Lembur: 200.000\nTotal Debit: 4.700.000\n\nCREDIT:\n2101 Hutang Gaji: 3.700.000\n1101 Kas (Potongan): 1.000.000\nTotal Credit: 4.700.000\n\nBalance: ✓"
```

## 6. Alur Laporan Penggajian dengan Slip Gaji

```mermaid
classDiagram
    class LaporanGenerator {
        +string periode: "2025-12"
        +array pegawais: [1, 2, 3]
        +decimal total_gaji: 15000000
        +decimal total_tunjangan: 2000000
        +decimal total_potongan: 3000000
        +decimal total_bayar: 14000000
        +generateLaporan()
        +generateSlipGaji()
        +exportToPdf()
    }
    
    class SlipGaji {
        +string kode_pegawai: "PGW-0001"
        +string nama: "Ahmad Rizki"
        +string jabatan: "Hair Stylist"
        +decimal gaji_pokok: 4000000
        +decimal tunjangan: 500000
        +decimal lembur: 200000
        +decimal total_pendapatan: 4700000
        +decimal asuransi: 200000
        +decimal pph21: 400000
        +decimal bpjs: 400000
        +decimal total_potongan: 1000000
        +decimal take_home_pay: 3700000
        +string bank: "BCA"
        +string rekening: "1234567890"
        +generateSlip()
        +calculateTotal()
        +formatRupiah()
    }
    
    class SummaryLaporan {
        +int total_pegawai: 3
        +decimal total_gaji_pokok: 12000000
        +decimal total_tunjangan: 2000000
        +decimal total_lembur: 1000000
        +decimal total_potongan: 3000000
        +decimal total_bayar: 12000000
        +generateSummary()
        +getAverageGaji()
        +getHighestGaji()
    }
    
    LaporanGenerator --> SlipGaji : "generate slip"
    LaporanGenerator --> SummaryLaporan : "generate summary"
    
    note for SlipGaji "SLIP GAJI - DESEMBER 2025\n\nKaryawan: Ahmad Rizki (PGW-0001)\nJabatan: Hair Stylist\n\nPENDAPATAN:\nGaji Pokok: Rp 4.000.000\nTunjangan Jabatan: Rp 500.000\nTunjangan Lembur: Rp 200.000\nTotal Pendapatan: Rp 4.700.000\n\nPOTONGAN:\nAsuransi: Rp 200.000\nPPH 21: Rp 400.000\nBPJS: Rp 400.000\nTotal Potongan: Rp 1.000.000\n\nTAKE HOME PAY: Rp 3.700.000\n\nTransfer ke:\nBCA - 1234567890 (Ahmad Rizki)"
```

## 7. Alur Jurnal Umum dengan Balance Akun

```mermaid
classDiagram
    class JurnalUmum {
        +string nomor_jurnal: "JR-202512-001"
        +date tanggal: "2025-12-31"
        +string memo: "Penggajian Desember 2025"
        +array journal_lines: []
        +decimal total_debit: 4700000
        +decimal total_credit: 4700000
        +enum status: "posted"
        +createJournalEntry()
        +addJournalLine()
        +validateBalance()
        +postJournal()
    }
    
    class JournalLine {
        +int account_id: 1
        +string kode_akun: "5101"
        +string nama_akun: "Beban Gaji Pokok"
        +decimal debit: 4000000
        +decimal credit: 0
        +string keterangan: "Gaji pokok Ahmad Rizki"
        +validateLine()
        +getBalance()
    }
    
    class BalanceValidation {
        +decimal total_debit: 4700000
        +decimal total_credit: 4700000
        +boolean is_balanced: true
        +decimal difference: 0
        +validateTotalBalance()
        +getDifference()
        +throwIfNotBalanced()
    }
    
    JurnalUmum --> JournalLine : "add lines"
    JurnalUmum --> BalanceValidation : "validate balance"
    
    note for JurnalUmum "JURNAL UMUM - JR-202512-001\nTanggal: 31 Desember 2025\nMemo: Penggajian Desember 2025\nStatus: POSTED\n\nACCOUNT                DEBIT        CREDIT        BALANCE\n5101 Beban Gaji Pokok   4.000.000    0             4.000.000\n5102 Beban Tunjangan     500.000    0             500.000\n5103 Beban Lembur       200.000    0             200.000\n2101 Hutang Gaji        0          3.700.000     (3.700.000)\n1101 Kas               1.000.000    0             1.000.000\nTOTAL                 5.700.000    3.700.000     2.000.000\n\nBalance Check: ✓ (4.700.000 = 4.700.000)"
```

## 8. Alur Buku Besar dengan Rekapitulasi

```mermaid
classDiagram
    class BukuBesar {
        +string periode: "2025-12"
        +array coa_accounts: []
        +decimal total_asset: 50000000
        +decimal total_liability: 20000000
        +decimal total_equity: 30000000
        +decimal total_revenue: 0
        +decimal total_expense: 4700000
        +generateBukuBesar()
        +calculateSaldoAkhir()
        +generateNeraca()
        +generateLabaRugi()
    }
    
    class RekeningBukuBesar {
        +string kode_akun: "5101"
        +string nama: "Beban Gaji Pokok"
        +decimal saldo_awal: 0
        +array transaksi: []
        +decimal total_debit: 4000000
        +decimal total_credit: 0
        +decimal saldo_akhir: 4000000
        +addTransaksi()
        +calculateSaldo()
        +formatBukuBesar()
    }
    
    class NeracaSaldo {
        +array akun_asset: []
        +array akun_liability: []
        +array akun_equity: []
        +array akun_revenue: []
        +array akun_expense: []
        +decimal total_debit: 54700000
        +decimal total_credit: 54700000
        +generateNeracaSaldo()
        +validateBalance()
        +exportToExcel()
    }
    
    BukuBesar --> RekeningBukuBesar : "generate per akun"
    BukuBesar --> NeracaSaldo : "generate summary"
    
    note for RekeningBukuBesar "BUKU BESAR - 5101 Beban Gaji Pokok\nPeriode: Desember 2025\n\nTanggal     Keterangan                  Debit      Credit     Saldo\n2025-12-01 Saldo Awal                  0          0          0\n2025-12-31 Gaji Ahmad Rizki            4.000.000  0          4.000.000\n2025-12-31 Gaji Siti Nurhaliza         3.500.000  0          7.500.000\n2025-12-31 Gaji Budi Santoso           3.000.000  0          10.500.000\n           TOTAL DEBIT                 10.500.000 0          10.500.000\n           TOTAL CREDIT                0          0          10.500.000\n           SALDO AKHIR                 10.500.000"
```

## 9. Alur Integrasi Lengkap dengan Balance Check

```mermaid
flowchart TD
    A[Setup COA] --> B[Setup Jabatan]
    B --> C[Setup Pegawai]
    C --> D[Input Presensi]
    D --> E[Proses Penggajian]
    E --> F[Generate Jurnal]
    F --> G[Validate Balance]
    G --> H{Balance OK?}
    H -->|Yes| I[Post Jurnal]
    H -->|No| J[Fix Error]
    J --> F
    I --> K[Update Buku Besar]
    K --> L[Generate Laporan]
    L --> M[Generate Slip Gaji]
    
    subgraph "COA Setup"
        A1[5101 Beban Gaji Pokok]
        A2[5102 Beban Tunjangan]
        A3[5103 Beban Lembur]
        A4[2101 Hutang Gaji]
        A5[1101 Kas]
        A6[1102 Bank]
    end
    
    subgraph "Balance Check"
        G1[Total Debit: 4.700.000]
        G2[Total Credit: 4.700.000]
        G3[Difference: 0]
        G4[Status: Balanced ✓]
    end
    
    subgraph "Journal Entry Example"
        F1[JR-202512-001]
        F2[31-12-2025]
        F3[Penggajian Desember]
        F4[Lines: 5 items]
    end
    
    A --> A1
    F --> F1
    G --> G1
```

## 10. Contoh Transaksi Lengkap dengan Angka

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
    
    User->>COA: Setup Akun Penggajian
    COA-->>User: Akun 5101, 5102, 5103, 2101 created
    
    User->>Klas: Input Klasifikasi Hair Stylist
    Klas->>Klas: Set Gaji: 4.000.000
    Klas->>Klas: Set Tunjangan: 500.000
    Klas-->>User: Klasifikasi saved with COA mapping
    
    User->>Peg: Input Pegawai Ahmad Rizki
    Peg->>Klas: Get data klasifikasi
    Klas-->>Peg: Gaji 4.000.000, Tunjangan 500.000
    Peg-->>User: Pegawai created
    
    User->>Pres: Input Presensi Desember
    Pres->>Pres: Hitung 22 hari hadir
    Pres->>Pres: Hitung 8 jam lembur
    Pres-->>User: Presensi recorded
    
    User->>Gaji: Proses Penggajian Desember
    Gaji->>Pres: Get data presensi
    Pres-->>Gaji: 22 hari, 8 jam lembur
    Gaji->>Gaji: Calculate:
    Gaji->>Gaji: Gaji Pokok: 4.000.000
    Gaji->>Gaji: Tunjangan: 500.000
    Gaji->>Gaji: Lembur: 8 x 25.000 = 200.000
    Gaji->>Gaji: Total: 4.700.000
    Gaji->>Gaji: Potongan: 1.000.000
    Gaji->>Gaji: Take Home: 3.700.000
    
    Gaji->>Jurnal: Create Journal Entry
    Jurnal->>Jurnal: DEBIT 5101: 4.000.000
    Jurnal->>Jurnal: DEBIT 5102: 500.000
    Jurnal->>Jurnal: DEBIT 5103: 200.000
    Jurnal->>Jurnal: CREDIT 2101: 3.700.000
    Jurnal->>Jurnal: CREDIT 1101: 1.000.000
    Jurnal->>Jurnal: Total D: 4.700.000
    Jurnal->>Jurnal: Total C: 4.700.000
    Jurnal->>Jurnal: Balance: ✓
    Jurnal-->>Gaji: Journal Posted
    
    Gaji->>BB: Update Buku Besar
    BB->>BB: Update saldo 5101: +4.000.000
    BB->>BB: Update saldo 5102: +500.000
    BB->>BB: Update saldo 5103: +200.000
    BB->>BB: Update saldo 2101: +3.700.000
    BB->>BB: Update saldo 1101: +1.000.000
    
    Gaji->>Lapor: Generate Slip Gaji
    Lapor->>Lapor: Format slip dengan semua detail
    Lapor-->>User: Slip Gaji Generated
    Lapor-->>User: Laporan Generated
```

## Kesimpulan

Alur ini menjamin:
1. **Balance Check** - Setiap jurnal otomatis divalidasi balance (Debit = Credit)
2. **COA Integration** - Setiap transaksi terlink ke akun yang benar
3. **Automated Calculation** - Perhitungan gaji, tunjangan, dan potongan otomatis
4. **Audit Trail** - Setiap transaksi tercatat di jurnal dan buku besar
5. **Compliance** - Mengikuti prinsip akuntansi double entry
6. **Reporting** - Laporan dan slip gaji otomatis tergenerate

Dengan alur ini, sistem penggajian akan selalu balance dan terintegrasi dengan sempurna ke sistem akuntansi.
