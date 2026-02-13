@extends('layouts.catalog')

@section('title', 'E-Catalog UMKM Desa')

@section('content')

<!-- ================= HERO DESA ================= -->
<div class="hero-desa">
    <div class="overlay"></div>
    <div class="container position-relative text-center text-white">
        <h1 class="fw-bold display-5">UMKM Desa Karangpakuan</h1>
        <p class="lead">Pusat Produk UMKM & Wisata Desa</p>

        <div class="d-flex justify-content-center gap-3 mt-4 flex-wrap">
            <a href="#tentang-desa" class="btn btn-outline-light px-4">Tentang Desa</a>
            <a href="#wisata-desa" class="btn btn-outline-light px-4">Wisata Desa</a>
            <a href="#produk-umkm" class="btn btn-warning px-4">Produk UMKM</a>
        </div>
    </div>
</div>

<!-- ================= TENTANG DESA ================= -->
<section id="tentang-desa" class="section-soft">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-md-6">
                <h2 class="section-title">Tentang Desa Karangpakuan</h2>
                <p>
                    Desa Karangpakuan merupakan desa asri di kaki gunung yang
                    memiliki potensi wisata alam serta produk UMKM unggulan.
                    Produk diproduksi langsung oleh masyarakat desa setiap hari.
                </p>
            </div>
            <div class="col-md-6">
                <img src="/images/desa.jpg" class="img-fluid rounded-4 shadow">
            </div>
        </div>
    </div>
</section>

<!-- ================= WISATA DESA ================= -->
<section id="wisata-desa" class="section-white">
    <div class="container text-center">
        <h2 class="section-title mb-4">Wisata Desa</h2>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card-wisata">
                    <img src="/images/wisata1.jpg">
                    <h5 class="mt-3">Curug Cibeureum</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-wisata">
                    <img src="/images/wisata2.jpg">
                    <h5 class="mt-3">Bukit Cinta</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-wisata">
                    <img src="/images/wisata3.jpg">
                    <h5 class="mt-3">Kampung Adat</h5>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= PRODUK UMKM ================= -->
<section id="produk-umkm" class="section-soft">
    <div class="container">
        <h2 class="section-title text-center mb-5">Produk Unggulan UMKM</h2>

        <div class="row g-4">
            @forelse($produks as $produk)
            <div class="col-md-4">
                <div class="card-produk">
                    @if($produk->foto)
                        <img src="{{ asset('storage/'.$produk->foto) }}">
                    @else
                        <img src="/images/no-image.png">
                    @endif

                    <div class="card-body text-center">
                        <h5>{{ $produk->nama_produk }}</h5>
                        <p class="price">
                            Rp {{ number_format($produk->harga_jual,0,',','.') }}
                        </p>

                        <button class="btn btn-warning w-100"
                            onclick="orderProduct({{ $produk->id }})"
                            @if($produk->stok <= 0) disabled @endif>
                            {{ $produk->stok > 0 ? 'Pesan Sekarang' : 'Stok Habis' }}
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center">
                <p class="text-muted">Belum ada produk tersedia</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- ================= CALL TO ACTION ================= -->
<section class="section-soft">
    <div class="container text-center">
        <h2 class="section-title mb-4">Ingin Menikmati Wisata dan Produk UMKM?</h2>
        <p class="mb-4">Daftar sekarang untuk memesan tiket wisata dan produk unggulan desa</p>
        
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="/pelanggan/login" class="btn btn-warning btn-lg px-5">
                <i class="fas fa-shopping-cart me-2"></i>
                Beli Tiket Wisata dan Produk UMKM Disini
            </a>
            <a href="/pelanggan/register" class="btn btn-outline-warning btn-lg px-5">
                <i class="fas fa-user-plus me-2"></i>
                Daftar Akun Baru
            </a>
        </div>
    </div>
</section>

<!-- ================= STYLE ================= -->
<style>
.hero-desa {
    background: url('/images/hero-desa.jpg') center/cover no-repeat;
    padding: 130px 0;
    position: relative;
}
.hero-desa .overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.55);
}
.section-soft {
    background: #f7f4ef;
    padding: 80px 0;
}
.section-white {
    background: #ffffff;
    padding: 80px 0;
}
.section-title {
    font-weight: 700;
    color: #3a3a3a;
}
.card-wisata,
.card-produk {
    background: #fff;
    border-radius: 20px;
    padding: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,.08);
}
.card-wisata img,
.card-produk img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    border-radius: 15px;
}
.price {
    font-weight: bold;
    color: #d39e00;
}
.step {
    background: #f7f4ef;
    padding: 20px;
    border-radius: 15px;
    font-weight: 600;
}
</style>

<!-- ================= SCRIPT ================= -->
<script>
function orderProduct(id) {
    window.location.href = '/pelanggan/login?redirect=catalog&product=' + id;
}
</script>

@endsection
