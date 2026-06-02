<?php

namespace App\Filament\Resources\Assets\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_asset')->required(),
                TextInput::make('jenis')->required(),
                TextInput::make('nilai')->required()->numeric(),
                DatePicker::make('tanggal_beli')->required(),
                Textarea::make('keterangan')->columnSpanFull()->default(null),
            ]);
    }
}
