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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Fix pembelian_details calculations
        if (isset($data['pembelian_details'])) {
            foreach ($data['pembelian_details'] as $index => &$detail) {
                $jumlah = (float) ($detail['jumlah'] ?? 0);
                $harga_satuan = (float) ($detail['harga_satuan'] ?? 0);
                $detail['subtotal'] = $jumlah * $harga_satuan;
            }
        }
        
        // Recalculate totals
        $subtotal = 0;
        if (isset($data['pembelian_details'])) {
            foreach ($data['pembelian_details'] as $detail) {
                $subtotal += (float) ($detail['subtotal'] ?? 0);
            }
        }
        
        $biaya_kirim = (float) ($data['biaya_kirim'] ?? 0);
        $ppn_persen = (float) ($data['ppn_persen'] ?? 0);
        $ppn_nominal = ($subtotal + $biaya_kirim) * ($ppn_persen / 100);
        $total_harga = $subtotal + $biaya_kirim + $ppn_nominal;
        
        $data['subtotal'] = $subtotal;
        $data['ppn_nominal'] = $ppn_nominal;
        $data['total_harga'] = $total_harga;
        
        return $data;
    }

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
