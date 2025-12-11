@php
use Illuminate\Support\Facades\DB;
@endphp

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Daftar Produk</h1>
        <a href="{{ route('master-data.produk.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Produk
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">#</th>
                            <th>Foto</th>
                            <th>Nama Produk</th>
                            <th>Deskripsi</th>
                            <th class="text-right">Harga BOM</th>
                            <th class="text-center">Margin</th>
                            <th class="text-right">Harga Jual</th>
                            <th class="text-center">Rating</th>
                            <th class="text-center">Stok</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produks as $produk)
                            @php
                                $hargaBomProduk = $produk->harga_bom ?? 0;
                                $margin = (float) ($produk->margin_percent ?? 30);
                                $hargaJual = $produk->harga_jual ?? $hargaBomProduk * (1 + ($margin / 100));
                                $stok = (float) $produk->stok;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-center">
                                    @if($produk->foto)
                                        <div class="product-image-wrapper" 
                                             onclick="showImageModal('{{ Storage::url($produk->foto) }}', '{{ addslashes($produk->nama_produk) }}')"
                                             style="width: 35px !important; height: 35px !important; cursor: pointer; position: relative; display: inline-block;">
                                            <img src="{{ Storage::url($produk->foto) }}" 
                                                 alt="{{ $produk->nama_produk }}" 
                                                 class="product-thumbnail"
                                                 style="width: 35px !important; height: 35px !important; object-fit: cover; border-radius: 4px;"
                                                 onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23ddd%22 width=%22100%22 height=%22100%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E';">
                                            <div class="image-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; border-radius: 4px;">
                                                <i class="fas fa-search-plus" style="color: white; font-size: 14px;"></i>
                                            </div>
                                        </div>
                                    @else
                                        <div class="no-image-placeholder" style="width: 35px !important; height: 35px !important;">
                                            <i class="fas fa-image" style="font-size: 12px;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $produk->nama_produk }}</td>
                                <td>{{ $produk->deskripsi ? \Illuminate\Support\Str::limit($produk->deskripsi, 50) : '-' }}</td>
                                <td class="text-right">Rp {{ number_format($hargaBomProduk, 0, ',', '.') }}</td>
                                <td class="text-center">{{ number_format($margin, 0, ',', '.') }}%</td>
                                <td class="text-right font-weight-bold">Rp {{ number_format($hargaJual, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @php
                                        $reviewsCount = DB::table('reviews')
                                            ->join('order_items', 'reviews.order_id', '=', 'order_items.order_id')
                                            ->where('order_items.produk_id', $produk->id)
                                            ->count();
                                            
                                        $avgRating = DB::table('reviews')
                                            ->join('order_items', 'reviews.order_id', '=', 'order_items.order_id')
                                            ->where('order_items.produk_id', $produk->id)
                                            ->avg('reviews.rating') ?? 0;
                                    @endphp
                                    
                                    @if($reviewsCount > 0)
                                        <div class="rating-display">
                                            <div class="stars">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= round($avgRating) ? 'text-warning' : 'text-muted' }}"></i>
                                                @endfor
                                            </div>
                                            <small class="text-muted d-block">{{ number_format($avgRating, 1) }} ({{ $reviewsCount }})</small>
                                        </div>
                                    @else
                                        <span class="text-muted">Belum ada rating</span>
                                    @endif
                                </td>
                                <td class="text-center {{ $stok <= 0 ? 'text-danger font-weight-bold' : '' }}">
                                    {{ number_format($stok, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('master-data.produk.edit', $produk->id) }}" 
                                           class="btn btn-sm btn-warning" 
                                           data-bs-toggle="tooltip" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('master-data.produk.destroy', $produk->id) }}" 
                                              method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data produk</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .btn-group .btn {
        margin: 0 2px;
    }
    .text-right {
        text-align: right !important;
    }
    .text-center {
        text-align: center !important;
    }
    
    /* Product Image Styling */
    .product-image-wrapper {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 40px;
        overflow: hidden;
        border-radius: 4px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .product-image-wrapper:hover {
        transform: scale(1.1);
        box-shadow: 0 3px 10px rgba(0,0,0,0.25);
        z-index: 10;
    }
    
    .product-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
        cursor: pointer;
        transition: opacity 0.3s ease;
    }
    
    .product-image-wrapper:hover .product-thumbnail {
        opacity: 0.85;
    }
    
    .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        border-radius: 4px;
    }
    
    .product-image-wrapper:hover .image-overlay {
        opacity: 1 !important;
    }
    
    .image-overlay i {
        color: white;
        font-size: 14px;
    }
    
    .no-image-placeholder {
        width: 40px;
        height: 40px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border: 1px dashed #dee2e6;
        border-radius: 4px;
        color: #6c757d;
    }
    
    .no-image-placeholder i {
        font-size: 14px;
        margin-bottom: 0;
    }
    
    .no-image-placeholder .small {
        font-size: 7px;
        line-height: 1;
    }
    
    /* Modal Image Styling */
    #imageModal .modal-dialog {
        max-width: 90vw;
    }
    
    #imageModal .modal-body {
        background: #000;
        padding: 20px;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    #imageModal .modal-content {
        background: transparent;
        border: none;
    }
    
    #imageModal .modal-header {
        background: #fff;
        border-bottom: 1px solid #dee2e6;
    }
    
    #modalImage {
        border-radius: 4px;
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        box-shadow: 0 4px 20px rgba(255,255,255,0.1);
    }
