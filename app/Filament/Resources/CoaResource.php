<?php
namespace App\Filament\Resources;

use App\Models\Coa;
use App\Services\JournalService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class CoaResource extends Resource
{
    protected static ?string $model = Coa::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'COA';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\TextInput::make('kode_akun')->maxLength(50),
            Forms\Components\TextInput::make('nama_akun')->required(),
            Forms\Components\Select::make('tipe_akun')->required()
                ->options([
                    'Aset'=>'Aset','Kewajiban'=>'Kewajiban','Modal'=>'Modal','Pendapatan'=>'Pendapatan','Beban'=>'Beban'
                ]),
            Forms\Components\Toggle::make('is_akun_header'),
            Forms\Components\Select::make('parent_id')->label('Akun Induk')
                ->options(fn() => Coa::pluck('nama_akun','id')->toArray()),
            Forms\Components\TextInput::make('saldo_awal')->numeric()->label('Saldo Awal')
                ->disabled(fn ($record) => $record?->posted_saldo_awal),
            Forms\Components\DatePicker::make('tanggal_saldo_awal')->label('Tanggal Saldo Awal')
                ->disabled(fn ($record) => $record?->posted_saldo_awal),
            Forms\Components\Toggle::make('posted_saldo_awal')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('kode_akun')->label('Kode')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('nama_akun')->label('Nama')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('tipe_akun')->label('Tipe'),
            Tables\Columns\TextColumn::make('kategori_akun')->label('Kategori'),
            Tables\Columns\IconColumn::make('is_akun_header')->boolean()->label('Header'),
            Tables\Columns\TextColumn::make('kode_induk')->label('Kode Induk')->sortable(),
            Tables\Columns\TextColumn::make('saldo_normal')->label('Saldo Normal'),
            Tables\Columns\TextColumn::make('saldo_awal')->money('IDR')->label('Saldo Awal'),
            Tables\Columns\TextColumn::make('keterangan')->toggleable(isToggledHiddenByDefault: true),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\CoaResource\Pages\ListCoas::route('/'),
            'create' => \App\Filament\Resources\CoaResource\Pages\CreateCoa::route('/create'),
            'edit' => \App\Filament\Resources\CoaResource\Pages\EditCoa::route('/{record}/edit'),
        ];
    }
}
