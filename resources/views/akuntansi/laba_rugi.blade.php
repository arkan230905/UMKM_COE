@extends('layouts.app')
@section('title', 'Laporan Laba Rugi')

@push('styles')
<style>
/* ── Base ── */
.lr { background:#F4F4F2; min-height:100vh; padding:32px 24px; font-family:'Poppins',sans-serif; }
.lr-wrap { max-width:780px; margin:0 auto; }

/* ── Page Header ── */
.lr-page-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; flex-wrap:wrap; gap:14px; }
.lr-page-head h1 { font-size:22px; font-weight:700; color:#1C1C1C; margin:0 0 4px; }
.lr-page-head .period { font-size:12px; color:#999; display:flex; align-items:center; gap:5px; }
.lr-filter { display:flex; gap:8px; align-items:flex-end; }
.lr-filter label { font-size:11px; color:#777; font-weight:500; display:block; margin-bottom:4px; }
.lr-filter input { padding:8px 12px; border:1px solid #DDD; border-radius:8px; font-size:13px; color:#333; background:white; outline:none; transition:border-color .2s; }
.lr-filter input:focus { border-color:#888; }
.btn-show { padding:8px 18px; background:#2C2C2C; border:none; border-radius:8px; color:white; font-size:13px; font-weight:500; cursor:pointer; transition:background .2s; }
.btn-show:hover { background:#444; }

/* ── Summary strip ── */
.lr-summary {
    display:flex; align-items:stretch;
    background:white; border-radius:12px;
    border:1px solid #E6E6E4; box-shadow:0 2px 10px rgba(0,0,0,0.05);
    margin-bottom:20px; overflow:hidden;
}
.ls-box {
    flex:1; padding:18px 22px;
    transition:box-shadow .2s, background .2s;
    cursor:default;
}
.ls-box:hover { background:#FAFAF8; box-shadow:inset 0 -3px 0 #E0E0DC; }
.ls-label { font-size:11px; color:#999; font-weight:500; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; }
.ls-value { font-size:18px; font-weight:800; line-height:1.2; }
.ls-value.green { color:#1A7A3C; }
.ls-value.blue  { color:#1A4A8A; }
.ls-value.red   { color:#C0392B; }
.ls-divider { width:1px; background:#EEEEEC; flex-shrink:0; margin:14px 0; }

/* ── Main card ── */
.lr-card { background:white; border-radius:12px; border:1px solid #E6E6E4; box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden; margin-bottom:12px; }

/* ── Section header strip ── */
.sec-strip { display:flex; align-items:center; gap:10px; padding:13px 22px; border-bottom:1px solid #F0F0EE; }
.sec-strip .sec-icon { width:28px; height:28px; border-radius:7px; display:flex; align-items:center; justify-content:center; font-size:12px; flex-shrink:0; }
.sec-strip .sec-icon.green { background:#E8F5EE; color:#1A7A3C; }
.sec-strip .sec-icon.red   { background:#FEF0EE; color:#C0392B; }
.sec-strip .sec-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:#555; }

/* ── Data rows ── */
.lr-row { display:flex; align-items:center; padding:12px 22px; border-bottom:1px solid #F7F7F5; transition:background .12s; }
.lr-row:last-child { border-bottom:none; }
.lr-row:hover { background:#FAFAF8; }
.lr-row .rname { flex:1; }
.lr-row .rname .main { font-size:13px; color:#2C2C2C; }
.lr-row .rname .hint { font-size:11px; color:#BBB; margin-top:1px; }
.lr-row .ramt { font-size:13px; font-weight:500; color:#2C2C2C; white-space:nowrap; min-width:140px; text-align:right; }

/* ── Total row ── */
.lr-total { display:flex; align-items:center; padding:13px 22px; background:#F7F7F5; border-top:1.5px solid #E6E6E4; }
.lr-total .rname { flex:1; font-size:13px; font-weight:700; color:#1C1C1C; }
.lr-total .ramt  { font-size:14px; font-weight:700; color:#1C1C1C; white-space:nowrap; min-width:140px; text-align:right; }
.lr-total .ramt.green { color:#1A7A3C; }
.lr-total .ramt.red   { color:#C0392B; }

/* ── HPP pengurang ── */
.lr-hpp { display:flex; align-items:center; padding:12px 22px; background:#FFF8F6; border-top:1px dashed #EDD; }
.lr-hpp .rname { flex:1; }
.lr-hpp .rname .main { font-size:13px; color:#B05030; }
.lr-hpp .rname .hint { font-size:11px; color:#CCA898; margin-top:1px; }
.lr-hpp .ramt { font-size:13px; font-weight:600; color:#B05030; white-space:nowrap; min-width:140px; text-align:right; }

/* ── Laba Kotor ── */
.lr-laba-kotor { display:flex; align-items:center; padding:14px 22px; background:#F0F8F3; border-top:2px solid #B8DEC8; }
.lr-laba-kotor .rname { flex:1; }
.lr-laba-kotor .rname .main { font-size:13px; font-weight:700; color:#1A5C30; }
.lr-laba-kotor .rname .hint { font-size:11px; color:#7AB898; margin-top:1px; }
.lr-laba-kotor .ramt { font-size:15px; font-weight:800; color:#1A5C30; white-space:nowrap; min-width:140px; text-align:right; }
.lr-laba-kotor .ramt.red { color:#C0392B; }

/* ── Spacer ── */
.lr-gap { height:12px; }

/* ── Hasil Akhir card ── */
.lr-hasil { background:white; border-radius:12px; border:1px solid #E6E6E4; box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden; margin-top:12px; }
.lr-hasil-body { display:flex; align-items:center; justify-content:space-between; padding:22px 24px; }
.lr-hasil-body .hl { }
.lr-hasil-body .hl .title { font-size:14px; font-weight:700; color:#1C1C1C; margin-bottom:3px; }
.lr-hasil-body .hl .formula { font-size:11px; color:#AAA; }
.lr-hasil-body .val { font-size:24px; font-weight:800; }
.lr-hasil-body .val.laba { color:#1A7A3C; }
.lr-hasil-body .val.rugi { color:#C0392B; }
.lr-hasil-footer { padding:10px 24px; background:#F7F7F5; border-top:1px solid #EEEEEC; font-size:11px; color:#AAA; display:flex; gap:16px; flex-wrap:wrap; }
.lr-hasil-footer span { display:flex; align-items:center; gap:5px; }

/* ── Empty ── */
.lr-empty { padding:14px 22px; font-size:12px; color:#CCC; font-style:italic; }

/* ── Sub rows (breakdown per produk) ── */
.lr-row.lr-sub { background:#FAFAF8; padding:9px 22px 9px 36px; border-bottom:1px solid #F2F2F0; }
.lr-row.lr-sub:hover { background:#F5F5F3; }
.sub-dot { color:#CCC; margin-right:6px; font-size:12px; }
.sub-name { font-size:12px; color:#555; }
.sub-amt { font-size:12px; color:#555; }
.lr-hpp.lr-hpp-sub { padding:9px 22px 9px 36px; background:#FFF5F2; border-top:none; border-bottom:1px solid #F5EAE6; }

@media(max-width:600px){
    .lr-page-head { flex-direction:column; }
    .lr-row .ramt, .lr-total .ramt, .lr-hpp .ramt, .lr-laba-kotor .ramt { min-width:100px; }
}
</style>
@endpush

@section('content')
<div class="lr">
<div class="lr-wrap">

{{-- PAGE HEADER --}}
<div class="lr-page-head">
    <div>
        <h1>Laporan Laba Rugi</h1>
        <div class="period">
            <i class="fas fa-calendar" style="color:#AAA;"></i>
            {{ \Carbon\Carbon::parse($periode.'-01')->isoFormat('MMMM YYYY') }}
        </div>
    </div>
    <form method="GET" class="lr-filter">
        <div>
            <label>Periode</label>
            <input type="month" name="periode" value="{{ $periode }}">
        </div>
        <button type="submit" class="btn-show">Tampilkan</button>
    </form>
</div>

{{-- ══════════════════════════════════════════
     RINGKASAN LABA RUGI
══════════════════════════════════════════ --}}
<div class="lr-summary">
    <div class="ls-box">
        <div class="ls-label">Total Pendapatan</div>
        <div class="ls-value green">Rp {{ number_format($totalPendapatan,0,',','.') }}</div>
    </div>
    <div class="ls-divider"></div>
    <div class="ls-box">
        <div class="ls-label">Laba Kotor</div>
        <div class="ls-value {{ $labaKotor < 0 ? 'red' : 'blue' }}">
            {{ $labaKotor < 0 ? '−' : '' }}Rp {{ number_format(abs($labaKotor),0,',','.') }}
        </div>
    </div>
    <div class="ls-divider"></div>
    <div class="ls-box">
        <div class="ls-label">Total Biaya</div>
        <div class="ls-value red">Rp {{ number_format($totalBeban,0,',','.') }}</div>
    </div>
    <div class="ls-divider"></div>
    <div class="ls-box">
        <div class="ls-label">{{ $labaBersih >= 0 ? 'Laba Bersih' : 'Rugi Bersih' }}</div>
        <div class="ls-value {{ $labaBersih >= 0 ? 'green' : 'red' }}">
            {{ $labaBersih < 0 ? '−' : '' }}Rp {{ number_format(abs($labaBersih),0,',','.') }}
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     PENDAPATAN
══════════════════════════════════════════ --}}
<div class="lr-card">

    <div class="sec-strip">
        <div class="sec-icon green"><i class="fas fa-arrow-up"></i></div>
        <span class="sec-label">Pendapatan</span>
    </div>

    @forelse($pendapatan as $coa)
        @php $s = $getSaldo($coa); @endphp
        <div class="lr-row">
            <div class="rname">
                <div class="main">{{ $coa->nama_akun }}</div>
            </div>
            <div class="ramt">Rp {{ number_format($s,0,',','.') }}</div>
        </div>
        {{-- Jika ini akun Penjualan (kode 41 atau nama mengandung "Penjualan"), tampilkan breakdown per produk --}}
        @if(str_starts_with($coa->kode_akun, '41') || strtolower($coa->nama_akun) === 'penjualan')
            @foreach($detailPenjualan as $dp)
            <div class="lr-row lr-sub">
                <div class="rname">
                    <div class="main sub-name">
                        <span class="sub-dot">↳</span> {{ $dp->nama_produk }}
                    </div>
                    <div class="hint">{{ number_format($dp->total_qty, 0, ',', '.') }} pcs terjual</div>
                </div>
                <div class="ramt sub-amt">Rp {{ number_format($dp->total_pendapatan, 0, ',', '.') }}</div>
            </div>
            @endforeach
        @endif
    @empty
        <div class="lr-empty">Belum ada data pendapatan pada periode ini</div>
    @endforelse

    {{-- Total Pendapatan --}}
    <div class="lr-total">
        <div class="rname">Total Pendapatan</div>
        <div class="ramt green">Rp {{ number_format($totalPendapatan,0,',','.') }}</div>
    </div>

    {{-- HPP sebagai pengurang --}}
    <div class="lr-hpp">
        <div class="rname">
            <div class="main">Modal Barang Terjual (HPP)</div>
            <div class="hint">Biaya pokok produk yang terjual</div>
        </div>
        <div class="ramt">− Rp {{ number_format($totalHpp,0,',','.') }}</div>
    </div>
    {{-- Breakdown HPP per produk --}}
    @foreach($detailHpp as $dh)
    <div class="lr-hpp lr-hpp-sub">
        <div class="rname">
            <div class="main sub-name" style="color:#C07050;">
                <span class="sub-dot">↳</span> HPP {{ $dh->nama_produk }}
            </div>
            <div class="hint" style="color:#D4A898;">{{ number_format($dh->total_qty, 0, ',', '.') }} pcs × HPP/unit</div>
        </div>
        <div class="ramt" style="color:#C07050;">− Rp {{ number_format($dh->total_hpp, 0, ',', '.') }}</div>
    </div>
    @endforeach

    {{-- Laba Kotor --}}
    <div class="lr-laba-kotor">
        <div class="rname">
            <div class="main">Laba Kotor</div>
            <div class="hint">Sisa setelah dikurangi modal barang</div>
        </div>
        <div class="ramt {{ $labaKotor < 0 ? 'red' : '' }}">
            {{ $labaKotor < 0 ? '−' : '' }}Rp {{ number_format(abs($labaKotor),0,',','.') }}
        </div>
    </div>

</div>

<div class="lr-gap"></div>

{{-- ══════════════════════════════════════════
     BIAYA OPERASIONAL
══════════════════════════════════════════ --}}
<div class="lr-card">

    <div class="sec-strip">
        <div class="sec-icon red"><i class="fas fa-arrow-down"></i></div>
        <span class="sec-label">Biaya Operasional</span>
    </div>

    @forelse($beban as $coa)
        @php $s = $getSaldo($coa); @endphp
        <div class="lr-row">
            <div class="rname">
                <div class="main">{{ $coa->nama_akun }}</div>
            </div>
            <div class="ramt">Rp {{ number_format($s,0,',','.') }}</div>
        </div>
    @empty
        <div class="lr-empty">Belum ada data biaya operasional pada periode ini</div>
    @endforelse

    <div class="lr-total">
        <div class="rname">Total Biaya Operasional</div>
        <div class="ramt red">Rp {{ number_format($totalBeban,0,',','.') }}</div>
    </div>

</div>

{{-- ══════════════════════════════════════════
     HASIL AKHIR
══════════════════════════════════════════ --}}
<div class="lr-hasil">
    <div class="lr-hasil-body">
        <div class="hl">
            <div class="title">
                @if($labaBersih >= 0)
                    Laba Bersih
                @else
                    Rugi Bersih
                @endif
            </div>
            <div class="formula">Laba Kotor − Biaya Operasional</div>
        </div>
        <div class="val {{ $labaBersih >= 0 ? 'laba' : 'rugi' }}">
            {{ $labaBersih < 0 ? '−' : '' }}Rp {{ number_format(abs($labaBersih),0,',','.') }}
        </div>
    </div>
    <div class="lr-hasil-footer">
        <span><i class="fas fa-circle" style="color:#1A7A3C;font-size:7px;"></i> Pendapatan: Rp {{ number_format($totalPendapatan,0,',','.') }}</span>
        <span><i class="fas fa-circle" style="color:#B05030;font-size:7px;"></i> HPP: Rp {{ number_format($totalHpp,0,',','.') }}</span>
        <span><i class="fas fa-circle" style="color:#C0392B;font-size:7px;"></i> Biaya: Rp {{ number_format($totalBeban,0,',','.') }}</span>
    </div>
</div>

</div>{{-- end lr-wrap --}}
</div>{{-- end lr --}}
@endsection
