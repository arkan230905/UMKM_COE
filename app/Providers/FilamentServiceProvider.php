<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Route;

class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Filament::serving(function () {

            Filament::registerNavigationGroups([

                // Master Data collapsible
                NavigationGroup::make()
                    ->label('Master Data')
                    ->collapsible() // membuat menu bisa di-expand/collapse
                    ->items([
                        NavigationItem::make('Pegawai')
                            ->url($this->safeRoute('filament.resources.pegawais.index', '/dashboard'))
                            ->icon('heroicon-o-user'),

                        NavigationItem::make('Presensi')
                            ->url($this->safeRoute('filament.resources.presensis.index', '/dashboard'))
                            ->icon('heroicon-o-calendar'),

                        NavigationItem::make('Produk')
                            ->url($this->safeRoute('filament.resources.produks.index', '/dashboard'))
                            ->icon('heroicon-o-archive-box'),

                        NavigationItem::make('Retur')
                            ->url($this->safeRoute('filament.resources.returs.index', '/dashboard'))
                            ->icon('heroicon-o-arrow-uturn-left'),

                        NavigationItem::make('Umkm')
                            ->url($this->safeRoute('filament.resources.umkms.index', '/dashboard'))
                            ->icon('heroicon-o-collection'),

                        NavigationItem::make('User')
                            ->url($this->safeRoute('filament.resources.users.index', '/dashboard'))
                            ->icon('heroicon-o-users'),

                        NavigationItem::make('Pembelian')
                            ->url($this->safeRoute('filament.resources.pembelians.index', '/dashboard'))
                            ->icon('heroicon-o-shopping-cart'),

                        NavigationItem::make('Penjualan')
                            ->url($this->safeRoute('filament.resources.penjualans.index', '/dashboard'))
                            ->icon('heroicon-o-currency-dollar'),
                    ]),

                // Laporan tetap terpisah
                NavigationGroup::make()
                    ->label('Laporan')
                    ->items([
                        NavigationItem::make('Laporan Penjualan')
                            ->url($this->safeRoute('filament.pages.laporan-penjualan', '/dashboard'))
                            ->icon('heroicon-o-document-text'),

                        NavigationItem::make('Laporan Pembelian')
                            ->url($this->safeRoute('filament.pages.laporan-pembelian', '/dashboard'))
                            ->icon('heroicon-o-document-text'),

                        NavigationItem::make('Laporan Keuangan')
                            ->url($this->safeRoute('filament.pages.laporan-keuangan', '/dashboard'))
                            ->icon('heroicon-o-document-text'),
                    ]),
            ]);
        });
    }

    private function safeRoute(string $name, string $fallback): string
    {
        return Route::has($name) ? route($name) : $fallback;
    }
}
