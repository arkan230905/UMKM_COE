<h1>Catalog Working</h1>
<p>Simple catalog test page.</p>
@if($company)
    <h2>{{ $company->nama }}</h2>
    <p>{{ $company->catalog_description ?? 'No description' }}</p>
@endif
<h3>Products ({{ $produks->count() }})</h3>
@foreach($produks as $produk)
    <div style="border: 1px solid #ccc; padding: 10px; margin: 10px 0;">
        <strong>{{ $produk->nama_produk }}</strong> - Rp {{ number_format($produk->harga_jual ?? 0, 0, ',', '.') }}
        <br>Foto: {{ $produk->foto_path ?? 'No photo' }}
        @if($produk->foto_path)
            <br><img src="{{ asset('storage/' . $produk->foto_path) }}" alt="{{ $produk->nama_produk }}" style="max-width: 200px; height: auto;" onerror="this.style.display='none'">
        @endif
    </div>
@endforeach