</style>
@endpush

<!-- Lightbox untuk preview foto fullscreen -->
<div id="imageLightbox" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 9999; cursor: pointer;" onclick="closeLightbox()">
    <div style="position: absolute; top: 20px; right: 30px; color: white; font-size: 40px; font-weight: bold; cursor: pointer; z-index: 10000;" onclick="closeLightbox()">&times;</div>
    <div style="position: absolute; top: 20px; left: 30px; color: white; font-size: 20px; z-index: 10000;" id="lightboxTitle"></div>
    <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; padding: 60px 20px 20px 20px;">
        <img id="lightboxImage" src="" alt="Foto Produk" style="max-width: 95%; max-height: 95%; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 30px rgba(255,255,255,0.3);">
    </div>
</div>

<!-- jQuery dan DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<script>
    // Fungsi global untuk lightbox - HARUS di atas agar bisa dipanggil dari onclick
    window.showImageModal = function(imageUrl, productName) {
        console.log('🖼️ Opening lightbox for:', productName, imageUrl);
        
        const lightbox = document.getElementById('imageLightbox');
        const lightboxImage = document.getElementById('lightboxImage');
        const lightboxTitle = document.getElementById('lightboxTitle');
        
        if (!lightbox || !lightboxImage || !lightboxTitle) {
            console.error('❌ Lightbox elements not found!');
            return;
        }
        
        // Set image dan title
        lightboxImage.src = imageUrl;
        lightboxTitle.textContent = 'Foto Produk: ' + productName;
        
        // Tampilkan lightbox dengan fade in
        lightbox.style.display = 'block';
        setTimeout(() => {
            lightbox.style.opacity = '1';
        }, 10);
        
        console.log('✅ Lightbox opened successfully');
    };
    
    // Fungsi global untuk menutup lightbox
    window.closeLightbox = function() {
        console.log('🔒 Closing lightbox');
        const lightbox = document.getElementById('imageLightbox');
        if (lightbox) {
            lightbox.style.opacity = '0';
            setTimeout(() => {
                lightbox.style.display = 'none';
            }, 300);
        }
    };
    
    // Tutup lightbox dengan tombol ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeLightbox();
        }
    });

    // Initialize setelah DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('📋 Initializing DataTable and Tooltips');
        
        // Initialize tooltips Bootstrap 5
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize DataTable
        if ($.fn.DataTable.isDataTable('#dataTable')) {
            $('#dataTable').DataTable().destroy();
        }
        
        const table = $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            },
            "columnDefs": [
                { 
                    "orderable": false, 
                    "targets": [0, 8] 
                },
                { 
                    "searchable": false, 
                    "targets": [0, 8] 
                },
                {
                    "className": "text-end",
                    "targets": [4, 6]
                },
                {
                    "className": "text-center",
                    "targets": [1, 5, 7, 8]
                }
            ],
            "order": [[2, 'asc']],
            "pageLength": 25
        });
        
        // Update nomor urut otomatis setiap kali tabel di-render ulang
        table.on('order.dt search.dt', function () {
            let i = 1;
            table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
                this.data(i++);
            });
        }).draw();
        
        console.log('✅ DataTable initialized');
    });
</script>
@endsection
