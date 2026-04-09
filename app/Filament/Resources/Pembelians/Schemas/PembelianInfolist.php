<?php

namespace App\Filament\Resources\Pembelians\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Infolist;

class PembelianInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detail Pembelian')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('nomor_pembelian')
                                    ->label('Nomor Pembelian'),
                                TextEntry::make('nomor_faktur')
                                    ->label('Nomor Faktur'),
                                TextEntry::make('tanggal')
                                    ->label('Tanggal')
                                    ->date(),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('vendor.nama_vendor')
                                    ->label('Vendor'),
                                TextEntry::make('payment_method')
                                    ->label('Metode Pembayaran')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'cash' => 'success',
                                        'credit' => 'warning',
                                    }),
                            ]),
                    ]),
                
                Section::make('Detail Barang')
                    ->schema([
                        RepeatableEntry::make('pembelianDetails')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('nama_bahan')
                                            ->label('Nama Bahan'),
                                        TextEntry::make('jumlah')
                                            ->label('Jumlah')
                                            ->suffix(fn ($record) => ' ' . $record->satuan),
                                        TextEntry::make('harga_satuan')
                                            ->label('Harga Satuan')
                                            ->money('IDR'),
                                        TextEntry::make('subtotal')
                                            ->label('Subtotal')
                                            ->money('IDR'),
                                    ]),
                                
                                // Additional Conversions Section
                                RepeatableEntry::make('additionalConversions')
                                    ->label('Konversi Tambahan')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('satuan_nama')
                                                    ->label('Satuan Target'),
                                                TextEntry::make('jumlah_konversi')
                                                    ->label('Jumlah Konversi')
                                                    ->suffix(fn ($record) => ' ' . $record->satuan_nama),
                                                TextEntry::make('keterangan')
                                                    ->label('Keterangan')
                                                    ->placeholder('Tidak ada keterangan'),
                                            ]),
                                    ])
                                    ->visible(fn ($record) => $record->additionalConversions->isNotEmpty())
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ]),
                
                Section::make('Total')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('IDR'),
                                TextEntry::make('biaya_kirim')
                                    ->label('Biaya Kirim')
                                    ->money('IDR'),
                                TextEntry::make('ppn_nominal')
                                    ->label('PPN')
                                    ->money('IDR'),
                            ]),
                        
                        TextEntry::make('total_harga')
                            ->label('Total Harga')
                            ->money('IDR')
                            ->size('lg')
                            ->weight('bold'),
                    ]),
            ]);
    }
}