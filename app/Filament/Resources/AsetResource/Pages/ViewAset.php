<?php

namespace App\Filament\Resources\AsetResource\Pages;

use App\Filament\Resources\AsetResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewAset extends ViewRecord
{
    protected static string $resource = AsetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
