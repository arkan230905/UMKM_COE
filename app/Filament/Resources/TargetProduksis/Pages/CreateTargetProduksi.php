<?php

namespace App\Filament\Resources\TargetProduksis\Pages;

use App\Filament\Resources\TargetProduksis\TargetProduksiResource;
use App\Services\TargetProduksiService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTargetProduksi extends CreateRecord
{
    protected static string $resource = TargetProduksiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
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

        // Check uniqueness
        $service = app(TargetProduksiService::class);
        if (!$service->isUnique($data['produk_id'], $data['tahun'])) {
            \Filament\Notifications\Notification::make()
                ->title('Data Sudah Ada')
                ->body('Target produksi untuk produk ini pada tahun tersebut sudah ada')
                ->danger()
                ->persistent()
                ->send();
            
            $this->halt();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        \Filament\Notifications\Notification::make()
            ->title('Target Produksi Berhasil Dibuat')
            ->body('Data target produksi telah berhasil disimpan')
            ->success()
            ->send();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Target produksi berhasil dibuat';
    }
}
