@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 retur-container owner-retur-theme">
    <div class="retur-hero mb-5">
        <div>
            <span class="retur-pill">Manajemen Retur 🔁</span>
            <h1 class="retur-title">Kelola Retur Penjualan & Pembelian</h1>
            <p class="retur-subtext">Pantau status retur, tindak lanjuti kompensasi, dan pastikan stok kembali akurat.</p>
        </div>
        <a href="{{ route('transaksi.retur.create') }}" class="btn btn-retur-primary">
            <i class="bi bi-plus-circle"></i> Tambah Retur
        </a>
    </div>

    @if($returs->isEmpty())
        <div class="retur-empty">
            <i class="bi bi-box-seam"></i>
            <h4>Belum ada retur</h4>
            <p>Kamu bisa menambahkan retur baru dari transaksi penjualan atau pembelian.</p>
            <a href="{{ route('transaksi.retur.create') }}" class="btn btn-retur-primary">Buat Retur Pertama</a>
        </div>
    @else
        <div class="row g-4">
            @foreach($returs as $retur)
            <div class="col-xl-4 col-lg-6">
                <div class="retur-card h-100">
                    <div class="retur-card__header">
                        <div>
                            <span class="retur-badge type-{{ $retur->type }}">{{ $retur->type === 'sale' ? 'Retur Penjualan' : 'Retur Pembelian' }}</span>
                            <h5 class="retur-number">RT-{{ str_pad($retur->id, 4, '0', STR_PAD_LEFT) }}</h5>
                        </div>
                        <div class="text-end">
                            <div class="retur-date">{{ \\Carbon\\Carbon::parse($retur->tanggal)->format('d M Y') }}</div>
                            <div class="retur-status status-{{ $retur->status }}">
                                <i class="bi bi-circle-fill"></i> {{ ucfirst($retur->status) }}
                            </div>
                        </div>
                    </div>

                    <div class="retur-card__body">
                        <div class="retur-info">
                            <span class="label">Kompensasi</span>
                            <span class="value">{{ ucfirst($retur->kompensasi) }}</span>
                        </div>
                        <div class="retur-info">
                            <span class="label">Items Dikembalikan</span>
                            <div class="value-stack">
                                @foreach($retur->details as $d)
                                    @php
                                        if ($retur->type === 'sale') {
                                            $itemName = $d->produk->nama_produk ?? '-';
                                        } else {
                                            $bahanBaku = \App\Models\BahanBaku::find($d->produk_id);
                                            $itemName = $bahanBaku->nama_bahan ?? '-';
                                        }
                                    @endphp
                                    <span class="chip">{{ $itemName }} <span>x {{ rtrim(rtrim(number_format($d->qty, 2, ',', '.'), '0'), ',') }}</span></span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="retur-card__actions">
                        <a href="{{ route('transaksi.retur.edit', $retur->id) }}" class="btn btn-action warning"><i class="bi bi-pencil"></i> Edit</a>
                        @if($retur->status !== 'posted')
                        <form action="{{ route('transaksi.retur.post', $retur->id) }}" method="POST" onsubmit="return confirm('Posting retur ini?')">
                            @csrf
                            <button class="btn btn-action success" type="submit"><i class="bi bi-send-check"></i> Post</button>
                        </form>
                        @endif
                        <form action="{{ route('transaksi.retur.destroy', $retur->id) }}" method="POST" onsubmit="return confirm('Yakin ingin hapus?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-action danger" type="submit"><i class="bi bi-trash"></i> Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

<style>
.owner-retur-theme {
    position: relative;
    z-index: 0;
    min-height: calc(100vh - 3rem);
    background: radial-gradient(circle at 10% 10%, rgba(99, 102, 241, 0.15) 0%, transparent 55%),
                radial-gradient(circle at 95% 20%, rgba(236, 72, 153, 0.12) 0%, transparent 50%),
                linear-gradient(180deg, rgba(8, 20, 45, 0.95) 0%, rgba(8, 20, 45, 0.8) 100%);
}

