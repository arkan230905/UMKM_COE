<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Models\Asset;
use App\Models\Coa;
use App\Models\KategoriAsset;
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
            Forms\Components\TextInput::make('kode_asset')
                ->label('Kode Aset')
                ->disabled()
                ->dehydrated(false),
            Forms\Components\TextInput::make('nama_asset')
                ->label('Nama Aset')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('kategori_asset_id')
                ->label('Kategori Aset')
                ->relationship('kategori', 'nama')
                ->searchable()
                ->required(),
            Forms\Components\DatePicker::make('tanggal_perolehan')
                ->label('Tanggal Perolehan')
                ->required(),
            Forms\Components\TextInput::make('harga_perolehan')
                ->label('Harga Perolehan')
                ->numeric()
                ->required()
                ->prefix('Rp '),
            Forms\Components\TextInput::make('nilai_residu')
                ->label('Nilai Residu')
                ->numeric()
                ->required()
                ->prefix('Rp '),
            Forms\Components\TextInput::make('umur_manfaat')
                ->label('Umur Manfaat')
                ->numeric()
                ->required()
                ->suffix(' tahun'),
            Forms\Components\Select::make('metode_penyusutan')
                ->label('Metode Penyusutan')
                ->options([
                    'straight_line' => 'Garis Lurus',
                    'declining_balance' => 'Saldo Menurun',
                ])
                ->default('straight_line')
                ->required(),
            Forms\Components\Select::make('asset_coa_id')
                ->label('COA Aset')
                ->options(fn () => Coa::pluck('nama_akun', 'id'))
                ->searchable()
                ->required(),
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
            Forms\Components\Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_asset')
                    ->label('Kode Aset')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_asset')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kategori.nama')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_perolehan')
                    ->label('Tanggal Perolehan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_perolehan')
                    ->label('Harga Perolehan')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('Rp ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nilai_residu')
                    ->label('Nilai Residu')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('Rp '),
                Tables\Columns\TextColumn::make('umur_manfaat')
                    ->label('Umur Manfaat')
                    ->suffix(' tahun'),
                Tables\Columns\TextColumn::make('nilai_buku')
                    ->label('Nilai Buku')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('Rp ')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Asset $record) => $record->bisaDihapus()),
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
            'view' => Pages\ViewAsset::route('/{record}'),
        ];
    }
}
