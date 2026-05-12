<?php

namespace App\Filament\Resources\Pembelians\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PembeliansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_pembelian')
                    ->label('Nomor Pembelian')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('nomor_faktur')
                    ->label('Nomor Faktur')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('vendor.nama_vendor')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                
                TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->money('IDR')
                    ->sortable(),
                
                TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'credit' => 'warning',
                        default => 'gray',
                    }),
                
                TextColumn::make('status_pembayaran')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Belum Lunas' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Cash',
                        'credit' => 'Credit',
                    ]),
                
                SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->relationship('vendor', 'nama_vendor')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
