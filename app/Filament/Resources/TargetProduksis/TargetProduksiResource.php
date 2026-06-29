<?php

namespace App\Filament\Resources\TargetProduksis;

use App\Filament\Resources\TargetProduksis\Pages\CreateTargetProduksi;
use App\Filament\Resources\TargetProduksis\Pages\EditTargetProduksi;
use App\Filament\Resources\TargetProduksis\Pages\ListTargetProduksis;
use App\Filament\Resources\TargetProduksis\Pages\ViewTargetProduksi;
use App\Filament\Resources\TargetProduksis\Schemas\TargetProduksiForm;
use App\Filament\Resources\TargetProduksis\Tables\TargetProduksisTable;
use App\Models\TargetProduksi;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TargetProduksiResource extends Resource
{
    protected static ?string $model = TargetProduksi::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Target Produksi';

    protected static ?string $modelLabel = 'Target Produksi';

    protected static ?string $pluralModelLabel = 'Target Produksi';

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return TargetProduksiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TargetProduksisTable::configure($table);
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
            'index' => ListTargetProduksis::route('/'),
            'create' => CreateTargetProduksi::route('/create'),
            'edit' => EditTargetProduksi::route('/{record}/edit'),
            'view' => ViewTargetProduksi::route('/{record}'),
        ];
    }
}
