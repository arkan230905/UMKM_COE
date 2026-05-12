<?php

namespace App\Filament\Resources\AsetResource\Pages;

use App\Filament\Resources\AsetResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditAset extends EditRecord
{
    protected static string $resource = AsetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
