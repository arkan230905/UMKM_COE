<?php

namespace App\Filament\Resources\AsetResource\Pages;

use App\Filament\Resources\AsetResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListAsets extends ListRecords
{
    protected static string $resource = AsetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
