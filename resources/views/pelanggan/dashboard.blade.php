@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">Katalog Produk</h2>
    </div>

    <form action="{{ route('pelanggan.dashboard') }}" method="GET" class="mb-3">
        <div class="d-flex justify-content-center">
            <div class="input-group" style="max-width: 560px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" value="{{ $search ?? request('q') }}" class="form-control border-start-0 rounded-end-pill" placeholder="Cari produk favoritmu..." style="box-shadow: 0 4px 12px rgba(0,0,0,.06);">
            </div>
        </div>
    </form>

    @if(($favoriteProduks ?? collect())->count())
    <div class="mb-4">
        <h4 class="text-center text-dark mb-3">Favorit Saya</h4>
        <div class="row justify-content-center">
            @foreach($favoriteProduks as $favP)
            <div class="col-6 col-md-3 col-lg-2 mb-3">
                <div class="card h-100 shadow-sm position-relative">
                    <form action="{{ route('pelanggan.favorites.toggle') }}" method="POST" class="position-absolute" style="top:8px; right:8px; z-index:2;">
                        @csrf
                        <input type="hidden" name="produk_id" value="{{ $favP->id }}">
                        <button type="submit" class="btn btn-light btn-sm rounded-circle">
                            <i class="bi bi-heart-fill text-danger"></i>
                        </button>
                    </form>
                    @if($favP->foto)
                    <img src="{{ asset('storage/' . $favP->foto) }}" class="card-img-top" alt="{{ $favP->nama_produk }}" style="height: 120px; object-fit: cover;">
                    @else
                    <div class="bg-secondary text-white text-center d-flex align-items-center justify-content-center" style="height: 120px;">
                        <i class="bi bi-image" style="font-size: 2rem;"></i>
                    </div>
                    @endif
                    <div class="card-body py-2">
                        <div class="small text-dark fw-semibold">{{ Str::limit($favP->nama_produk, 24) }}</div>
                        <div class="text-primary fw-bold">Rp {{ number_format($favP->harga_jual, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="mb-4">
        <h4 class="text-center text-dark mb-3">Best Seller</h4>
        <div class="row justify-content-center">
            @forelse(($bestSellers ?? collect()) as $b)
            <div class="col-6 col-md-3 col-lg-2 mb-3">
                <div class="card h-100 shadow-sm position-relative">
                    <form action="{{ route('pelanggan.favorites.toggle') }}" method="POST" class="position-absolute" style="top:8px; right:8px; z-index:2;">
                        @csrf
                        <input type="hidden" name="produk_id" value="{{ $b->id }}">
                        @php
                            $isFav = in_array($b->id, $favoriteIds ?? []);
                        @endphp
                        <button type="submit" class="btn btn-light btn-sm rounded-circle">
                            @if($isFav)
                            <i class="bi bi-heart-fill text-danger"></i>
                            @else
                            <i class="bi bi-heart text-danger"></i>
                            @endif
                        </button>
                    </form>
                    @if($b->foto)
                    <img src="{{ asset('storage/' . $b->foto) }}" class="card-img-top" alt="{{ $b->nama_produk }}" style="height: 120px; object-fit: cover;">
                    @else
                    <div class="bg-secondary text-white text-center d-flex align-items-center justify-content-center" style="height: 120px;">
                        <i class="bi bi-image" style="font-size: 2rem;"></i>
                    </div>
                    @endif
                    <div class="card-body py-2">
                        <div class="small text-dark fw-semibold">{{ Str::limit($b->nama_produk, 24) }}</div>
                        <div class="text-primary fw-bold">Rp {{ number_format($b->harga_jual, 0, ',', '.') }}</div>
                        @php
                            $ratingBs = $b->avg_rating ?? 0;
                            $countRatingBs = $b->rating_count ?? 0;
                            $filledBs = floor($ratingBs);
                            $halfBs = ($ratingBs - $filledBs) >= 0.5 ? 1 : 0;
                            $emptyBs = 5 - $filledBs - $halfBs;
                        @endphp
                        @php
                            $filledBs = $filledBs ?? 0;
                            $halfBs = $halfBs ?? 0;
                            $emptyBs = $emptyBs ?? (5 - $filledBs - $halfBs);
                        @endphp
                        <div class="text-warning small">
                            @for($i=0;$i<$filledBs;$i++)<i class="bi bi-star-fill"></i>@endfor
                            @for($i=0;$i<$halfBs;$i++)<i class="bi bi-star-half"></i>@endfor
                            @for($i=0;$i<$emptyBs;$i++)<i class="bi bi-star"></i>@endfor
                            <span class="text-muted">{{ number_format($ratingBs,1) }}{{ $countRatingBs ? ' ('.$countRatingBs.')' : '' }}</span>
                        </div>
                        @if(isset($b->total_terjual))
                        <div class="small text-muted">Terjual: {{ number_format($b->total_terjual,0,',','.') }}</div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center text-muted small">
                Belum ada data Best Seller. Menampilkan produk terbaru saat tersedia.
            </div>
            @endforelse
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <h4 class="text-dark mb-3">Produk</h4>

    <div class="row">
        @forelse($produks as $produk)
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm position-relative">
                <form action="{{ route('pelanggan.favorites.toggle') }}" method="POST" class="position-absolute" style="top:8px; right:8px; z-index:2;">
                    @csrf
                    <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                    @php
                        $isFav = in_array($produk->id, $favoriteIds ?? []);
                    @endphp
                    <button type="submit" class="btn btn-light btn-sm rounded-circle">
                        @if($isFav)
                        <i class="bi bi-heart-fill text-danger"></i>
                        @else
                        <i class="bi bi-heart text-danger"></i>
                        @endif
                    </button>
                </form>
                @if($produk->foto)
                <img src="{{ asset('storage/' . $produk->foto) }}" class="card-img-top" alt="{{ $produk->nama_produk }}" style="height: 200px; object-fit: cover;">
                @else
                <div class="bg-secondary text-white text-center d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                </div>
                @endif
                <div class="card-body">
                    <h5 class="card-title text-dark">{{ $produk->nama_produk }}</h5>
                    <p class="card-text text-muted small">{{ Str::limit($produk->deskripsi ?? 'Produk berkualitas', 80) }}</p>
                    <p class="fw-bold text-primary fs-5">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</p>
                    @php
                        $ratingPd = $produk->avg_rating ?? 0;
                        $countRatingPd = $produk->rating_count ?? 0;
                        $filledPd = floor($ratingPd);
                        $halfPd = ($ratingPd - $filledPd) >= 0.5 ? 1 : 0;
                        $emptyPd = 5 - $filledPd - $halfPd;
                    @endphp
                    @php
                        $filledPd = $filledPd ?? 0;
                        $halfPd = $halfPd ?? 0;
                        $emptyPd = $emptyPd ?? (5 - $filledPd - $halfPd);
                    @endphp
                    <div class="text-warning small mb-2">
                        @for($i=0;$i<$filledPd;$i++)<i class="bi bi-star-fill"></i>@endfor
                        @for($i=0;$i<$halfPd;$i++)<i class="bi bi-star-half"></i>@endfor
                        @for($i=0;$i<$emptyPd;$i++)<i class="bi bi-star"></i>@endfor
                        <span class="text-muted">{{ number_format($ratingPd,1) }}{{ $countRatingPd ? ' ('.$countRatingPd.')' : '' }}</span>
                    </div>
                    <p class="small text-white">
                        <span class="badge bg-light text-secondary me-1">Stok :</span>
                        @if($produk->stok > 10)
                        <span class="badge bg-success">{{ number_format($produk->stok, 0, ',', '.') }}</span>
                        @elseif($produk->stok > 0)
                        <span class="badge bg-warning">{{ number_format($produk->stok, 0, ',', '.') }}</span>
                        @else
                        <span class="badge bg-danger">Habis</span>
                        @endif
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    @if($produk->stok > 0)
                    <form action="{{ route('pelanggan.cart.store') }}" method="POST" class="d-grid gap-2">
                        @csrf
                        <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                        <div class="input-group input-group-sm">
                            <button class="btn btn-outline-secondary" type="button" onclick="const i=this.nextElementSibling; i.stepDown();">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" name="qty" value="1" min="1" max="{{ $produk->stok }}" class="form-control text-center text-dark">
                            <button class="btn btn-outline-secondary" type="button" onclick="const i=this.previousElementSibling; const m=parseInt(i.max)||1; i.value=Math.min(parseInt(i.value||1)+1,m);">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                        </button>
                    </form>
                    @else
                    <button class="btn btn-secondary w-100" disabled>Stok Habis</button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Belum ada produk tersedia saat ini.
            </div>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center">
        {{ $produks->links() }}
    </div>

    <div class="mt-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="mb-2 text-dark">Contact Us</h5>
                <p class="text-muted mb-3">Ada pertanyaan tentang produk atau pesanan? Hubungi kami melalui WhatsApp.</p>
                @php
                    $wa = $whatsappNumber ?? '';
                    $wa = preg_replace('/[^0-9]/', '', $wa);
                    $waLink = $wa ? 'https://wa.me/'.$wa : null;
                @endphp
                @if($waLink)
                <a href="{{ $waLink }}" target="_blank" class="btn btn-success">
                    <i class="bi bi-whatsapp"></i> Chat via WhatsApp
                </a>
                @else
                <div class="alert alert-warning p-2 d-inline-block">Nomor WhatsApp belum dikonfigurasi.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
