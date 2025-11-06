<?php
namespace App\Filament\Resources;

use App\Filament\Resources\JabatanResource\Pages;
use App\Models\Jabatan;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class JabatanResource extends Resource
{
    protected static ?string $model = Jabatan::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Jabatan';
    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama')->label('Nama Jabatan')->required()->maxLength(255),
            Forms\Components\Select::make('kategori')->options(['btkl'=>'BTKL','btktl'=>'BTKTL'])->required()->label('Kategori'),
            Forms\Components\TextInput::make('tunjangan')->numeric()->default(0)->prefix('Rp '),
            Forms\Components\TextInput::make('asuransi')->numeric()->default(0)->prefix('Rp '),
            Forms\Components\TextInput::make('gaji')->numeric()->default(0)->prefix('Rp ')
                ->helperText('BTKTL: gaji per bulan. BTKL: isi 0, gunakan tarif/jam.'),
            Forms\Components\TextInput::make('tarif')->numeric()->default(0)->prefix('Rp ')
                ->helperText('Tarif per jam untuk BTKL. BTKTL: isi 0.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')->label('Nama Jabatan')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('kategori')->badge()->sortable()
                    ->formatStateUsing(fn($s) => strtoupper($s)),
                Tables\Columns\TextColumn::make('tunjangan')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('asuransi')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('gaji')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('tarif')->money('IDR')->sortable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJabatans::route('/'),
            'create' => Pages\CreateJabatan::route('/create'),
            'edit' => Pages\EditJabatan::route('/{record}/edit'),
        ];
    }
}
