<?php

namespace App\Filament\Resources\Pembelians\Schemas;

use App\Models\Vendor;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;

class PembelianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Header Section
                Card::make()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('nomor_pembelian')
                                    ->label('Nomor Pembelian')
                                    ->default(function () {
                                        $date = now()->format('Ymd');
                                        $count = \App\Models\Pembelian::whereDate('tanggal', now())->count() + 1;
                                        return 'PB-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                                    })
                                    ->disabled()
                                    ->dehydrated(),
                                
                                TextInput::make('nomor_faktur')
                                    ->label('Nomor Faktur Pembelian')
                                    ->placeholder('0232000002'),
                                
                                DatePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->default(now())
                                    ->required(),
                                
                                Select::make('payment_method')
                                    ->label('Metode Pembayaran')
                                    ->options([
                                        'cash' => 'Cash',
                                        'credit' => 'Credit',
                                    ])
                                    ->default('cash')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        self::calculateTotals($set, $get);
                                    }),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                Select::make('vendor_id')
                                    ->label('Vendor')
                                    ->options(Vendor::all()->pluck('nama_vendor', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('nama_vendor')
                                            ->label('Nama Vendor')
                                            ->required(),
                                        TextInput::make('alamat')
                                            ->label('Alamat'),
                                        TextInput::make('no_telp')
                                            ->label('No. Telepon'),
                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email(),
                                    ])
                                    ->createOptionUsing(function (array $data) {
                                        return Vendor::create($data)->id;
                                    }),
                                
                                Textarea::make('keterangan')
                                    ->label('Keterangan')
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Conversion Examples Section
                Section::make('Contoh Konversi Satuan Pembelian')
                    ->description('Tips: Sistem akan otomatis mengkonversi satuan pembelian ke satuan utama untuk perhitungan stok. Pastikan faktor konversi sudah benar.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('contoh_bahan_baku')
                                    ->label('Satuan Bahan & Konversi')
                                    ->content('• 1 Liter = 1 kg (cairan utama)
• 1 Ton = 1000 kg (bahan utama)
• 1 Kg = 2 Kg (bahan khusus)
• 1 Kg = 1 Kg (bahan normal)
• 500 Gram = 0.5 Kg'),
                                
                                Placeholder::make('contoh_konversi')
                                    ->label('Satuan Konversi')
                                    ->content('• 1 Tabung = 12 kg (tabung 12 kg)
• 1 Karung = 25 kg (karung 25 kg)
• 1 Kaleng = 5.5 kg (kaleng 5.5 kg)
• 1 Jerigen = 5 kg (jerigen 5 kg)
• 1 Karton = 0.5 kg (karton 0.5 kg)'),
                                
                                Placeholder::make('contoh_hitung')
                                    ->label('Estimasi Harga Satuan')
                                    ->content('• 1 kg = Rp 5000 = Rp 5000 Gram
• 1 Liter = Rp 6000 = Rp 6000 Liter
• 1 Kaleng = Rp 27500 = Rp 5000 Kg
• 1 Tabung = Rp 60000 = Rp 5000 Kg
• 1 Ton = Rp 5000000 = Rp 5000 Kg'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // Purchase Details Section
                Section::make('Detail Barang Baku')
                    ->schema([
                        Repeater::make('pembelian_details')
                            ->relationship('pembelianDetails')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        Select::make('tipe_item')
                                            ->label('Barang Baku')
                                            ->options([
                                                'bahan_baku' => 'Bahan Baku',
                                                'bahan_pendukung' => 'Bahan Pendukung',
                                            ])
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set) {
                                                $set('bahan_baku_id', null);
                                                $set('bahan_pendukung_id', null);
                                                $set('satuan', null);
                                                $set('harga_satuan', null);
                                                $set('faktor_konversi', 1);
                                            }),
                                        
                                        Select::make('bahan_baku_id')
                                            ->label('Nama Bahan')
                                            ->options(function () {
                                                return BahanBaku::all()->pluck('nama_bahan', 'id');
                                            })
                                            ->searchable()
                                            ->visible(fn (Get $get) => $get('tipe_item') === 'bahan_baku')
                                            ->required(fn (Get $get) => $get('tipe_item') === 'bahan_baku')
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                if ($state) {
                                                    $bahan = BahanBaku::find($state);
                                                    if ($bahan && $bahan->satuan) {
                                                        $set('satuan', $bahan->satuan->nama);
                                                        $set('faktor_konversi', 1);
                                                    }
                                                }
                                            }),
                                        
                                        Select::make('bahan_pendukung_id')
                                            ->label('Nama Bahan')
                                            ->options(function () {
                                                return BahanPendukung::all()->pluck('nama_bahan', 'id');
                                            })
                                            ->searchable()
                                            ->visible(fn (Get $get) => $get('tipe_item') === 'bahan_pendukung')
                                            ->required(fn (Get $get) => $get('tipe_item') === 'bahan_pendukung')
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                if ($state) {
                                                    $bahan = BahanPendukung::find($state);
                                                    if ($bahan && $bahan->satuanRelation) {
                                                        $set('satuan', $bahan->satuanRelation->nama);
                                                        $set('faktor_konversi', 1);
                                                    }
                                                }
                                            }),
                                        
                                        TextInput::make('jumlah')
                                            ->label('Jumlah')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0.0001)
                                            ->step(0.0001)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Set $set, Get $get) {
                                                self::calculateSubtotal($set, $get);
                                            }),
                                        
                                        TextInput::make('satuan')
                                            ->label('Satuan Pembelian')
                                            ->required()
                                            ->disabled(),
                                        
                                        TextInput::make('harga_satuan')
                                            ->label('Harga Satuan')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->prefix('Rp')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Set $set, Get $get) {
                                                self::calculateSubtotal($set, $get);
                                            }),
                                        
                                        TextInput::make('faktor_konversi')
                                            ->label('Faktor Konversi')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->minValue(0.0001)
                                            ->step(0.0001)
                                            ->helperText('1 unit pembelian = berapa satuan utama')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Set $set, Get $get) {
                                                self::calculateSubtotal($set, $get);
                                            }),
                                    ]),
                                
                                Grid::make(4)
                                    ->schema([
                                        Placeholder::make('jumlah_konversi')
                                            ->label('Estimasi Isi Satuan Utama (Manual)')
                                            ->content(function (Get $get) {
                                                $jumlah = $get('jumlah') ?? 0;
                                                $faktor = $get('faktor_konversi') ?? 1;
                                                $hasil = $jumlah * $faktor;
                                                return number_format($hasil, 4) . ' Kg Bahan baku yang dipilih';
                                            }),
                                        
                                        Placeholder::make('harga_per_kg')
                                            ->label('Harga per Satuan Utama')
                                            ->content(function (Get $get) {
                                                $harga = $get('harga_satuan') ?? 0;
                                                $faktor = $get('faktor_konversi') ?? 1;
                                                if ($faktor > 0) {
                                                    $hargaPerKg = $harga / $faktor;
                                                    return 'Rp ' . number_format($hargaPerKg, 0, ',', '.');
                                                }
                                                return 'Rp 0';
                                            }),
                                        
                                        TextInput::make('subtotal')
                                            ->label('Harga Total')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->disabled()
                                            ->dehydrated(),
                                    ]),
                            ])
                            ->addActionLabel('+ Tambah Barang')
                            ->defaultItems(1)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::calculateTotals($set, $get);
                            }),
                    ]),

                // 🔄 KONVERSI TAMBAHAN - Section Baru
                Card::make()
                    ->schema([
                        Section::make('🔄 Konversi Tambahan')
                            ->description('Tambahkan konversi ke satuan lain untuk bahan yang dipilih')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('enable_additional_conversion')
                                            ->label('✅ Aktifkan Konversi Tambahan')
                                            ->default(false)
                                            ->live(),
                                        
                                        Placeholder::make('info_konversi')
                                            ->label('ℹ️ Info')
                                            ->content('Fitur ini memungkinkan konversi ke satuan 1, 2, atau 3')
                                            ->visible(fn (Get $get) => $get('enable_additional_conversion')),
                                    ]),
                                
                                // Form konversi tambahan
                                Grid::make(3)
                                    ->schema([
                                        Select::make('target_satuan_type')
                                            ->label('🎯 Pilih Satuan Target')
                                            ->options([
                                                'satuan_1' => '1️⃣ Satuan 1',
                                                'satuan_2' => '2️⃣ Satuan 2',
                                                'satuan_3' => '3️⃣ Satuan 3',
                                            ])
                                            ->placeholder('Pilih satuan target'),
                                        
                                        TextInput::make('jumlah_konversi_manual')
                                            ->label('📊 Jumlah Konversi')
                                            ->numeric()
                                            ->placeholder('Masukkan jumlah'),
                                        
                                        TextInput::make('keterangan_konversi')
                                            ->label('📝 Keterangan')
                                            ->placeholder('Catatan tambahan'),
                                    ])
                                    ->visible(fn (Get $get) => $get('enable_additional_conversion')),
                            ])
                            ->collapsible()
                            ->collapsed(false), // Buka secara default untuk testing
                    ])
                    ->columnSpanFull(),

                // Totals Section
                Card::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated(),
                                
                                TextInput::make('biaya_kirim')
                                    ->label('Biaya Kirim')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        self::calculateTotals($set, $get);
                                    }),
                                
                                TextInput::make('ppn_persen')
                                    ->label('PPN (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        self::calculateTotals($set, $get);
                                    }),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('ppn_nominal')
                                    ->label('PPN Nominal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated(),
                                
                                TextInput::make('total_harga')
                                    ->label('Total Harga')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Hidden fields for payment tracking
                Hidden::make('terbayar')->default(0),
                Hidden::make('sisa_pembayaran')->default(0),
                Hidden::make('status')->default('belum_lunas'),
            ]);
    }

    private static function calculateSubtotal(Set $set, Get $get): void
    {
        $jumlah = (float) ($get('jumlah') ?? 0);
        $harga = (float) ($get('harga_satuan') ?? 0);
        $subtotal = $jumlah * $harga;
        
        $set('subtotal', $subtotal);
    }

    /**
     * Calculate additional conversion based on purchase quantity and conversion factors
     */
    private static function calculateAdditionalConversion(Set $set, Get $get): void
    {
        $jumlahPembelian = (float) ($get('../../jumlah') ?? 0);
        $faktorKonversiUtama = (float) ($get('../../faktor_konversi') ?? 1);
        $masterConversionFactor = (float) ($get('master_conversion_factor') ?? 1);
        $manualConversionFactor = $get('faktor_konversi_manual');
        
        if ($jumlahPembelian <= 0) return;
        
        // Use manual factor if provided, otherwise use master data
        $conversionFactor = $manualConversionFactor ? (float) $manualConversionFactor : $masterConversionFactor;
        
        // Calculate: Purchase Quantity → Main Unit → Target Sub Unit
        // Step 1: Convert purchase quantity to main unit
        $jumlahSatuanUtama = $jumlahPembelian * $faktorKonversiUtama;
        
        // Step 2: Convert main unit to target sub unit
        // If 1 sub unit = X main units, then Y main units = Y/X sub units
        if ($conversionFactor > 0) {
            $jumlahKonversi = $jumlahSatuanUtama / $conversionFactor;
        } else {
            $jumlahKonversi = 0;
        }
        
        $set('jumlah_konversi', round($jumlahKonversi, 4));
    }

    private static function calculateTotals(Set $set, Get $get): void
    {
        $details = $get('pembelian_details') ?? [];
        $subtotal = 0;
        
        foreach ($details as $detail) {
            $subtotal += (float) ($detail['subtotal'] ?? 0);
        }
        
        $biayaKirim = (float) ($get('biaya_kirim') ?? 0);
        $ppnPersen = (float) ($get('ppn_persen') ?? 0);
        
        $ppnNominal = ($subtotal + $biayaKirim) * ($ppnPersen / 100);
        $totalHarga = $subtotal + $biayaKirim + $ppnNominal;
        
        $set('subtotal', $subtotal);
        $set('ppn_nominal', $ppnNominal);
        $set('total_harga', $totalHarga);
        
        // Set payment fields based on method
        $paymentMethod = $get('payment_method');
        if ($paymentMethod === 'cash') {
            $set('terbayar', $totalHarga);
            $set('sisa_pembayaran', 0);
            $set('status', 'lunas');
        } else {
            $set('terbayar', 0);
            $set('sisa_pembayaran', $totalHarga);
            $set('status', 'belum_lunas');
        }
    }
}
