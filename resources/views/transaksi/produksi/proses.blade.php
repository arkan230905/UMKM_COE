@extends('layouts.app')

@section('title', 'Kelola Proses Produksi')

@section('head')
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-tasks me-2"></i>Kelola Proses Produksi
        </h2>
        <a href="{{ route('transaksi.produksi.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Info Produksi -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Informasi Produksi</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="fw-bold">Produk:</label>
                    <p>{{ $produksi->produk->nama_produk }}</p>
                </div>
                <div class="col-md-3">
                    <label class="fw-bold">Tanggal:</label>
                    <p>{{ \Carbon\Carbon::parse($produksi->tanggal)->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-3">
                    <label class="fw-bold">Qty Produksi:</label>
                    <p>{{ number_format($produksi->qty_produksi, 0, ',', '.') }} pcs</p>
                </div>
                <div class="col-md-3">
                    <label class="fw-bold">Status:</label>
                    <p>{!! $produksi->status_badge !!}</p>
                </div>
            </div>
            
            <!-- Current Time and Progress Bar -->
            <div class="mt-3">
                <div class="row">
                    <div class="col-md-6">
                        <label class="fw-bold">Progress Produksi:</label>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: {{ $produksi->progress_percentage }}%"
                                 aria-valuenow="{{ $produksi->progress_percentage }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ $produksi->actual_proses_selesai }}/{{ $produksi->total_proses }} Proses ({{ $produksi->progress_percentage }}%)
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Waktu Saat Ini:</label>
                        <p class="mb-0">
                            <span id="current-time" class="badge bg-info fs-6">{{ now()->format('d/m/Y H:i:s') }}</span>
                        </p>
                        <small class="text-muted">Timezone: Asia/Jakarta</small>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Proses -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Tahapan Proses Produksi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px">Urutan</th>
                            <th>Nama Proses</th>
                            <th>Status</th>
                            <th class="text-end">Biaya BTKL</th>
                            <th class="text-end">Biaya BOP</th>
                            <th class="text-end">Total Biaya</th>
                            <th>Waktu</th>
                            <th class="text-center" style="width: 200px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produksi->proses as $proses)
                            <tr class="{{ $proses->status === 'sedang_dikerjakan' ? 'table-primary' : '' }}">
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $proses->urutan }}</span>
                                </td>
                                <td>
                                    <strong>{{ $proses->nama_proses }}</strong>
                                    @if($proses->catatan)
                                        <br><small class="text-muted">{{ $proses->catatan }}</small>
                                    @endif
                                </td>
                                <td>{!! $proses->status_badge !!}</td>
                                <td class="text-end">Rp {{ number_format($proses->biaya_btkl, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($proses->biaya_bop, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">Rp {{ number_format($proses->total_biaya_proses, 0, ',', '.') }}</td>
                                <td>
                                    @if($proses->waktu_mulai)
                                        <small>Mulai: {{ $proses->waktu_mulai->format('d/m/Y H:i:s') }}</small>
                                    @endif
                                    @if($proses->waktu_selesai)
                                        <br><small>Selesai: {{ $proses->waktu_selesai->format('d/m/Y H:i:s') }}</small>
                                        <br><small class="text-success">Durasi: {{ $proses->formatted_duration }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($proses->status === 'pending')
                                        @php
                                            // Check if any other process is currently running
                                            $hasRunningProcess = $produksi->proses->where('status', 'sedang_dikerjakan')->count() > 0;
                                        @endphp
                                        
                                        @if(!$hasRunningProcess)
                                            <form action="{{ route('transaksi.produksi.proses.mulai', $proses->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-play"></i> Mulai
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="fas fa-hourglass-start"></i> Menunggu
                                            </button>
                                        @endif
                                    @elseif($proses->status === 'sedang_dikerjakan')
                                        <form action="{{ route('transaksi.produksi.proses.selesai', $proses->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Selesaikan
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Selesai
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total Biaya Produksi:</td>
                            <td class="text-end fw-bold">Rp {{ number_format($produksi->total_btkl, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($produksi->total_bop, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($produksi->total_btkl + $produksi->total_bop, 0, ',', '.') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh page every 30 seconds to ensure latest data
setTimeout(function() {
    location.reload();
}, 30000);

// Show current time for reference (server already corrected)
function updateCurrentTime() {
    const now = new Date();
    // No additional correction needed - server already provides correct time
    
    const timeString = now.toLocaleString('id-ID', {
        timeZone: 'Asia/Jakarta',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    
    // Update any element with id 'current-time' if exists
    const timeElement = document.getElementById('current-time');
    if (timeElement) {
        timeElement.textContent = timeString;
    }
}

// Update time every second
setInterval(updateCurrentTime, 1000);
updateCurrentTime();
</script>
@endsection
