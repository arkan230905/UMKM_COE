<?php

namespace App\Filament\Resources\TargetProduksis\Schemas;

use App\Models\Produk;
use App\Models\TargetProduksi;
use App\Models\TargetProduksiDetail;
use App\Services\TargetProduksiService;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;

class TargetProduksiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Target Produksi')
                    ->description('Masukkan informasi dasar target produksi')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('tahun')
                                    ->label('Tahun Target')
                                    ->required()
                                    ->options(self::getYearOptions())
                                    ->default(now()->year)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        self::validateUniqueness($state, $get, $set);
                                    })
                                    ->helperText('Pilih tahun untuk target produksi'),

                                Select::make('produk_id')
                                    ->label('Produk')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->options(fn() => Produk::where('user_id', auth()->id())
                                        ->pluck('nama_produk', 'id'))
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        self::validateUniqueness($get('tahun'), $get, $set);
                                    })
                                    ->helperText('Pilih produk yang akan ditargetkan'),

                                TextInput::make('total_target_tahunan')
                                    ->label('Total Target Tahunan')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(0)
                                    ->reactive()
                                    ->suffix('Unit')
                                    ->helperText('Total target produksi untuk 1 tahun'),
                            ]),
                    ]),

                Section::make('Distribusi Target Bulanan')
                    ->description('Tentukan target produksi untuk setiap bulan')
                    ->headerActions([
                        Action::make('generate')
                            ->label('Generate Otomatis')
                            ->icon('heroicon-o-sparkles')
                            ->iconSize(IconSize::Small)
                            ->color('info')
                            ->modalHeading('Generate Target Otomatis')
                            ->modalDescription('Pilih metode untuk generate target bulanan secara otomatis')
                            ->modalSubmitActionLabel('Generate')
                            ->form([
                                Select::make('method')
                                    ->label('Metode Generate')
                                    ->required()
                                    ->options([
                                        'merata' => 'Dibagi Rata (12 bulan sama)',
                                        'persentase' => 'Berdasarkan Persentase (distribusi normal)',
                                        'histori' => 'Berdasarkan Histori Tahun Sebelumnya',
                                    ])
                                    ->default('merata')
                                    ->reactive(),

                                Select::make('previous_year')
                                    ->label('Tahun Histori')
                                    ->visible(fn(Get $get) => $get('method') === 'histori')
                                    ->options(function (Get $get) {
                                        $produkId = $get('../../produk_id');
                                        if (!$produkId) {
                                            return [];
                                        }
                                        $service = app(TargetProduksiService::class);
                                        $years = $service->getAvailableYears($produkId);
                                        return array_combine($years, $years);
                                    })
                                    ->helperText('Pilih tahun sebagai referensi histori'),
                            ])
                            ->action(function (array $data, Get $get, Set $set) {
                                $totalTarget = (int) $get('total_target_tahunan');
                                if ($totalTarget <= 0) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Total target tahunan harus diisi terlebih dahulu')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                $service = app(TargetProduksiService::class);
                                $targets = $service->generateAutoTarget(
                                    $totalTarget,
                                    $data['method'],
                                    $data['previous_year'] ?? null
                                );

                                $set('details', $targets);

                                \Filament\Notifications\Notification::make()
                                    ->title('Target bulanan berhasil di-generate')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->schema([
                        Repeater::make('details')
                            ->label('')
                            ->relationship('details')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('bulan')
                                            ->label('Bulan')
                                            ->required()
                                            ->options(self::getMonthOptions())
                                            ->disabled(fn(?TargetProduksiDetail $record) => 
                                                $record ? $record->isLocked() : false
                                            )
                                            ->dehydrated(),

                                        TextInput::make('target_bulanan')
                                            ->label('Target')
                                            ->required()
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->suffix('Unit')
                                            ->disabled(fn(?TargetProduksiDetail $record) => 
                                                $record ? $record->isLocked() : false
                                            )
                                            ->reactive()
                                            ->dehydrated(),
                                    ]),
                            ])
                            ->defaultItems(12)
                            ->minItems(12)
                            ->maxItems(12)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull()
                            ->default(self::getDefaultMonths()),

                        Placeholder::make('validation_summary')
                            ->label('')
                            ->content(function (Get $get) {
                                $totalTarget = (int) ($get('total_target_tahunan') ?? 0);
                                $details = $get('details') ?? [];
                                
                                $totalBulanan = 0;
                                foreach ($details as $detail) {
                                    $totalBulanan += (int) ($detail['target_bulanan'] ?? 0);
                                }

                                $isValid = $totalBulanan === $totalTarget;
                                $difference = $totalBulanan - $totalTarget;

                                if ($isValid) {
                                    return view('filament.components.validation-success', [
                                        'message' => "✓ Total target bulanan sesuai: " . number_format($totalBulanan, 0, ',', '.') . " Unit"
                                    ]);
                                } else {
                                    $diffText = $difference > 0 
                                        ? 'Kelebihan: ' . number_format(abs($difference), 0, ',', '.') 
                                        : 'Kekurangan: ' . number_format(abs($difference), 0, ',', '.');
                                    
                                    return view('filament.components.validation-error', [
                                        'message' => "✗ Total belum sesuai. {$diffText} Unit"
                                    ]);
                                }
                            })
                            ->reactive()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Get year options
     */
    private static function getYearOptions(): array
    {
        $currentYear = now()->year;
        $years = [];
        
        for ($i = $currentYear - 2; $i <= $currentYear + 5; $i++) {
            $years[$i] = $i;
        }
        
        return $years;
    }

    /**
     * Get month options
     */
    private static function getMonthOptions(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    /**
     * Get default months
     */
    private static function getDefaultMonths(): array
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = [
                'bulan' => $i,
                'target_bulanan' => 0,
            ];
        }
        return $months;
    }

    /**
     * Validate uniqueness
     */
    private static function validateUniqueness($tahun, Get $get, Set $set): void
    {
        $produkId = $get('produk_id');
        
        if (!$tahun || !$produkId) {
            return;
        }

        $service = app(TargetProduksiService::class);
        $recordId = $get('id');
        
        if (!$service->isUnique($produkId, $tahun, $recordId)) {
            \Filament\Notifications\Notification::make()
                ->title('Target produksi untuk produk ini pada tahun tersebut sudah ada')
                ->warning()
                ->send();
        }
    }
}