.owner-retur-theme::before,
.owner-retur-theme::after {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: -1;
}

.owner-retur-theme::before {
    background: radial-gradient(circle at 40% 0%, rgba(56, 189, 248, 0.18), transparent 60%);
}

.owner-retur-theme::after {
    background: radial-gradient(circle at 80% 75%, rgba(147, 197, 253, 0.16), transparent 55%);
}

.owner-retur-theme .retur-hero {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.85), rgba(79, 70, 229, 0.8));
    border: 1px solid rgba(148, 163, 233, 0.35);
    color: #f8fafc;
    box-shadow: 0 18px 36px rgba(14, 23, 42, 0.45);
    backdrop-filter: blur(16px);
}

.owner-retur-theme .retur-pill {
    background: rgba(248, 250, 252, 0.16);
    color: rgba(226, 232, 240, 0.9);
}

.owner-retur-theme .retur-title {
    color: #f8fafc;
}

.owner-retur-theme .retur-subtext {
    color: rgba(226, 232, 240, 0.78);
}

.owner-retur-theme .btn-retur-primary {
    background: linear-gradient(120deg, #8b5cf6, #6366f1, #22d3ee);
    color: #0f172a !important;
    box-shadow: 0 14px 22px rgba(99, 102, 241, 0.35);
}

.owner-retur-theme .btn-retur-primary:hover {
    color: #0f172a !important;
}

.owner-retur-theme .retur-empty,
.owner-retur-theme .retur-card {
    background: linear-gradient(155deg, rgba(15, 23, 42, 0.95), rgba(17, 24, 39, 0.82));
    border: 1px solid rgba(71, 85, 105, 0.45);
    box-shadow: 0 22px 45px rgba(2, 6, 23, 0.6);
    backdrop-filter: blur(16px);
    color: #e2e8f0;
}

.owner-retur-theme .retur-empty h4,
.owner-retur-theme .retur-number {
    color: #f8fafc;
}

.owner-retur-theme .retur-date {
    color: rgba(148, 163, 184, 0.9);
}

.owner-retur-theme .retur-info {
    background: rgba(30, 41, 59, 0.45);
    border: 1px solid rgba(51, 65, 85, 0.55);
}

.owner-retur-theme .retur-info .label {
    color: rgba(148, 163, 184, 0.95);
}

.owner-retur-theme .retur-info .value {
    color: #e2e8f0;
}

.owner-retur-theme .retur-badge.type-sale {
    background: rgba(59, 130, 246, 0.2);
    color: #bfdbfe;
}

.owner-retur-theme .retur-badge.type-purchase {
    background: rgba(34, 197, 94, 0.18);
    color: #bbf7d0;
}

.owner-retur-theme .chip {
    background: linear-gradient(120deg, rgba(59, 130, 246, 0.2), rgba(59, 130, 246, 0.45));
    color: #bfdbfe;
    border: 1px solid rgba(96, 165, 250, 0.4);
}

.owner-retur-theme .chip span {
    color: rgba(226, 232, 240, 0.8);
}

.owner-retur-theme .btn-action {
    box-shadow: 0 12px 22px rgba(2, 6, 23, 0.45);
}

.owner-retur-theme .btn-action.warning {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.9), rgba(249, 115, 22, 0.8));
}

.owner-retur-theme .btn-action.success {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.9), rgba(16, 185, 129, 0.8));
}

.owner-retur-theme .btn-action.danger {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.9), rgba(244, 63, 94, 0.8));
}

.retur-container {
    position: relative;
}

