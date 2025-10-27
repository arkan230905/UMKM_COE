<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsetResource\Pages\CreateAset;
use App\Filament\Resources\AsetResource\Pages\EditAset;
use App\Filament\Resources\AsetResource\Pages\ListAsets;
use App\Models\Aset;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\DateInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

class AsetResource extends Resource
{
    protected static ?string $model = Aset::class;
    protected static ?string $navigationLabel = 'Aset';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('nama')
                    ->label('Nama Aset')
                    ->required(),
                TextInput::make('kategori')
                    ->label('Kategori')
                    ->required(),
                TextInput::make('harga')
                    ->label('Harga')
                    ->numeric()
                    ->required(),
                DateInput::make('tanggal_beli')
                    ->label('Tanggal Beli')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->sortable(),
                TextColumn::make('harga')
                    ->label('Harga')
                    ->money('idr')
                    ->sortable(),
                TextColumn::make('tanggal_beli')
                    ->label('Tanggal Beli')
                    ->date()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAsets::route('/'),
            'create' => CreateAset::route('/create'),
            'edit' => EditAset::route('/{record}/edit'),
        ];
    }
}
