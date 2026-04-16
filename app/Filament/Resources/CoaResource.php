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
    
    // Disable automatic ID column
    protected static bool $shouldRegisterNavigation = true;
    
    /**
     * Get category options based on account type
     */
    protected function getKategoriOptions($tipeAkun): array
    {
        return match($tipeAkun) {
            'Aset', 'Asset' => [
                'Kas & Bank' => 'Kas & Bank',
                'Persediaan' => 'Persediaan',
                'Aset Tetap - Kendaraan' => 'Aset Tetap - Kendaraan',
                'Aset Tetap - Gedung' => 'Aset Tetap - Gedung',
                'Aset Tetap - Peralatan' => 'Aset Tetap - Peralatan',
                'Aset Tetap - Mesin' => 'Aset Tetap - Mesin',
                'Aset Tetap - Lainnya' => 'Aset Tetap - Lainnya',
                'Akumulasi Penyusutan' => 'Akumulasi Penyusutan',
                'Piutang' => 'Piutang',
                'PPN Masukkan' => 'PPN Masukkan',
                'Lainnya' => 'Lainnya'
            ],
            'Kewajiban', 'Liability' => [
                'Hutang Jangka Pendek' => 'Hutang Jangka Pendek',
                'Hutang Jangka Panjang' => 'Hutang Jangka Panjang',
                'Hutang Usaha' => 'Hutang Usaha',
                'Hutang Gaji' => 'Hutang Gaji',
                'PPN Keluaran' => 'PPN Keluaran',
                'Lainnya' => 'Lainnya'
            ],
            'Modal', 'Equity' => [
                'Modal Usaha' => 'Modal Usaha',
                'Modal Disetor' => 'Modal Disetor',
                'Prive' => 'Prive',
                'Laba Ditahan' => 'Laba Ditahan',
                'Lainnya' => 'Lainnya'
            ],
            'Pendapatan', 'Revenue' => [
                'Penjualan Produk' => 'Penjualan Produk',
                'Penjualan Jasa' => 'Penjualan Jasa',
                'Pendapatan Bunga' => 'Pendapatan Bunga',
                'Pendapatan Sewa' => 'Pendapatan Sewa',
                'Retur Penjualan' => 'Retur Penjualan',
                'Diskon Pembelian' => 'Diskon Pembelian',
                'Pendapatan Ongkir' => 'Pendapatan Ongkir',
                'Lainnya' => 'Lainnya'
            ],
            default => [
                'Biaya Bahan Baku' => 'Biaya Bahan Baku',
                'Biaya Tenaga Kerja Langsung' => 'Biaya Tenaga Kerja Langsung',
                'Biaya Overhead Pabrik' => 'Biaya Overhead Pabrik',
                'Biaya Tenaga Kerja Tidak Langsung' => 'Biaya Tenaga Kerja Tidak Langsung',
                'BOP Tidak Langsung Lainnya' => 'BOP Tidak Langsung Lainnya',
                'Lainnya' => 'Lainnya'
            ]
        };
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Dasar')
                ->schema([
                    Forms\Components\TextInput::make('kode_akun')
                        ->label('Kode Akun')
                        ->maxLength(50)
                        ->required()
                        ->unique(Coa::class, 'kode_akun', ignoreRecord: true)
                        ->validationMessages([
                            'unique' => 'Kode akun sudah ada. Silakan gunakan kode akun yang berbeda.',
                        ])
                        ->helperText('Masukkan kode akun unik. Sistem akan mengurutkan berdasarkan kode ini.'),
                    Forms\Components\TextInput::make('nama_akun')
                        ->label('Nama Akun')
                        ->required(),
                    Forms\Components\Select::make('tipe_akun')
                        ->label('Tipe Akun')
                        ->required()
                        ->options([
                            'Aset'=>'Aset',
                            'Asset'=>'Asset',
                            'Kewajiban'=>'Kewajiban',
                            'Liability'=>'Liability',
                            'Modal'=>'Modal',
                            'Equity'=>'Equity',
                            'Pendapatan'=>'Pendapatan',
                            'Revenue'=>'Revenue',
                            'Biaya Bahan Baku'=>'Biaya Bahan Baku',
                            'Biaya Tenaga Kerja Langsung'=>'Biaya Tenaga Kerja Langsung',
                            'Biaya Overhead Pabrik'=>'Biaya Overhead Pabrik',
                            'Biaya Tenaga Kerja Tidak Langsung'=>'Biaya Tenaga Kerja Tidak Langsung',
                            'BOP Tidak Langsung Lainnya'=>'BOP Tidak Langsung Lainnya'
                        ])
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('kategori_akun', $this->getKategoriOptions($state))),
                    Forms\Components\Select::make('kategori_akun')
                        ->label('Kategori Akun')
                        ->options(fn (callable $get) => $this->getKategoriOptions($get('tipe_akun')))
                        ->required()
                        ->helperText('Kategori untuk pengelompokan akun'),
                ])->columns(2),
            
            Forms\Components\Section::make('Pengaturan Akuntansi')
                ->schema([
                    Forms\Components\Select::make('saldo_normal')
                        ->label('Saldo Normal')
                        ->options([
                            'debit' => 'Debit',
                            'credit' => 'Credit'
                        ])
                        ->required()
                        ->helperText('Pilih saldo normal untuk akun ini'),
                    Forms\Components\TextInput::make('saldo_awal')
                        ->label('Saldo Awal')
                        ->numeric()
                        ->disabled(fn ($record) => $record?->posted_saldo_awal)
                        ->helperText('Saldo awal akun ini'),
                    Forms\Components\DatePicker::make('tanggal_saldo_awal')
                        ->label('Tanggal Saldo Awal')
                        ->disabled(fn ($record) => $record?->posted_saldo_awal)
                        ->default(now()),
                    Forms\Components\Toggle::make('posted_saldo_awal')
                        ->label('Saldo Awal Sudah Diposting')
                        ->disabled()
                        ->helperText('Menunjukkan apakah saldo awal sudah diposting ke jurnal'),
                ])->columns(2),
            
            Forms\Components\Section::make('Keterangan')
                ->schema([
                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan')
                        ->maxLength(500)
                        ->helperText('Keterangan tambahan untuk akun ini'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('kode_akun')->label('Kode Akun')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('nama_akun')->label('Nama Akun')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('tipe_akun')->label('Tipe Akun')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Asset' => 'success',
                    'Aset' => 'success',
                    'Liability' => 'warning', 
                    'Kewajiban' => 'warning',
                    'Equity' => 'info',
                    'Modal' => 'info',
                    'Revenue' => 'primary',
                    'Pendapatan' => 'primary',
                    'Biaya Bahan Baku' => 'danger',
                    'Biaya Tenaga Kerja Langsung' => 'danger',
                    'Biaya Overhead Pabrik' => 'danger',
                    'Biaya Tenaga Kerja Tidak Langsung' => 'danger',
                    'BOP Tidak Langsung Lainnya' => 'danger',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('kategori_akun')->label('Kategori Akun'),
            Tables\Columns\TextColumn::make('saldo_normal')->label('Saldo Normal')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'debit' => 'success',
                    'credit' => 'warning',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('saldo_awal')->money('IDR')->label('Saldo Awal'),
            Tables\Columns\TextColumn::make('keterangan')->label('Keterangan')
                ->limit(50)
                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                    $state = $column->getState();
                    if (strlen($state) <= 50) {
                        return null;
                    }
                    return $state;
                }),
        ])
        ->recordTitleAttribute('nama_akun')
        ->defaultSort('kode_akun', 'asc') // Sort by kode_akun ascending
        ->modifyQueryUsing(function ($query) {
            // Custom ordering untuk menangani kode akun dengan titik dan tanpa titik
            return $query->orderByRaw("
                CASE 
                    WHEN kode_akun REGEXP '^[0-9]+$' THEN CAST(kode_akun AS UNSIGNED)
                    WHEN kode_akun REGEXP '^[0-9]+\\.[0-9]+$' THEN CAST(SUBSTRING_INDEX(kode_akun, '.', 1) AS UNSIGNED) * 1000 + CAST(SUBSTRING_INDEX(kode_akun, '.', -1) AS UNSIGNED)
                    ELSE 999999
                END ASC,
                LENGTH(kode_akun) ASC,
                kode_akun ASC
            ");
        })
        ->filters([
            Tables\Filters\SelectFilter::make('tipe_akun')
                ->options([
                    'Aset' => 'Aset',
                    'Asset' => 'Asset',
                    'Kewajiban' => 'Kewajiban',
                    'Liability' => 'Liability', 
                    'Modal' => 'Modal',
                    'Equity' => 'Equity',
                    'Pendapatan' => 'Pendapatan',
                    'Revenue' => 'Revenue',
                    'Biaya Bahan Baku' => 'Biaya Bahan Baku',
                    'Biaya Tenaga Kerja Langsung' => 'Biaya Tenaga Kerja Langsung',
                    'Biaya Overhead Pabrik' => 'Biaya Overhead Pabrik',
                    'Biaya Tenaga Kerja Tidak Langsung' => 'Biaya Tenaga Kerja Tidak Langsung',
                    'BOP Tidak Langsung Lainnya' => 'BOP Tidak Langsung Lainnya',
                ]),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make()
                ->before(function ($record) {
                    // Cek apakah akun ini digunakan dalam transaksi
                    $journalCount = \App\Models\JournalLine::where('coa_id', $record->id)->count();
                    if ($journalCount > 0) {
                        Notification::make()
                            ->title('Tidak dapat menghapus')
                            ->body('Akun ini sudah digunakan dalam transaksi jurnal.')
                            ->danger()
                            ->send();
                        return false;
                    }
                    
                    // Cek apakah akun ini digunakan di bahan_bakus (hanya jika masih ada yang menggunakan)
                    $bahanBakuCount = \Illuminate\Support\Facades\DB::table('bahan_bakus')
                        ->where('coa_persediaan_id', $record->kode_akun)
                        ->orWhere('coa_hpp_id', $record->kode_akun)
                        ->orWhere('coa_pembelian_id', $record->kode_akun)
                        ->count();
                    
                    if ($bahanBakuCount > 0) {
                        // Ambil nama bahan yang masih menggunakan akun ini
                        $bahanNames = \Illuminate\Support\Facades\DB::table('bahan_bakus')
                            ->where('coa_persediaan_id', $record->kode_akun)
                            ->orWhere('coa_hpp_id', $record->kode_akun)
                            ->orWhere('coa_pembelian_id', $record->kode_akun)
                            ->pluck('nama_bahan')
                            ->take(3)
                            ->implode(', ');
                        
                        Notification::make()
                            ->title('Tidak dapat menghapus')
                            ->body("Akun ini masih digunakan oleh bahan baku: {$bahanNames}" . ($bahanBakuCount > 3 ? ' dan lainnya' : '') . ". Ubah referensi COA di bahan baku terlebih dahulu.")
                            ->danger()
                            ->send();
                        return false;
                    }
                    
                    // Cek apakah akun ini digunakan di bahan_pendukungs (hanya jika masih ada yang menggunakan)
                    $bahanPendukungCount = \Illuminate\Support\Facades\DB::table('bahan_pendukungs')
                        ->where('coa_persediaan_id', $record->kode_akun)
                        ->orWhere('coa_hpp_id', $record->kode_akun)
                        ->orWhere('coa_pembelian_id', $record->kode_akun)
                        ->count();
                    
                    if ($bahanPendukungCount > 0) {
                        // Ambil nama bahan yang masih menggunakan akun ini
                        $bahanNames = \Illuminate\Support\Facades\DB::table('bahan_pendukungs')
                            ->where('coa_persediaan_id', $record->kode_akun)
                            ->orWhere('coa_hpp_id', $record->kode_akun)
                            ->orWhere('coa_pembelian_id', $record->kode_akun)
                            ->pluck('nama_bahan')
                            ->take(3)
                            ->implode(', ');
                        
                        Notification::make()
                            ->title('Tidak dapat menghapus')
                            ->body("Akun ini masih digunakan oleh bahan pendukung: {$bahanNames}" . ($bahanPendukungCount > 3 ? ' dan lainnya' : '') . ". Ubah referensi COA di bahan pendukung terlebih dahulu.")
                            ->danger()
                            ->send();
                        return false;
                    }
                    
                    // Cek apakah akun ini digunakan di produks
                    $produkCount = \Illuminate\Support\Facades\DB::table('produks')
                        ->where('coa_persediaan_id', $record->id)
                        ->orWhere('coa_hpp_id', $record->id)
                        ->count();
                    
                    if ($produkCount > 0) {
                        $produkNames = \Illuminate\Support\Facades\DB::table('produks')
                            ->where('coa_persediaan_id', $record->id)
                            ->orWhere('coa_hpp_id', $record->id)
                            ->pluck('nama_produk')
                            ->take(3)
                            ->implode(', ');
                        
                        Notification::make()
                            ->title('Tidak dapat menghapus')
                            ->body("Akun ini masih digunakan oleh produk: {$produkNames}" . ($produkCount > 3 ? ' dan lainnya' : '') . ". Ubah referensi COA di produk terlebih dahulu.")
                            ->danger()
                            ->send();
                        return false;
                    }
                }),
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
