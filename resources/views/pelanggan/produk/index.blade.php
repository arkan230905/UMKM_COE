<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="max-w-6xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-semibold mb-6 text-slate-800">Katalog Produk</h1>
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($produks as $produk)
                <div class="bg-white rounded-lg shadow-sm p-4 flex flex-col justify-between">
                    <div>
                        @if($produk->foto)
                            <div class="mb-3">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($produk->foto) }}" alt="Foto Produk" class="w-full h-40 object-cover rounded-md">
                            </div>
                        @endif
                        <h2 class="text-lg font-semibold text-slate-800 mb-1">{{ $produk->nama_produk }}</h2>
                        <p class="text-sm text-slate-600 mb-2">{{ \Illuminate\Support\Str::limit($produk->deskripsi, 80) }}</p>
                        <p class="text-base font-bold text-blue-600">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</p>
                        <p class="text-xs mt-1 flex items-center gap-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-slate-500">Stok :</span>
                            <span class="{{ $produk->stok > 0 ? 'text-slate-600' : 'text-rose-400' }}">{{ number_format($produk->stok, 0, ',', '.') }}</span>
                        </p>
                    </div>
                    <div class="mt-4">
                        <form action="{{ route('pelanggan.cart.store') }}" method="POST" class="space-y-3">
                            @csrf
                            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                            <div class="flex items-center gap-2">
                                <button type="button" class="px-3 py-2 bg-slate-200 text-slate-700 rounded disabled:opacity-50" onclick="const i=this.nextElementSibling; i.stepDown(); i.dispatchEvent(new Event('input',{bubbles:true}));" {{ $produk->stok <= 1 ? '' : '' }}>−</button>
                                <input type="number" name="qty" value="1" min="1" max="{{ max(1, (int) $produk->stok) }}" class="w-20 border rounded px-2 py-2 text-center" oninput="const m=parseInt(this.max)||1; if(parseInt(this.value)>m){this.value=m} if(parseInt(this.value)<1){this.value=1}">
                                <button type="button" class="px-3 py-2 bg-slate-200 text-slate-700 rounded disabled:opacity-50" onclick="const i=this.previousElementSibling; const m=parseInt(i.max)||1; i.value=Math.min(parseInt(i.value||1)+1,m); i.dispatchEvent(new Event('input',{bubbles:true}));">+</button>
                            </div>
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 {{ $produk->stok > 0 ? 'bg-blue-600 hover:bg-blue-700' : 'bg-slate-300 cursor-not-allowed' }} text-white text-sm font-medium rounded focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1" {{ $produk->stok > 0 ? '' : 'disabled' }}>
                                Tambah ke Keranjang
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-slate-600">Belum ada produk yang dapat ditampilkan.</p>
            @endforelse
        </div>
    </div>
</body>
</html>
