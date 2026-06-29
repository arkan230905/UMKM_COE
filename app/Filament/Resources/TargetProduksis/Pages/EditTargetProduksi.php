<?php

namespace App\Filament\Resources\TargetProduksis\Pages;

use App\Filament\Resources\TargetProduksiResource;
use App\Models\TargetProduksiDetail;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTargetProduksi extends EditRecord
{
    protected static string $resource = TargetProduksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Detail'),
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action) {
                    if ($this->record->hasProductions()) {
                        \Filament\Notifications\Notification::make()
                            ->title('Tidak Dapat Dihapus')
                            ->body('Target produksi ini tidak dapat dihapus karena sudah digunakan dalam transaksi produksi')
                            ->danger()
                            ->send();
                        
                        $action->cancel();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load details untuk edit
        if (isset($data['id'])) {
            $details = TargetProduksiDetail::where('target_produksi_id', $data['id'])
                ->orderBy('bulan')
                ->get()
                ->map(fn($detail) => [
                    'id' => $detail->id,
                    'bulan' => $detail->bulan,
                    'target_bulanan' => $detail->target_bulanan,
                ])
                ->toArray();
            
            $data['details'] = $details;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validasi total target bulanan
        $totalBulanan = 0;
        if (isset($data['details']) && is_array($data['details'])) {
            foreach ($data['details'] as $detail) {
                $totalBulanan += (int) ($detail['target_bulanan'] ?? 0);
            }
        }

        $totalTarget = (int) ($data['total_target_tahunan'] ?? 0);

        if ($totalBulanan !== $totalTarget) {
            \Filament\Notifications\Notification::make()
                ->title('Validasi Gagal')
                ->body('Total target bulanan harus sama dengan total target tahunan')
                ->danger()
                ->persistent()
                ->send();
            
            $this->halt();
        }

        // Validasi locked period
        if (isset($data['details']) && is_array($data['details'])) {
            foreach ($data['details'] as $detail) {
                if (isset($detail['id'])) {
                    $existingDetail = TargetProduksiDetail::find($detail['id']);
                    if ($existingDetail && $existingDetail->isLocked()) {
                        if ($existingDetail->target_bulanan != $detail['target_bulanan']) {
                            \Filament\Notifications\Notification::make()
                                ->title('Periode Terkunci')
                                ->body('Target produksi pada periode ini telah dikunci sehingga tidak dapat diubah')
                                ->danger()
                                ->persistent()
                                ->send();
                            
                            $this->halt();
                        }
                    }
                }
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        \Filament\Notifications\Notification::make()
            ->title('Target Produksi Berhasil Diperbarui')
            ->body('Data target produksi telah berhasil diperbarui')
            ->success()
            ->send();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Target produksi berhasil diperbarui';
    }
}
