<?php

namespace App\Filament\Resources\Returs;

use App\Filament\Resources\Returs\Pages\CreateRetur;
use App\Filament\Resources\Returs\Pages\EditRetur;
use App\Filament\Resources\Returs\Pages\ListReturs;
use App\Filament\Resources\Returs\Schemas\ReturForm;
use App\Filament\Resources\Returs\Tables\RetursTable;
use App\Models\Retur;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReturResource extends Resource
{
    protected static ?string $model = Retur::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReturForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RetursTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReturs::route('/'),
            'create' => CreateRetur::route('/create'),
            'edit' => EditRetur::route('/{record}/edit'),
        ];
    }
}
