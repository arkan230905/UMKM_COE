<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Models\Asset;
use App\Models\Coa;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Aset';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
                Forms\Components\TextInput::make('nama_aset')->required()->maxLength(255),
                Forms\Components\DatePicker::make('tanggal_perolehan')->required(),
                Forms\Components\TextInput::make('harga_perolehan')->numeric()->required()->prefix('Rp '),
                Forms\Components\TextInput::make('nilai_sisa')->numeric()->required()->prefix('Rp '),
                Forms\Components\TextInput::make('umur_ekonomis')->numeric()->required()->suffix('tahun'),
                Forms\Components\Select::make('expense_coa_id')
                    ->label('COA Beban Penyusutan')
                    ->options(fn () => Coa::pluck('nama_akun', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('accum_depr_coa_id')
                    ->label('COA Akumulasi Penyusutan')
                    ->options(fn () => Coa::pluck('nama_akun', 'id'))
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_aset')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('tanggal_perolehan')->date()->sortable(),
                Tables\Columns\TextColumn::make('harga_perolehan')->numeric(decimalPlaces: 2)->prefix('Rp ')->sortable(),
                Tables\Columns\TextColumn::make('nilai_sisa')->numeric(decimalPlaces: 2)->prefix('Rp '),
                Tables\Columns\TextColumn::make('umur_ekonomis'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Asset $record) => !$record->locked),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}
