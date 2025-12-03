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
                    </div>
                    <div class="mt-4">
                        <button class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1">
                            Beli
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-slate-600">Belum ada produk yang dapat ditampilkan.</p>
            @endforelse
        </div>
    </div>
</body>
</html>
