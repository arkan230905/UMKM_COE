@extends('layouts.app')

@section('title', 'Detail Aset')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="text-white">Detail Aset</h2>
        </div>
    </div>

    <!-- Informasi Aset -->
    <div class="card mb-4 bg-white">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Aset</h5>
        </div>
        <div class="card-body bg-white">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="40%" class="text-dark"><strong>Kode Aset</strong></td>
                            <td class="text-dark">: {{ $aset->kode_aset }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Nama Aset</strong></td>
                            <td class="text-dark">: {{ $aset->nama_aset }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Jenis Aset</strong></td>
                            <td class="text-dark">: {{ $aset->kategori->jenisAset->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Kategori Aset</strong></td>
                            <td class="text-dark">: {{ $aset->kategori->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Tanggal Pembelian</strong></td>
                            <td class="text-dark">: {{ \Carbon\Carbon::parse($aset->tanggal_beli)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Tanggal Mulai Penyusutan</strong></td>
                            <td class="text-dark">: {{ \Carbon\Carbon::parse($aset->tanggal_akuisisi)->format('d/m/Y') }}</td>
                        </tr>
                        @if($aset->metode_penyusutan === 'sum_of_years_digits' && $aset->tanggal_perolehan)
                        <tr>
                            <td class="text-dark"><strong>Tanggal Perolehan</strong></td>
                            <td class="text-dark">: {{ \Carbon\Carbon::parse($aset->tanggal_perolehan)->format('d/m/Y') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="40%" class="text-dark"><strong>Harga Perolehan</strong></td>
                            <td class="text-dark">: Rp {{ number_format($aset->harga_perolehan, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Biaya Perolehan</strong></td>
                            <td class="text-dark">: Rp {{ number_format($aset->biaya_perolehan ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Total Perolehan</strong></td>
                            <td class="text-dark"><strong>: Rp {{ number_format($totalPerolehan, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Nilai Residu</strong></td>
                            <td class="text-dark">: Rp {{ number_format($aset->nilai_residu, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Umur Manfaat</strong></td>
                            <td class="text-dark">: {{ $aset->umur_manfaat }} tahun</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Metode Penyusutan</strong></td>
                            <td class="text-dark">: {{ ucwords(str_replace('_', ' ', $aset->metode_penyusutan)) }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Akumulasi Penyusutan</strong></td>
                            <td class="text-dark">: Rp {{ number_format($asetSummary['akumulasi_penyusutan'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Nilai Buku Saat Ini</strong></td>
                            <td class="text-dark"><strong>: Rp {{ number_format($asetSummary['nilai_buku_saat_ini'] ?? $totalPerolehan, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-dark"><strong>Status Posting</strong></td>
                            <td class="text-dark">: 
                                @if($asetSummary['sudah_diposting'] ?? false)
                                    <span class="badge bg-success">Sudah Diposting</span>
                                @else
                                    <span class="badge bg-warning">Belum Diposting</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
    <!-- Hasil Perhitungan Penyusutan -->
    <div class="card mb-4 bg-white" id="hasil_perhitungan_card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Hasil Perhitungan Penyusutan</h5>
        </div>
        <div class="card-body bg-white">
            <div class="row text-center">
                <div class="col-md-6 mb-3">
                    <div class="card bg-info bg-opacity-10 border border-info">
                        <div class="card-body">
                            <h6 class="text-dark">Penyusutan Per Bulan</h6>
                            <h3 class="text-dark mb-0">Rp {{ number_format($penyusutanPerBulan, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card bg-success bg-opacity-10 border border-success">
                        <div class="card-body">
                            <h6 class="text-dark">Penyusutan Per Tahun</h6>
                            <h3 class="text-dark mb-0">Rp {{ number_format($penyusutanPerTahun, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Penyusutan Per Tahun -->
    <div class="card mb-4 bg-white">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Jadwal Penyusutan Per Bulan</h5>
        </div>
        <div class="card-body bg-white">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>TAHUN/BULAN</th>
                            <th class="text-end">PENYUSUTAN</th>
                            <th class="text-end">AKUMULASI PENY</th>
                            <th class="text-end">NILAI BUKU</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($depreciationData as $index => $row)
                            <tr>
                                <td>{{ $row['tahun_bulan'] }}</td>
                                <td class="text-end">Rp {{ number_format($row['penyusutan'], 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($row['akumulasi_penyusutan'], 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($row['nilai_buku'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada jadwal penyusutan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi -->
    <div class="mb-4">
        <a href="{{ route('master-data.aset.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <a href="{{ route('master-data.aset.edit', $aset->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        
        @if($aset->status_posting !== 'posted')
            <button type="button" class="btn btn-success" onclick="showPostingModal()">
                <i class="bi bi-check-circle me-1"></i> Posting ke Jurnal
            </button>
        @else
            <button type="button" class="btn btn-warning" onclick="unpostAsset()">
                <i class="bi bi-x-circle me-1"></i> Batalkan Posting
            </button>
        @endif
    </div>
</div>

<!-- Modal Posting Aset -->
<div class="modal fade" id="postingModal" tabindex="-1" aria-labelledby="postingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="postingModalLabel">
                    <i class="bi bi-check-circle me-2"></i>Posting Aset ke Jurnal
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Posting aset akan membuat jurnal dengan <strong>nominal penyusutan bulan ini</strong>:
                    <ul class="mb-0 mt-2">
                        <li><strong>Debit:</strong> COA Aset ({{ $aset->assetCoa->nama_akun ?? 'Belum diset' }})</li>
                        <li><strong>Kredit:</strong> Kas/Bank atau Hutang (pilih di bawah)</li>
                    </ul>
                </div>
                
                @if(!$aset->asset_coa_id)
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        COA Aset belum diset. Silakan edit aset dan lengkapi COA Aset terlebih dahulu.
                    </div>
                @else
                    <form id="postingForm">
                        @csrf
                        <div class="mb-3">
                            <label for="coa_kredit_id" class="form-label">Pilih COA untuk Pembayaran <span class="text-danger">*</span></label>
                            <select class="form-select" id="coa_kredit_id" name="coa_kredit_id" required>
                                <option value="">-- Pilih COA --</option>
                                <optgroup label="Kas & Bank">
                                    @foreach(\App\Models\Coa::where('kode_akun', 'LIKE', '11%')->orderBy('kode_akun')->get() as $coa)
                                        <option value="{{ $coa->id }}">{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Hutang">
                                    @foreach(\App\Models\Coa::where('kode_akun', 'LIKE', '2%')->orderBy('kode_akun')->get() as $coa)
                                        <option value="{{ $coa->id }}">{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <small class="text-muted">Pilih Kas/Bank jika dibayar tunai, atau Hutang jika dibeli kredit</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nilai yang Akan Diposting</label>
                            @php
                                $penyusutanPerBulan = (float)($aset->penyusutan_per_bulan ?? 0);
                                
                                if ($penyusutanPerBulan <= 0) {
                                    // Fallback calculation
                                    $hargaPerolehan = (float)$aset->harga_perolehan;
                                    $umurManfaat = (int)$aset->umur_manfaat;
                                    $penyusutanPerBulan = $umurManfaat > 0 ? $hargaPerolehan / ($umurManfaat * 12) : 0;
                                }
                            @endphp
                            <input type="text" class="form-control" value="Rp {{ number_format($penyusutanPerBulan, 0, ',', '.') }}" readonly>
                            <small class="text-muted">Nominal penyusutan bulan ini dari data aset</small>
                        </div>
                    </form>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                @if($aset->coa_aset_id)
                    <button type="button" class="btn btn-success" onclick="postAsset()">
                        <i class="bi bi-check-circle me-1"></i> Posting
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const metodePenyusutan = '{{ $aset->metode_penyusutan }}';
    const hasilPerhitunganCard = document.getElementById('hasil_perhitungan_card');
    
    console.log('Metode Penyusutan:', metodePenyusutan);
    console.log('Card found:', hasilPerhitunganCard);
    
    // Sembunyikan/tampilkan hasil perhitungan berdasarkan metode
    if (metodePenyusutan === 'garis_lurus') {
        // Tampilkan untuk metode garis lurus
        hasilPerhitunganCard.style.display = 'block';
        console.log('Menampilkan hasil perhitungan untuk garis lurus');
    } else if (metodePenyusutan === 'saldo_menurun' || metodePenyusutan === 'sum_of_years_digits') {
        // Sembunyikan untuk metode saldo menurun dan jumlah angka tahun
        hasilPerhitunganCard.style.display = 'none';
        console.log('Menyembunyikan hasil perhitungan untuk metode:', metodePenyusutan);
    }
});

function showPostingModal() {
    const modal = new bootstrap.Modal(document.getElementById('postingModal'));
    modal.show();
}

function postAsset() {
    const coaKreditId = document.getElementById('coa_kredit_id').value;
    
    if (!coaKreditId) {
        alert('Silakan pilih COA untuk pembayaran');
        return;
    }
    
    if (!confirm('Apakah Anda yakin ingin memposting aset ini ke jurnal?')) {
        return;
    }
    
    // Disable button
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memproses...';
    
    fetch('{{ route("master-data.aset.post-to-journal", $aset->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            coa_kredit_id: coaKreditId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Berhasil: ' + data.message);
            location.reload();
        } else {
            alert('Gagal: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Posting';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memposting aset');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Posting';
    });
}

function unpostAsset() {
    if (!confirm('Apakah Anda yakin ingin membatalkan posting aset ini? Jurnal terkait akan dihapus.')) {
        return;
    }
    
    // Disable button
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memproses...';
    
    fetch('{{ route("master-data.aset.unpost-from-journal", $aset->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Berhasil: ' + data.message);
            location.reload();
        } else {
            alert('Gagal: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-x-circle me-1"></i> Batalkan Posting';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat membatalkan posting');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-x-circle me-1"></i> Batalkan Posting';
    });
}
</script>
@endsection
