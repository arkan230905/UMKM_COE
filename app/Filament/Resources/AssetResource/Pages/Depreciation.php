<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Services\AssetDepreciationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Depreciation extends Page implements HasTable
{
    protected static string $resource = AssetResource::class;

    protected string $view = 'filament.pages.asset-depreciation';

    public ?Asset $record = null;

    use Tables\Concerns\InteractsWithTable;

    public function mount($record): void
    {
        $this->record = Asset::findOrFail($record);
        static::$title = 'Depresiasi: ' . $this->record->nama_aset;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('compute')
                ->label('Hitung Penyusutan')
                ->icon('heroicon-o-calculator')
                ->requiresConfirmation()
                ->action(function (AssetDepreciationService $service) {
                    try {
                        $service->computeAndPost($this->record);
                        Notification::make()
                            ->title('Penyusutan berhasil dihitung dan dicatat')
                            ->success()
                            ->send();
                        $this->resetTable();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal menghitung penyusutan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => AssetDepreciation::query()->where('asset_id', $this->record->id)->orderBy('tahun'))
            ->columns([
                Tables\Columns\TextColumn::make('tahun')->sortable(),
                Tables\Columns\TextColumn::make('beban_penyusutan')->label('Beban Penyusutan')->numeric(decimalPlaces: 2)->prefix('Rp '),
                Tables\Columns\TextColumn::make('akumulasi_penyusutan')->label('Akumulasi Penyusutan')->numeric(decimalPlaces: 2)->prefix('Rp '),
                Tables\Columns\TextColumn::make('nilai_buku_akhir')->label('Nilai Buku Akhir')->numeric(decimalPlaces: 2)->prefix('Rp '),
            ])
            ->paginated(false)
            ->heading('Tabel Penyusutan (Metode Garis Lurus)');
    }
}
