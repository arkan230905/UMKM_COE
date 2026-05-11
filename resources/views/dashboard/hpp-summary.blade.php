<!-- HPP Summary Dashboard Widget -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        HPP Rata-rata
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        @php
                            $avgHpp = \App\Models\Produk::where('user_id', auth()->id())
                                ->where('harga_pokok', '>', 0)
                                ->avg('harga_pokok');
                        @endphp
                        Rp {{ number_format($avgHpp ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-calculator fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Produk dengan HPP
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        @php
                            $countHpp = \App\Models\Produk::where('user_id', auth()->id())
                                ->where('harga_pokok', '>', 0)
                                ->count();
                        @endphp
                        {{ $countHpp }} / {{ \App\Models\Produk::where('user_id', auth()->id())->count() }}
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Margin Rata-rata
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        @php
                            $products = \App\Models\Produk::where('user_id', auth()->id())
                                ->where('harga_jual', '>', 0)
                                ->where('harga_pokok', '>', 0)
                                ->get();
                            $avgMargin = $products->avg(function($product) {
                                return (($product->harga_jual - $product->harga_pokok) / $product->harga_jual) * 100;
                            });
                        @endphp
                        {{ number_format($avgMargin ?? 0, 1) }}%
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-percentage fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-warning shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        <a href="{{ route('hpp.index') }}" class="text-warning">Lihat Semua HPP</a>
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <a href="{{ route('hpp.index') }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>
