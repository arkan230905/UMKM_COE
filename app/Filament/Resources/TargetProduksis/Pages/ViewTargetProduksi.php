<?php

namespace App\Filament\Resources\TargetProduksis\Pages;

use App\Filament\Resources\TargetProduksis\TargetProduksiResource;
use App\Services\TargetProduksiService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Components\ViewEntry;
use Filament\Schemas\Schema;

class ViewTargetProduksi extends ViewRecord
{
    protected static string $resource = TargetProduksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('grafik')
                ->label('Lihat Grafik')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->modalHeading('Grafik Target vs Realisasi')
                ->modalContent(function () {
                    $service = app(TargetProduksiService::class);
                    $comparison = $service->getComparison($this->record);
                    
                    return view('filament.modals.target-chart', [
                        'target' => $this->record,
                        'comparison' => $comparison,
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),

            Actions\Action::make('audit_log')
                ->label('Riwayat Perubahan')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->modalHeading('Riwayat Perubahan Target Produksi')
                ->modalContent(fn() => view('filament.modals.target-audit-log', [
                    'logs' => $this->record->logs,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),

            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Target Produksi')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tahun')
                                    ->label('Tahun')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('produk.nama_produk')
                                    ->label('Produk'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn() => $this->record->status_color),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('total_target_tahunan')
                                    ->label('Total Target Tahunan')
                                    ->numeric(decimalPlaces: 0)
                                    ->suffix(' Unit'),

                                TextEntry::make('total_realisasi')
                                    ->label('Total Realisasi')
                                    ->numeric(decimalPlaces: 0)
                                    ->suffix(' Unit')
                                    ->color(fn() => $this->record->total_realisasi >= $this->record->total_target_tahunan ? 'success' : 'warning'),

                                TextEntry::make('persentase_pencapaian')
                                    ->label('Persentase Pencapaian')
                                    ->suffix('%')
                                    ->badge()
                                    ->color(fn() => match(true) {
                                        $this->record->persentase_pencapaian >= 100 => 'success',
                                        $this->record->persentase_pencapaian >= 80 => 'info',
                                        $this->record->persentase_pencapaian >= 60 => 'warning',
                                        default => 'danger',
                                    }),
                            ]),
                    ]),

                Section::make('Distribusi Bulanan')
                    ->schema([
                        ViewEntry::make('monthly_details')
                            ->label('')
                            ->view('filament.infolists.target-monthly-table')
                            ->state(function () {
                                $service = app(TargetProduksiService::class);
                                return $service->getComparison($this->record);
                            }),
                    ]),

                Section::make('Informasi Tambahan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('creator.name')
                                    ->label('Dibuat Oleh'),

                                TextEntry::make('created_at')
                                    ->label('Tanggal Dibuat')
                                    ->dateTime('d F Y H:i'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
