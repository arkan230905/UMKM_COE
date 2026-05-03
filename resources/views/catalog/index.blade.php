<h1>Catalog Working</h1>
<p>Simple catalog test page.</p>
@if($company)
    <h2>{{ $company->nama }}</h2>
    <p>{{ $company->catalog_description ?? 'No description' }}</p>
@endif
<h3>Products ({{ $produks->count() }})</h3>
@foreach($produks as $produk)
    <div>
        <strong>{{ $produk->nama_produk }}</strong> - Rp {{ number_format($produk->harga_jual ?? 0, 0, ',', '.') }}
    </div>
@endforeach
