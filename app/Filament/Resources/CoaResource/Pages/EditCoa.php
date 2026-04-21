<?php
namespace App\Filament\Resources\CoaResource\Pages;

use App\Filament\Resources\CoaResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Models\Coa;

class EditCoa extends EditRecord
{
    protected static string $resource = CoaResource::class;
    
    protected static ?string $title = 'Edit COA';
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validasi kode akun tidak duplikat (kecuali record ini sendiri)
        if (!empty($data['kode_akun'])) {
            if (!Coa::validateUniqueKodeAkun($data['kode_akun'], $this->record->kode_akun)) {
                Notification::make()
                    ->title('Error')
                    ->body('Kode akun "' . $data['kode_akun'] . '" sudah ada. Silakan gunakan kode akun yang berbeda.')
                    ->danger()
                    ->send();
                
                $this->halt();
            }
        }
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        $record = $this->record;
        $prefix = substr($record->kode_akun, 0, 1);
        
        Notification::make()
            ->title('COA berhasil diupdate')
            ->body("Akun {$record->kode_akun} - {$record->nama_akun} telah diperbarui.")
            ->success()
            ->send();
    }
}