.retur-hero {
    background: linear-gradient(135deg, #f6f8ff, #e8efff);
    border-radius: 22px;
    padding: 28px 32px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    border: 1px solid #dbe4ff;
    box-shadow: 0 18px 40px rgba(79, 70, 229, 0.08);
}

.retur-pill {
    background: #e0e7ff;
    color: #3f4a6b;
    padding: 6px 16px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.retur-title {
    font-size: 2.1rem;
    font-weight: 700;
    color: #1f2937;
    margin-top: 12px;
}

.retur-subtext {
    color: #6b7280;
    margin-top: 6px;
    max-width: 520px;
}

.btn-retur-primary {
    border-radius: 999px;
    padding: 10px 22px;
    background: linear-gradient(135deg, #7d5cff, #9c6bff);
    color: #fff;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 12px 24px rgba(125, 92, 255, 0.22);
}

.btn-retur-primary:hover {
    color: #fff;
    transform: translateY(-2px);
}

.retur-empty {
    background: #fff;
    border-radius: 24px;
    padding: 60px 30px;
    text-align: center;
    border: 1px dashed #d1d5db;
    color: #6b7280;
    box-shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
}

.retur-empty i {
    font-size: 3rem;
    color: #7d5cff;
    margin-bottom: 12px;
}

.retur-card {
    background: #fff;
    border-radius: 20px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 22px 40px rgba(30, 64, 175, 0.08);
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    position: relative;
    overflow: hidden;
}

.retur-card::before {
    content: "";
    position: absolute;
    top: -40px;
    right: -60px;
    width: 180px;
    height: 180px;
    background: radial-gradient(circle, rgba(125, 92, 255, 0.18), rgba(125, 92, 255, 0));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.retur-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 30px 60px rgba(30, 64, 175, 0.15);
}

.retur-card:hover::before {
    opacity: 1;
}

.retur-card__header {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: center;
}

.retur-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
}

.retur-badge.type-sale {
    background: rgba(59, 130, 246, 0.12);
    color: #1d4ed8;
}

.retur-badge.type-purchase {
    background: rgba(16, 185, 129, 0.12);
    color: #047857;
}

.retur-number {
    margin-top: 10px;
    font-weight: 700;
    color: #1f2937;
}

.retur-date {
    font-size: 0.85rem;
    color: #6b7280;
}

.retur-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    font-size: 0.85rem;
}

.retur-status i {
    font-size: 0.6rem;
}

.retur-status.status-posted {
    color: #059669;
}

.retur-status.status-approved {
    color: #2563eb;
}

.retur-status.status-draft {
    color: #f59e0b;
}

.retur-card__body {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.retur-info {
    background: #f8fafc;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.retur-info .label {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #94a3b8;
}

.retur-info .value {
    font-weight: 600;
    color: #1f2937;
}

.value-stack {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.chip {
    background: rgba(125, 92, 255, 0.12);
    color: #4c1d95;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 600;
}

.chip span {
    color: #6b7280;
    font-weight: 500;
    margin-left: 4px;
}

.retur-card__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: flex-end;
}

.btn-action {
    border-radius: 999px;
    padding: 8px 16px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
    text-decoration: none;
    color: #fff;
    cursor: pointer;
}

.btn-action.warning {
    background: linear-gradient(135deg, #f59e0b, #f97316);
    box-shadow: 0 10px 20px rgba(249, 115, 22, 0.25);
}

.btn-action.success {
    background: linear-gradient(135deg, #34d399, #10b981);
    box-shadow: 0 10px 20px rgba(16, 185, 129, 0.22);
}

.btn-action.danger {
    background: linear-gradient(135deg, #f87171, #ef4444);
    box-shadow: 0 10px 20px rgba(239, 68, 68, 0.22);
}

.btn-action:hover {
    transform: translateY(-2px);
}

@media (max-width: 992px) {
    .retur-title {
        font-size: 1.8rem;
    }
}

@media (max-width: 576px) {
    .retur-hero {
        padding: 24px;
    }

    .retur-card {
        padding: 20px;
    }

    .retur-card__actions {
        justify-content: flex-start;
    }

    .retur-card__actions form,
    .retur-card__actions a {
        width: 100%;
    }

    .btn-action {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endsection
