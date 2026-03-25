<?php

namespace App\Filament\Resources\Pembelians\Pages;

use App\Filament\Resources\Pembelians\PembelianResource;
use App\Models\StockMovement;
use App\Models\StockLayer;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePembelian extends CreateRecord
{
    protected static string $resource = PembelianResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pembelian berhasil dibuat';
    }

    protected function afterCreate(): void
    {
        $pembelian = $this->record;
        
        // Update stock for each detail
        foreach ($pembelian->pembelianDetails as $detail) {
            $this->updateStock($detail);
        }

        Notification::make()
            ->title('Pembelian berhasil dibuat')
            ->body("Nomor pembelian: {$pembelian->nomor_pembelian}")
            ->success()
            ->send();
    }

    private function updateStock($detail): void
    {
        $itemType = 'material';
        $itemId = $detail->bahan_baku_id ?? $detail->bahan_pendukung_id;
        $jumlahSatuanUtama = $detail->jumlah * ($detail->faktor_konversi ?? 1);
        
        // Create stock movement
        StockMovement::create([
            'item_type' => $itemType,
            'item_id' => $itemId,
            'direction' => 'in',
            'qty' => $jumlahSatuanUtama,
            'unit_cost' => $detail->harga_satuan / ($detail->faktor_konversi ?? 1),
            'remaining_qty' => $jumlahSatuanUtama,
            'tanggal' => $detail->pembelian->tanggal,
            'keterangan' => 'Pembelian #' . $detail->pembelian->nomor_pembelian,
        ]);

        // Update or create stock layer
        $stockLayer = StockLayer::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->first();

        if ($stockLayer) {
            $stockLayer->remaining_qty += $jumlahSatuanUtama;
            $stockLayer->save();
        } else {
            StockLayer::create([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'remaining_qty' => $jumlahSatuanUtama,
                'unit_cost' => $detail->harga_satuan / ($detail->faktor_konversi ?? 1),
            ]);
        }
    }
}
