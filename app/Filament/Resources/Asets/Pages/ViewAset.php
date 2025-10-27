<?php

namespace App\Filament\Resources\AsetResource\Pages;

use App\Filament\Resources\AsetResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;

class ViewAset extends ViewRecord
{
    protected static string $resource = AsetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Edit'),
            Actions\DeleteAction::make()->label('Hapus'),
        ];
    }

    protected function getInfolist(): Infolist
    {
        return Infolist::make()
            ->schema([
                Section::make('Informasi Aset')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('nama_aset')->label('Nama Aset'),
                            TextEntry::make('jenis')->label('Jenis Aset'),
                            TextEntry::make('nilai')->label('Nilai')->money('idr', true),
                            TextEntry::make('tanggal_beli')->label('Tanggal Beli')->date('d F Y'),
                        ]),
                        TextEntry::make('keterangan')->label('Keterangan')->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Informasi Sistem')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('created_at')->label('Dibuat Pada')->dateTime('d F Y, H:i'),
                            TextEntry::make('updated_at')->label('Terakhir Diupdate')->dateTime('d F Y, H:i'),
                        ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
