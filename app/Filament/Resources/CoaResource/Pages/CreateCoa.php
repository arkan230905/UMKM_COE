<?php
namespace App\Filament\Resources\CoaResource\Pages;

use App\Filament\Resources\CoaResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\Coa;

class CreateCoa extends CreateRecord
{
    protected static string $resource = CoaResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validasi kode akun tidak duplikat
        if (!empty($data['kode_akun'])) {
            if (!Coa::validateUniqueKodeAkun($data['kode_akun'])) {
                Notification::make()
                    ->title('Error')
                    ->body('Kode akun "' . $data['kode_akun'] . '" sudah ada. Silakan gunakan kode akun yang berbeda.')
                    ->danger()
                    ->send();
                
                $this->halt();
            }
        }
        
        // Set default values jika tidak ada
        if (empty($data['saldo_awal'])) {
            $data['saldo_awal'] = 0;
        }
        
        if (empty($data['tanggal_saldo_awal'])) {
            $data['tanggal_saldo_awal'] = now();
        }
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        $record = $this->record;
        $prefix = substr($record->kode_akun, 0, 1);
        $sameGroupCount = Coa::byPrefix($prefix)->count();
        
        Notification::make()
            ->title('COA berhasil dibuat')
            ->body("Akun {$record->kode_akun} - {$record->nama_akun} telah ditambahkan. Total akun dengan prefix {$prefix}: {$sameGroupCount}")
            ->success()
            ->send();
    }
}
