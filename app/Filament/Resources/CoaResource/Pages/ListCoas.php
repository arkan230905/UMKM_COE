<?php
namespace App\Filament\Resources\CoaResource\Pages;

use App\Filament\Resources\CoaResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCoas extends ListRecords
{
    protected static string $resource = CoaResource::class;
    
    protected function getTableQuery(): Builder
    {
        // Ensure proper ordering by kode_akun
        return static::getResource()::getEloquentQuery()
            ->orderByRaw('CAST(kode_akun AS UNSIGNED) ASC, kode_akun ASC');
    }
    
    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [25, 50, 100, 'all'];
    }
}
