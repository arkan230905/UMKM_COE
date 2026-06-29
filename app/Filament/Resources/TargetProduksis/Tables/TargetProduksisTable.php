<?php

namespace App\Filament\Resources\TargetProduksis\Tables;

use App\Models\TargetProduksi;
use App\Services\TargetProduksiService;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TargetProduksisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('produk.nama_produk')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->description(fn(TargetProduksi $record) => 
                        'Kode: ' . $record->produk->kode_produk
                    ),

                TextColumn::make('total_target_tahunan')
                    ->label('Total Target')
                    ->numeric(decimalPlaces: 0)
                    ->sortable()
                    ->suffix(' Unit')
                    ->alignEnd(),

                TextColumn::make('total_realisasi')
                    ->label('Realisasi')
                    ->numeric(decimalPlaces: 0)
                    ->sortable()
                    ->suffix(' Unit')
                    ->alignEnd()
                    ->color(fn(TargetProduksi $record) => 
                        $record->total_realisasi >= $record->total_target_tahunan 
                            ? 'success' 
                            : 'warning'
                    ),

                TextColumn::make('persentase_pencapaian')
                    ->label('Pencapaian')
                    ->badge()
                    ->suffix('%')
                    ->color(fn(TargetProduksi $record) => match(true) {
                        $record->persentase_pencapaian >= 100 => 'success',
                        $record->persentase_pencapaian >= 80 => 'info',
                        $record->persentase_pencapaian >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(TargetProduksi $record) => $record->status_color)
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(self::getYearFilterOptions())
                    ->default(now()->year),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Belum Dimulai' => 'Belum Dimulai',
                        'Aktif' => 'Aktif',
                        'Selesai' => 'Selesai',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Detail'),
                
                EditAction::make()
                    ->label('Edit'),
                
                Action::make('distribusi')
                    ->label('Distribusi')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalHeading(fn(TargetProduksi $record) => 
                        'Distribusi Bulanan - ' . $record->produk->nama_produk . ' (' . $record->tahun . ')'
                    )
                    ->modalContent(fn(TargetProduksi $record) => 
                        view('filament.modals.target-distribusi', [
                            'target' => $record,
                            'comparison' => app(TargetProduksiService::class)->getComparison($record),
                        ])
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                
                DeleteAction::make()
                    ->before(function (TargetProduksi $record, DeleteAction $action) {
                        $service = app(TargetProduksiService::class);
                        $validation = $service->canDelete($record);
                        
                        if (!$validation['can_delete']) {
                            \Filament\Notifications\Notification::make()
                                ->title('Tidak Dapat Dihapus')
                                ->body($validation['message'])
                                ->danger()
                                ->send();
                            
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                // Bulk actions disabled karena kompleksitas validasi
            ])
            ->defaultSort('tahun', 'desc');
    }

    /**
     * Get year filter options
     */
    private static function getYearFilterOptions(): array
    {
        $currentYear = now()->year;
        $years = [];
        
        for ($i = $currentYear - 5; $i <= $currentYear + 2; $i++) {
            $years[$i] = $i;
        }
        
        return $years;
    }
}
