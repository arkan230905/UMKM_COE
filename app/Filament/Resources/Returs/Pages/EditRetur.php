<?php

namespace App\Filament\Resources\Returs\Pages;

use App\Filament\Resources\Returs\ReturResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRetur extends EditRecord
{
    protected static string $resource = ReturResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
