<?php

namespace App\Filament\Resources\Asets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AsetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama_aset'),
                TextEntry::make('jenis'),
                TextEntry::make('nilai')->numeric(),
                TextEntry::make('tanggal_beli')->date()->placeholder('-'),
                TextEntry::make('keterangan')->placeholder('-')->columnSpanFull(),
                TextEntry::make('created_at')->dateTime()->placeholder('-'),
                TextEntry::make('updated_at')->dateTime()->placeholder('-'),
            ]);
    }
}
