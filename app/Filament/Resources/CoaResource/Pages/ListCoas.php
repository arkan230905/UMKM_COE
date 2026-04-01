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
        // Custom ordering untuk menangani kode akun dengan titik dan tanpa titik
        return static::getResource()::getEloquentQuery()
            ->orderByRaw("
                CASE 
                    WHEN kode_akun REGEXP '^[0-9]+$' THEN CAST(kode_akun AS UNSIGNED)
                    WHEN kode_akun REGEXP '^[0-9]+\\.[0-9]+$' THEN CAST(SUBSTRING_INDEX(kode_akun, '.', 1) AS UNSIGNED) * 1000 + CAST(SUBSTRING_INDEX(kode_akun, '.', -1) AS UNSIGNED)
                    ELSE 999999
                END ASC,
                LENGTH(kode_akun) ASC,
                kode_akun ASC
            ");
    }
    
    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [25, 50, 100, 'all'];
    }
}
