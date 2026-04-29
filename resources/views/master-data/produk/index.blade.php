@extends('layouts.app')
@section('title', 'Daftar Produk')

@push('styles')
<style>
.produk-page { background:#F7F4F0; min-height:100vh; padding:28px 24px; font-family:'Poppins',sans-serif; }
.produk-container { max-width:1400px; margin:0 auto; }
.produk-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
.produk-header-left h1 { font-size:22px; font-weight:700; color:#2C1810; margin:0 0 3px 0; }
.produk-header-left p { font-size:12px; color:#A89080; margin:0; }
.produk-header-right { display:flex; gap:10px; align-items:center; }
.btn-cetak { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; background:white; border:1.5px solid #D4C4B0; border-radius:8px; color:#7A6350; font-size:13px; font-weight:500; text-decoration:none; transition:all 0.2s; }
.btn-cetak:hover { background:#F5EFE8; border-color:#8B7355; color:#5C4A35; }
.btn-tambah-prod { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:linear-gradient(135deg,#8B7355,#6B5535); border:none; border-radius:8px; color:white; font-size:13px; font-weight:600; text-decoration:none; transition:all 0.2s; box-shadow:0 2px 8px rgba(139,115,85,0.35); }
.btn-tambah-prod:hover { background:linear-gradient(135deg,#7A6349,#5A4425); color:white; transform:translateY(-1px); }
.produk-layout { display:flex; gap:20px; align-items:flex-start; }
.produk-sidebar { width:240px; flex-shrink:0; }
.sidebar-card { background:white; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,0.07); overflow:hidden; margin-bottom:12px; }
.sidebar-card-header { padding:16px 18px 12px; border-bottom:1px solid #F0EAE2; }
.sidebar-card-header h3 { font-size:14px; font-weight:700; color:#2C1810; margin:0; }
.kat-all { display:flex; align-items:center; gap:10px; padding:11px 18px; text-decoration:none; transition:all 0.2s; border-bottom:1px solid #F5F0EB; }
.kat-all:hover { background:#FBF8F5; }
.kat-all.active { background:linear-gradient(135deg,#8B7355,#7A6349); }
.kat-icon { width:30px; height:30px; border-radius:7px; background:#F0EAE2; display:flex; align-items:center; justify-content:center; font-size:12px; color:#8B7355; flex-shrink:0; }
.kat-all.active .kat-icon { background:rgba(255,255,255,0.2); color:white; }
.kat-all .kat-label { flex:1; font-size:13px; font-weight:500; color:#4A3728; }
.kat-all.active .kat-label { color:white; }
.kat-all .kat-count { font-size:11px; font-weight:600; color:#A89080; background:#F0EAE2; padding:2px 8px; border-radius:20px; }
.kat-all.active .kat-count { background:rgba(255,255,255,0.25); color:white; }
.kat-list { list-style:none; padding:0; margin:0; }
.kat-list li { border-bottom:1px solid #F5F0EB; }
.kat-list li:last-child { border-bottom:none; }
.kat-link { display:flex; align-items:center; gap:10px; padding:11px 18px; text-decoration:none; transition:all 0.2s; }
.kat-link:hover { background:#FBF8F5; }
.kat-link.active { background:#F5EFE8; }
.kat-link .kat-icon { background:#F5F0EB; color:#A89080; }
.kat-link.active .kat-icon { background:#EDE4D8; color:#8B7355; }
.kat-link .kat-label { flex:1; font-size:13px; color:#6A5545; }
.kat-link.active .kat-label { color:#5C4A35; font-weight:600; }
.kat-link .kat-count { font-size:11px; font-weight:600; color:#B0A090; background:#F0EAE2; padding:2px 8px; border-radius:20px; }
.kat-link.active .kat-count { background:#DDD0C0; color:#7A6349; }
.btn-add-kat { display:flex; align-items:center; justify-content:center; gap:7px; padding:11px 16px; background:white; border:1.5px dashed #C8B8A0; border-radius:10px; color:#8B7355; font-size:12px; font-weight:500; text-decoration:none; transition:all 0.2s; width:100%; }
.btn-add-kat:hover { background:#F5EFE8; border-color:#8B7355; border-style:solid; color:#6B5535; }
.produk-content { flex:1; min-width:0; }
.filter-card { background:white; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,0.07); padding:16px 20px; margin-bottom:16px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
.search-wrap { flex:1; min-width:220px; position:relative; }
.search-wrap .si { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#C0B0A0; font-size:13px; }
.search-input { width:100%; padding:9px 12px 9px 36px; border:1.5px solid #E8DDD0; border-radius:8px; font-size:13px; color:#4A3728; background:#FDFAF7; outline:none; transition:all 0.2s; }
.search-input:focus { border-color:#8B7355; background:white; box-shadow:0 0 0 3px rgba(139,115,85,0.1); }
.search-input::placeholder { color:#C0B0A0; }
.f-select { padding:9px 12px; border:1.5px solid #E8DDD0; border-radius:8px; font-size:13px; color:#4A3728; background:#FDFAF7; cursor:pointer; min-width:140px; outline:none; }
.f-select:focus { border-color:#8B7355; }
.btn-cari { padding:9px 16px; background:#8B7355; border:none; border-radius:8px; color:white; font-size:13px; cursor:pointer; display:flex; align-items:center; gap:6px; }
.btn-cari:hover { background:#7A6349; }
.btn-reset { padding:9px 14px; background:white; border:1.5px solid #E8DDD0; border-radius:8px; color:#A89080; font-size:13px; cursor:pointer; text-decoration:none; display:flex; align-items:center; gap:6px; }
.btn-reset:hover { background:#F5EFE8; color:#7A6349; }
.table-card { background:white; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,0.07); overflow:hidden; }
.table-card-head { padding:16px 20px; border-bottom:1px solid #F0EAE2; display:flex; align-items:center; justify-content:space-between; }
.table-card-head h4 { font-size:14px; font-weight:600; color:#2C1810; margin:0; }
.table-card-head span { font-size:12px; color:#A89080; }
.table-wrap { overflow-x:auto; }
.pt { width:100%; border-collapse:collapse; font-size:13px; }
.pt thead tr { background:#FDFAF7; border-bottom:1.5px solid #EDE4D8; }
.pt th { padding:12px 14px; text-align:left; font-weight:600; color:#8B7355; white-space:nowrap; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; }
.pt th.tc { text-align:center; } .pt th.tr { text-align:right; }
.pt tbody tr { border-bottom:1px solid #F5F0EB; transition:background 0.15s; }
.pt tbody tr:last-child { border-bottom:none; }
.pt tbody tr:hover { background:#FDFAF7; }
.pt td { padding:13px 14px; vertical-align:middle; color:#3A2A1E; }
.td-no { text-align:center; color:#B0A090; font-size:12px; width:40px; }
.td-foto { text-align:center; width:70px; }
.pimg { width:48px; height:48px; border-radius:10px; object-fit:cover; cursor:pointer; border:2px solid #F0EAE2; transition:all 0.2s; }
.pimg:hover { border-color:#8B7355; transform:scale(1.08); }
.pimg-ph { width:48px; height:48px; border-radius:10px; background:#F5F0EB; display:flex; align-items:center; justify-content:center; color:#C8B8A0; font-size:16px; margin:0 auto; }
.td-bc { text-align:center; width:110px; }
.bc-svg { height:32px; display:block; margin:0 auto 2px; }
.bc-num { font-size:9px; color:#B0A090; letter-spacing:0.5px; }
.td-nama { min-width:140px; }
.pname { font-weight:600; color:#2C1810; font-size:13px; }
.td-kat { width:110px; }
.kb { display:inline-flex; align-items:center; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap; }
.kb-mkn { background:#FFF3E0; color:#E65100; }
.kb-mnm { background:#E3F2FD; color:#1565C0; }
.kb-pkt { background:#E8F5E9; color:#2E7D32; }
.kb-lain { background:#F5F5F5; color:#757575; }
.td-harga { text-align:right; min-width:110px; white-space:nowrap; }
.hval { font-weight:600; color:#2C1810; font-size:13px; }
.td-stok { text-align:center; width:60px; }
.sval { font-weight:700; font-size:14px; color:#2C1810; }
.td-status { text-align:center; width:80px; }
.sbadge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:600; }
.s-aktif { background:#E8F5E9; color:#2E7D32; }
.s-habis { background:#FFEBEE; color:#C62828; }
.td-aksi { text-align:center; width:100px; white-space:nowrap; }
.ba { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:7px; border:none; cursor:pointer; font-size:12px; transition:all 0.2s; margin:0 2px; text-decoration:none; }
.ba-bc { background:#E3F2FD; color:#1565C0; } .ba-bc:hover { background:#BBDEFB; }
.ba-ed { background:#FFF8E1; color:#E65100; } .ba-ed:hover { background:#FFE0B2; }
.ba-del { background:#FFEBEE; color:#C62828; } .ba-del:hover { background:#FFCDD2; }
.empty-st { text-align:center; padding:60px 20px; color:#B0A090; }
.empty-st i { font-size:40px; margin-bottom:12px; display:block; color:#D4C4B0; }
.pg-bar { display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-top:1px solid #F0EAE2; flex-wrap:wrap; gap:10px; }
.pg-info { font-size:12px; color:#A89080; }
.pg-btns { display:flex; gap:4px; align-items:center; }
.pgb { width:32px; height:32px; border:1.5px solid #E8DDD0; background:white; border-radius:7px; cursor:pointer; font-size:12px; font-weight:500; color:#7A6349; transition:all 0.2s; display:flex; align-items:center; justify-content:center; }
.pgb:hover { background:#F5EFE8; border-color:#C8B8A0; }
.pgb.active { background:#8B7355; border-color:#8B7355; color:white; font-weight:700; }
.pg-sel { padding:5px 10px; border:1.5px solid #E8DDD0; border-radius:7px; font-size:12px; color:#7A6349; background:white; cursor:pointer; outline:none; }
@media(max-width:1024px){ .produk-layout{flex-direction:column;} .produk-sidebar{width:100%;} .filter-card{flex-direction:column;align-items:stretch;} }
</style>
@endpush

@section('content')
<div class="produk-page">
<div class="produk-container">

{{-- HEADER --}}
<div class="produk-header">
    <div class="produk-header-left">
        <h1><i class="fas fa-box-open" style="color:#8B7355;margin-right:8px;"></i>Daftar Produk</h1>
        <p>Kelola semua produk yang tersedia dalam sistem</p>
    </div>
    <div class="produk-header-right">
        <a href="{{ route('master-data.produk.print-barcode-all') }}" class="btn-cetak" target="_blank">
            <i class="fas fa-barcode"></i> Cetak Barcode Semua
        </a>
        <a href="{{ route('master-data.produk.create') }}" class="btn-tambah-prod">
            <i class="fas fa-plus"></i> Tambah Produk
        </a>
    </div>
</div>

{{-- MAIN LAYOUT --}}
<div class="produk-layout">

    {{-- SIDEBAR --}}
    <div class="produk-sidebar">
        <div class="sidebar-card">
            <div class="sidebar-card-header">
                <h3><i class="fas fa-tags" style="color:#8B7355;margin-right:6px;"></i>Kategori Produk</h3>
            </div>
            <a href="{{ route('master-data.produk.index') }}"
               class="kat-all {{ !isset($kategoriFilter) || !$kategoriFilter ? 'active' : '' }}">
                <span class="kat-icon"><i class="fas fa-th-large"></i></span>
                <span class="kat-label">Semua Kategori</span>
                <span class="kat-count">{{ $produks->count() }}</span>
            </a>
            <ul class="kat-list">
                @foreach($kategoris ?? [] as $kategori)
                <li>
                    <a href="{{ route('master-data.produk.index', ['kategori' => $kategori->id]) }}"
                       class="kat-link {{ isset($kategoriFilter) && $kategoriFilter == $kategori->id ? 'active' : '' }}">
                        <span class="kat-icon">
                            @switch($kategori->kode_kategori)
                                @case('MKN') <i class="fas fa-utensils"></i> @break
                                @case('MNM') <i class="fas fa-coffee"></i> @break
                                @case('PKT') <i class="fas fa-gift"></i> @break
                                @default     <i class="fas fa-cube"></i>
                            @endswitch
                        </span>
                        <span class="kat-label">{{ $kategori->nama }}</span>
                        <span class="kat-count">{{ $kategori->produks_count }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        <a href="{{ route('master-data.kategori-produk.create') }}" class="btn-add-kat">
            <i class="fas fa-plus"></i> Tambah Kategori
        </a>
    </div>

    {{-- CONTENT --}}
    <div class="produk-content">

        {{-- FILTER --}}
        <form method="GET" action="{{ route('master-data.produk.index') }}" class="filter-card">
            <div class="search-wrap">
                <i class="fas fa-search si"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                       placeholder="Cari nama produk atau barcode..." class="search-input">
            </div>
            <select name="kategori" class="f-select">
                <option value="">Semua Kategori</option>
                @foreach($kategoris ?? [] as $kat)
                    <option value="{{ $kat->id }}" {{ isset($kategoriFilter) && $kategoriFilter == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
                @endforeach
            </select>
            <select name="status" class="f-select">
                <option value="">Semua Status</option>
                <option value="aktif"  {{ isset($statusFilter) && $statusFilter == 'aktif'  ? 'selected' : '' }}>Aktif</option>
                <option value="habis"  {{ isset($statusFilter) && $statusFilter == 'habis'  ? 'selected' : '' }}>Habis</option>
            </select>
            <button type="submit" class="btn-cari"><i class="fas fa-search"></i> Cari</button>
            <a href="{{ route('master-data.produk.index') }}" class="btn-reset"><i class="fas fa-undo"></i> Reset</a>
        </form>

        {{-- TABLE --}}
        <div class="table-card">
            <div class="table-card-head">
                <h4>Data Produk</h4>
                <span>{{ $produks->count() }} produk ditemukan</span>
            </div>
            <div class="table-wrap">
                <table class="pt">
                    <thead>
                        <tr>
                            <th class="tc">No</th>
                            <th class="tc">Foto</th>
                            <th class="tc">Barcode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th class="tr">Harga Pokok</th>
                            <th class="tr">Harga Jual</th>
                            <th class="tc">Stok</th>
                            <th class="tc">Status</th>
                            <th class="tc">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($produks as $produk)
                        @php
                            $hpp   = $hargaBom[$produk->id] ?? 0;
                            $hjual = $produk->harga_jual ?? 0;
                            $stok  = (float) $produk->stok;
                            $kb = 'kb-lain';
                            if ($produk->kategori) {
                                switch ($produk->kategori->kode_kategori) {
                                    case 'MKN': $kb = 'kb-mkn'; break;
                                    case 'MNM': $kb = 'kb-mnm'; break;
                                    case 'PKT': $kb = 'kb-pkt'; break;
                                    case 'SNK': $kb = 'kb-snk'; break;
                                    case 'BMB': $kb = 'kb-bmb'; break;
                                }
                            }
                        @endphp
                        <tr>
                            <td class="td-no">{{ $loop->iteration }}</td>
                            <td class="td-foto">
                                @if($produk->foto)
                                    <img src="{{ Storage::url($produk->foto) }}" alt="{{ $produk->nama_produk }}"
                                         class="pimg"
                                         onclick="showImg('{{ Storage::url($produk->foto) }}','{{ addslashes($produk->nama_produk) }}')"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2248%22 height=%2248%22%3E%3Crect fill=%22%23f0ebe4%22 width=%2248%22 height=%2248%22/%3E%3C/svg%3E';">
                                @else
                                    <div class="pimg-ph"><i class="fas fa-image"></i></div>
                                @endif
                            </td>
                            <td class="td-bc">
                                @if($produk->barcode)
                                    <svg class="bc-svg" data-barcode="{{ $produk->barcode }}"></svg>
                                    <div class="bc-num">{{ $produk->barcode }}</div>
                                @else
                                    <span style="color:#D4C4B0;">—</span>
                                @endif
                            </td>
                            <td class="td-nama"><div class="pname">{{ $produk->nama_produk }}</div></td>
                            <td class="td-kat">
                                @if($produk->kategori)
                                    <span class="kb {{ $kb }}">{{ $produk->kategori->nama }}</span>
                                @else
                                    <span style="color:#D4C4B0;font-size:12px;">—</span>
                                @endif
                            </td>
                            <td class="td-harga"><span class="hval">Rp {{ number_format($hpp,0,',','.') }}</span></td>
                            <td class="td-harga"><span class="hval">Rp {{ number_format($hjual,0,',','.') }}</span></td>
                            <td class="td-stok"><span class="sval">{{ number_format($stok,0,',','.') }}</span></td>
                            <td class="td-status">
                                @if($stok > 0)
                                    <span class="sbadge s-aktif">Aktif</span>
                                @else
                                    <span class="sbadge s-habis">Habis</span>
                                @endif
                            </td>
                            <td class="td-aksi">
                                @if($produk->barcode)
                                <a href="{{ route('master-data.produk.print-barcode', $produk->id) }}"
                                   class="ba ba-bc" title="Cetak Barcode" target="_blank">
                                    <i class="fas fa-barcode"></i>
                                </a>
                                @endif
                                <a href="{{ route('master-data.produk.edit', $produk->id) }}"
                                   class="ba ba-ed" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('master-data.produk.destroy', $produk->id) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ba ba-del" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="empty-st">
                                    <i class="fas fa-box-open"></i>
                                    <p>Belum ada data produk</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pg-bar">
                <span class="pg-info">Menampilkan {{ $produks->count() }} produk</span>
                <div class="pg-btns">
                    <button class="pgb" type="button"><i class="fas fa-chevron-left"></i></button>
                    <button class="pgb active" type="button">1</button>
                    <button class="pgb" type="button">2</button>
                    <button class="pgb" type="button">3</button>
                    <button class="pgb" type="button"><i class="fas fa-chevron-right"></i></button>
                </div>
                <select class="pg-sel">
                    <option>10 / halaman</option>
                    <option>25 / halaman</option>
                    <option>50 / halaman</option>
                </select>
            </div>
        </div>

    </div>{{-- end produk-content --}}
</div>{{-- end produk-layout --}}
</div>{{-- end produk-container --}}
</div>{{-- end produk-page --}}

{{-- Lightbox --}}
<div id="imgLb" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.92);z-index:9999;cursor:pointer;" onclick="closeImg()">
    <div style="position:absolute;top:20px;right:28px;color:white;font-size:36px;font-weight:bold;cursor:pointer;" onclick="closeImg()">&times;</div>
    <div style="position:absolute;top:20px;left:28px;color:rgba(255,255,255,0.7);font-size:14px;" id="imgLbTitle"></div>
    <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;padding:60px 20px 20px;">
        <img id="imgLbSrc" src="" alt="" style="max-width:90%;max-height:90%;object-fit:contain;border-radius:12px;">
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.bc-svg').forEach(function (svg) {
        var val = svg.getAttribute('data-barcode');
        if (!val) return;
        try {
            JsBarcode(svg, val, { format:'EAN13', width:1.2, height:32, displayValue:false, margin:0, background:'transparent' });
        } catch(e) {
            try { JsBarcode(svg, val, { format:'CODE128', width:1, height:32, displayValue:false, margin:0, background:'transparent' }); } catch(e2){}
        }
    });
});
window.showImg = function(url, name) {
    document.getElementById('imgLbSrc').src = url;
    document.getElementById('imgLbTitle').textContent = name;
    document.getElementById('imgLb').style.display = 'block';
};
window.closeImg = function() { document.getElementById('imgLb').style.display = 'none'; };
document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeImg(); });
</script>
@endsection
