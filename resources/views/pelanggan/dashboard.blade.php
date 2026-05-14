@extends('layouts.pelanggan')
@section('content')
<div style="background: linear-gradient(135deg, #f5e6d3 0%, #e8d4c0 100%); padding: 4rem 2rem; min-height: 100vh;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <!-- Hero Section -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center; margin-bottom: 3rem;">
            <div>
                <div style="color: #a0826d; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.5rem;">Selamat Datang!</div>
                <h1 style="font-size: 2.8rem; font-weight: 700; color: #2d3748; margin-bottom: 1rem; line-height: 1.1;">
                    Selamat Datang di<br><span style="color: #a0826d;">UMKM COE</span>
                </h1>
                <p style="font-size: 0.95rem; color: #666; margin-bottom: 2rem; line-height: 1.6;">
                    Temukan produk berkualitas terbaik dengan harga terjangkau. Belanja sekarang dan nikmati pengalaman berbelanja yang menyenangkan!
                </p>
                <div style="display: flex; gap: 1rem;">
                    <a href="#products" style="padding: 0.75rem 1.8rem; background: #8b6f47; color: white; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                        Mulai Belanja
                    </a>
                    <a href="{{ route('pelanggan.cart') }}" style="padding: 0.75rem 1.8rem; background: white; color: #8b6f47; border: 2px solid #8b6f47; border-radius: 50px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                        Keranjang Saya
                    </a>
                </div>
            </div>
            <div style="text-align: center;">
                <img src="https://via.placeholder.com/400x350?text=Produk+Berkualitas" alt="Produk" style="max-width: 100%; height: auto;">
            </div>
        </div>

        <!-- Category Menu -->
        <div style="background: white; padding: 2.5rem 2rem; border-radius: 12px; margin-bottom: 3rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
            <div style="display: flex; justify-content: center; gap: 2.5rem; flex-wrap: wrap;">
                <a href="{{ route('pelanggan.dashboard') }}" style="display: flex; flex-direction: column; align-items: center; gap: 0.8rem; text-decoration: none; color: #666; transition: all 0.3s ease; cursor: pointer;">
                    <div style="width: 70px; height: 70px; background: #f5e6d3; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                        📦
                    </div>
                    <div style="font-size: 0.85rem; font-weight: 600; text-align: center; max-width: 80px;">Semua Kategori</div>
                </a>
                @if($kategoris && $kategoris->count() > 0)
                    @foreach($kategoris as $kat)
                    <a href="{{ route('pelanggan.dashboard', ['kategori' => $kat->id]) }}" style="display: flex; flex-direction: column; align-items: center; gap: 0.8rem; text-decoration: none; color: #666; transition: all 0.3s ease; cursor: pointer;">
                        <div style="width: 70px; height: 70px; background: #f5e6d3; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                            @php
                                $icons = ['MKN' => '🍔', 'MNM' => '🥤', 'SNK' => '🍪', 'FSH' => '👕', 'KRJ' => '🎁', 'KCT' => '💄', 'LNY' => '📦'];
                                echo $icons[$kat->kode_kategori] ?? '📦';
                            @endphp
                        </div>
                        <div style="font-size: 0.85rem; font-weight: 600; text-align: center; max-width: 80px;">{{ $kat->nama }}</div>
                    </a>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Products Section -->
        <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
            <h2 style="font-size: 1.4rem; font-weight: 700; color: #2d3748; margin-bottom: 2rem; text-align: center;">
                @if($kategoriFilter)
                    {{ $kategoris->where('id', $kategoriFilter)->first()->nama ?? 'Produk' }}
                @else
                    Semua Produk
                @endif
            </h2>
            
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" style="margin-bottom: 2rem;">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1.5rem;">
                @if($produks && $produks->count() > 0)
                    @foreach($produks as $produk)
                    <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; border: 1px solid #f0f0f0;">
                        @if($produk->foto)
                        <img src="{{ storage_url($produk->foto) }}" alt="{{ $produk->nama_produk }}" style="width: 100%; height: 160px; object-fit: cover; background: #f5f5f5;">
                        @else
                        <div style="width: 100%; height: 160px; display: flex; align-items: center; justify-content: center; font-size: 2rem; background: #f5f5f5;">📦</div>
                        @endif
                        <div style="padding: 1rem;">
                            <h3 style="font-weight: 600; color: #2d3748; margin-bottom: 0.5rem; font-size: 0.9rem; line-height: 1.3; min-height: 2.6rem;">{{ $produk->nama_produk }}</h3>
                            <div style="font-size: 1rem; font-weight: 700; color: #a0826d; margin-bottom: 1rem;">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</div>
                            <div style="display: flex; gap: 0.5rem;">
                                @if($produk->stok_tersedia > 0)
                                <form action="{{ route('pelanggan.cart.store') }}" method="POST" style="flex: 1;">
                                    @csrf
                                    <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit" style="flex: 1; background: #8b6f47; color: white; border: none; border-radius: 8px; padding: 0.6rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.85rem;">
                                        Tambah
                                    </button>
                                </form>
                                @else
                                <button style="flex: 1; background: #8b6f47; color: white; border: none; border-radius: 8px; padding: 0.6rem; font-weight: 600; cursor: not-allowed; font-size: 0.85rem; opacity: 0.5;" disabled>
                                    Habis
                                </button>
                                @endif
                                <form action="{{ route('pelanggan.favorites.toggle') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                                    <button type="submit" style="width: 40px; background: #f5f5f5; border: none; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; color: #a0826d; font-size: 1rem;" title="Tambah ke favorit">
                                        ♥
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                    <h4 style="color: #999;">Belum ada produk tersedia</h4>
                </div>
                @endif
            </div>

            @if($produks && $produks->hasPages())
            <div style="display: flex; justify-content: center; margin-top: 2rem;">
                {{ $produks->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
