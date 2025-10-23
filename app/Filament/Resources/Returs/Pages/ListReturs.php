<?php

namespace App\Filament\Resources\Returs\Pages;

use App\Filament\Resources\Returs\ReturResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReturs extends ListRecords
{
    protected static string $resource = ReturResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
