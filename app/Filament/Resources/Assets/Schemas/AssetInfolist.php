<?php

namespace App\Filament\Resources\Assets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AssetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama_asset'),
                TextEntry::make('jenis'),
                TextEntry::make('nilai')->numeric(),
                TextEntry::make('tanggal_beli')->date()->placeholder('-'),
                TextEntry::make('keterangan')->placeholder('-')->columnSpanFull(),
                TextEntry::make('created_at')->dateTime()->placeholder('-'),
                TextEntry::make('updated_at')->dateTime()->placeholder('-'),
            ]);
    }
}
