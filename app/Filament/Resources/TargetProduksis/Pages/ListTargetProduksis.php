<?php

namespace App\Filament\Resources\TargetProduksis\Pages;

use App\Filament\Resources\TargetProduksis\TargetProduksiResource;
use App\Services\TargetProduksiService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\IconSize;

class ListTargetProduksis extends ListRecords
{
    protected static string $resource = TargetProduksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('dashboard')
                ->label('Dashboard Ringkasan')
                ->icon('heroicon-o-chart-pie')
                ->iconSize(IconSize::Small)
                ->color('info')
                ->modalHeading('Dashboard Target Produksi')
                ->modalContent(function () {
                    $tahun = request()->get('tableFilters')['tahun']['value'] ?? now()->year;
                    $service = app(TargetProduksiService::class);
                    $summary = $service->getDashboardSummary($tahun);
                    
                    return view('filament.modals.target-dashboard', [
                        'summary' => $summary,
                        'tahun' => $tahun,
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),

            Actions\CreateAction::make()
                ->label('Buat Target Produksi')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Bisa ditambahkan widget statistik di sini
        ];
    }
}
