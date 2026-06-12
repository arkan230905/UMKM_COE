@extends('layouts.app')

@section('title', 'Detail Aset')

@section('content')
<div style="background-color: #F5F6F8; padding: 30px 20px; min-height: 100vh;">
    <div class="container-fluid" style="max-width: 1400px;">
        
        <!-- HEADER HALAMAN -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: #1a1a2e; margin: 0;">Detail Aset</h1>
                <p style="font-size: 14px; color: #6b7280; margin: 5px 0 0 0;">Informasi lengkap aset dan penyusutan</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('master-data.aset.index') }}" style="padding: 10px 20px; background-color: #e5e7eb; color: #374151; border: none; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 14px; cursor: pointer;">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
                
                @if(!$aset->is_posted)
                    <button id="btnPosting" onclick="postAset()" style="padding: 10px 20px; background-color: #10b981; color: white; border: none; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 14px; cursor: pointer;">
                        <i class="fas fa-paper-plane me-2"></i>Posting ke Jurnal Penyesuaian
                    </button>
                @endif
                
                <a href="{{ route('master-data.aset.edit', $aset->id) }}" style="padding: 10px 20px; background-color: #7A4F2A; color: white; border: none; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 14px; cursor: pointer;">
                    <i class="fas fa-edit me-2"></i>Edit Aset
                </a>
            </div>
        </div>

        <!-- SUMMARY CARDS -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <!-- Harga Perolehan -->
            <div style="background-color: #FFFFFF; border: 0.5px solid #e0d8ce; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 50px; height: 50px; background-color: #FEF3C7; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-tag" style="color: #8B5E34; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: #6b7280; margin: 0; font-weight: 500;">Harga Perolehan</p>
                        <p style="font-size: 20px; font-weight: 700; color: #1a1a2e; margin: 5px 0 0 0;">Rp {{ number_format($aset->harga_perolehan, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Nilai Buku Saat Ini -->
            <div style="background-color: #FFFFFF; border: 0.5px solid #e0d8ce; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 50px; height: 50px; background-color: #DBEAFE; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-book" style="color: #1e40af; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: #6b7280; margin: 0; font-weight: 500;">Nilai Buku Saat Ini</p>
                        <p style="font-size: 20px; font-weight: 700; color: #1a1a2e; margin: 5px 0 0 0;">Rp {{ number_format($asetSummary['nilai_buku_saat_ini'] ?? $totalPerolehan, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Akumulasi Penyusutan -->
            <div style="background-color: #FFFFFF; border: 0.5px solid #e0d8ce; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 50px; height: 50px; background-color: #F3E8FF; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chart-line" style="color: #7c3aed; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: #6b7280; margin: 0; font-weight: 500;">Akumulasi Penyusutan</p>
                        <p style="font-size: 20px; font-weight: 700; color: #1a1a2e; margin: 5px 0 0 0;">Rp {{ number_format($asetSummary['akumulasi_penyusutan'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Status Posting -->
            <div style="background-color: #FFFFFF; border: 0.5px solid #e0d8ce; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 50px; height: 50px; background-color: #ECFDF5; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check-circle" style="color: #059669; font-size: 24px;"></i>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: #6b7280; margin: 0; font-weight: 500;">Status Posting</p>
                        <div style="margin-top: 5px;">
                            @if($aset->is_posted)
                                <span style="background-color: #D1FAE5; color: #065F46; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Sudah Posting</span>
                            @else
                                <span style="background-color: #FEF3C7; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Belum Posting</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BARIS 2: INFORMASI ASET (FULL WIDTH) -->
        <div style="margin-bottom: 30px;">
            <!-- INFORMASI ASET CARD -->
            <div style="background-color: #FFFFFF; border: 0.5px solid #e0d8ce; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <!-- Header Card -->
                <div style="padding: 16px; border-bottom: 0.5px solid #e0d8ce; background-color: #FFFFFF;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #5a3a1a; margin: 0;">
                        <i class="fas fa-file-alt me-2"></i>Informasi Aset
                    </h3>
                </div>
                
                <!-- Isi Card -->
                <div style="padding: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
                    <!-- Kolom Kiri -->
                    <div>
                        <!-- Kode Aset -->
                        <div style="display: grid; grid-template-columns: 160px 12px 1fr; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eef0f3;">
                            <span style="font-weight: 600; color: #111827; font-size: 13px;">Kode Aset</span>
                            <span style="color: #6b7280; text-align: center; font-size: 13px;">:</span>
                            <span style="color: #374151; font-size: 13px; font-family: inherit; word-break: break-word;">{{ $aset->kode_aset }}</span>
                        </div>
                        <!-- Nama Aset -->
                        <div style="display: grid; grid-template-columns: 160px 12px 1fr; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eef0f3;">
                            <span style="font-weight: 600; color: #111827; font-size: 13px;">Nama Aset</span>
                            <span style="color: #6b7280; text-align: center; font-size: 13px;">:</span>
                            <span style="color: #374151; font-size: 13px; word-break: break-word;">{{ $aset->nama_aset }}</span>
                        </div>
                        <!-- Jenis Aset -->
                        <div style="display: grid; grid-template-columns: 160px 12px 1fr; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eef0f3;">
                            <span style="font-weight: 600; color: #111827; font-size: 13px;">Jenis Aset</span>
                            <span style="color: #6b7280; text-align: center; font-size: 13px;">:</span>
                            <span style="color: #374151; font-size: 13px; word-break: break-word;">{{ $aset->kategori->jenisAset->nama ?? '-' }}</span>
                        </div>
                        <!-- Kategori Aset -->
                        <div style="display: grid; grid-template-columns: 160px 12px 1fr; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eef0f3;">
                            <span style="font-weight: 600; color: #111827; font-size: 13px;">Kategori Aset</span>
                            <span style="color: #6b7280; text-align: center; font-size: 13px;">:</span>
                            <span style="color: #374151; font-size: 13px; word-break: break-word;">{{ $aset->kategori->nama ?? '-' }}</span>
                        </div>
                        <!-- Tanggal Pembelian -->
                        <div style="display: grid; grid-template-columns: 160px 12px 1fr; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eef0f3;">
                            <span style="font-weight: 600; color: #111827; font-size: 13px;">Tanggal Pembelian</span>
                            <span style="color: #6b7280; text-align: center; font-size: 13px;">:</span>
                            <span style="color: #374151; font-size: 13px; word-break: break-word;">{{ \Carbon\Carbon::parse($aset->tanggal_beli)->format('d/m/Y') }}</span>
                        </div>
                        <!-- Tanggal Mulai Penyusutan -->
                        <div style="display: grid; grid-template-columns: 160px 12px 1fr; align-items: center; gap: 8px; padding: 10px 0;">
                            <span style="font-weight: 600; color: #111827; font-size: 13px;">Tanggal Mulai Penyusutan</span>
                            <span style="color: #6b7280; text-align: center; font-size: 13px;">:</span>
                            <span style="color: #374151; font-size: 13px; word-break: break-word;">{{ \Carbon\Carbon::parse($aset->tanggal_akuisisi)->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div>
                        <!-- Harga Perolehan -->
                        <div style="display: grid; grid-template-columns: 160px 12px 1fr; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eef0f3;">
                            <span style="font-weight: 600; color: #111827; font-size: 13px;">Harga Perolehan</span>
                            <span style="color: #6b7280; text-align: center; font-size: 13px;">:</span>
                            <span style="color: #374151; font-size: 13px; word-break: break-word;">Rp {{ number_format($aset->harga_perolehan, 0, ',', '.') }}</span>
                        </div>
                        <!-- Biaya Perolehan -->
                        <div style="display: grid; grid-template-columns: 160px 12px 1fr; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eef0f3;">
                            <span style="font-weight: 600; color: #111827; font-size: 13px;">Biaya Perolehan</span>
                            <span style="color: #6b7280; text-align: center; font-size: 13px;">:</span>
                            <span style="color: #374151; font-size: 13px; word-break: break-word;">Rp {{ number_format($aset->biaya_perolehan ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <!-- Total Perolehan -->
                        <div style="display: grid; grid-template-columns: 160px 12px 1fr; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eef0f3;">
                            <span style="font-weight: 600; color: #111827; font-size: 13px;">Total Perolehan</span>
                            <span style="color: #6b7280; text-align: center; font-size: 13px;">:</span>
                            <span style="color: #374151; font-size: 13px; font-weight: 600; word-break: break-word;">Rp {{ number_format($totalPerolehan, 0, ',', '.') }}</span>
                        </div>
                        <!-- Nilai Residu -->
                        <div style="display: grid; grid-template-columns: 160px 12px 1fr; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eef0f3;">
                            <span style="font-weight: 600; color: #111827; font-size: 13px;">Nilai Residu</span>
                            <span style="color: #6b7280; text-align: center; font-size: 13px;">:</span>
                            <span style="color: #374151; font-size: 13px; word-break: break-word;">Rp {{ number_format($aset->nilai_residu, 0, ',', '.') }}</span>
                        </div>
                        <!-- Umur Manfaat -->
                        <div style="display: grid; grid-template-columns: 160px 12px 1fr; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eef0f3;">
                            <span style="font-weight: 600; color: #111827; font-size: 13px;">Umur Manfaat</span>
                            <span style="color: #6b7280; text-align: center; font-size: 13px;">:</span>
                            <span style="color: #374151; font-size: 13px; word-break: break-word;">{{ $aset->umur_manfaat }} tahun</span>
                        </div>
                        <!-- Metode Penyusutan -->
                        <div style="display: grid; grid-template-columns: 160px 12px 1fr; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eef0f3;">
                            <span style="font-weight: 600; color: #111827; font-size: 13px;">Metode Penyusutan</span>
                            <span style="color: #6b7280; text-align: center; font-size: 13px;">:</span>
                            <span style="color: #374151; font-size: 13px; word-break: break-word;">{{ ucwords(str_replace('_', ' ', $aset->metode_penyusutan)) }}</span>
                        </div>
                        <!-- Penyusutan Per Bulan -->
                        <div style="display: grid; grid-template-columns: 160px 12px 1fr; align-items: center; gap: 8px; padding: 10px 0;">
                            <span style="font-weight: 600; color: #111827; font-size: 13px;">Penyusutan Per Bulan</span>
                            <span style="color: #6b7280; text-align: center; font-size: 13px;">:</span>
                            <span style="color: #374151; font-size: 13px; word-break: break-word;">Rp {{ number_format($penyusutanPerBulan, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BARIS 3: HASIL PERHITUNGAN PENYUSUTAN (FULL WIDTH) -->
        <div style="margin-bottom: 30px; width: 100%;">
            <!-- HASIL PERHITUNGAN PENYUSUTAN CARD -->
            <div style="background-color: #FFFFFF; border: 0.5px solid #e0d8ce; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <!-- Header Card -->
                <div style="padding: 16px; border-bottom: 0.5px solid #e0d8ce; background-color: #FFFFFF;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #5a3a1a; margin: 0;">
                        <i class="fas fa-calculator me-2"></i>Hasil Perhitungan Penyusutan
                    </h3>
                </div>
                
                <!-- Isi Card -->
                <div style="padding: 16px; text-align: center;">
                    <!-- Penyusutan Per Bulan -->
                    <div style="background-color: #F9FAFB; border: 0.5px solid #e0d8ce; border-radius: 10px; padding: 18px; margin-bottom: 15px; text-align: center;">
                        <p style="font-size: 12px; color: #6b7280; margin: 0 0 8px 0; font-weight: 500; text-align: center;">Penyusutan Per Bulan</p>
                        <p style="font-size: 24px; font-weight: 700; color: #1a1a2e; margin: 0; text-align: center;">Rp {{ number_format($penyusutanPerBulan, 0, ',', '.') }}</p>
                    </div>

                    <!-- Penyusutan Per Tahun -->
                    <div style="background-color: #F9FAFB; border: 0.5px solid #e0d8ce; border-radius: 10px; padding: 18px; text-align: center;">
                        <p style="font-size: 12px; color: #6b7280; margin: 0 0 8px 0; font-weight: 500; text-align: center;">Penyusutan Per Tahun</p>
                        <p style="font-size: 24px; font-weight: 700; color: #1a1a2e; margin: 0; text-align: center;">Rp {{ number_format($penyusutanPerTahun, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- BARIS 4: JADWAL PENYUSUTAN (FULL WIDTH) -->
        <div style="margin-bottom: 30px;">
            <!-- JADWAL PENYUSUTAN CARD -->
            <div style="background-color: #FFFFFF; border: 0.5px solid #e0d8ce; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <!-- Header Card -->
                <div style="padding: 16px; border-bottom: 0.5px solid #e0d8ce; background-color: #FFFFFF; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #5a3a1a; margin: 0;" id="jadwalTitle">
                        <i class="fas fa-calendar me-2"></i>Jadwal Penyusutan Per Bulan
                    </h3>
                    <select id="jadwalTipe" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #e0d8ce; font-size: 13px;" onchange="toggleJadwal(this.value)">
                        <option value="bulan">Per Bulan</option>
                        <option value="tahun">Per Tahun</option>
                    </select>
                </div>
                
                <!-- Isi Card -->
                <div style="padding: 16px;">
                    <div style="overflow-x: auto;" id="tableBulan">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #5a3a1a; color: white;">
                                    <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600;">Tahun/Bulan</th>
                                    <th style="padding: 12px; text-align: right; font-size: 12px; font-weight: 600;">Penyusutan</th>
                                    <th style="padding: 12px; text-align: right; font-size: 12px; font-weight: 600;">Akumulasi Penyusutan</th>
                                    <th style="padding: 12px; text-align: right; font-size: 12px; font-weight: 600;">Nilai Buku</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($depreciationData as $index => $row)
                                    <tr style="border-bottom: 0.5px solid #e0d8ce;">
                                        <td style="padding: 12px; font-size: 13px; color: #1a1a2e;">{{ $row['tahun_bulan'] }}</td>
                                        <td style="padding: 12px; text-align: right; font-size: 13px; color: #1a1a2e;">Rp {{ number_format($row['penyusutan'], 0, ',', '.') }}</td>
                                        <td style="padding: 12px; text-align: right; font-size: 13px; color: #1a1a2e;">Rp {{ number_format($row['akumulasi_penyusutan'], 0, ',', '.') }}</td>
                                        <td style="padding: 12px; text-align: right; font-size: 13px; color: #1a1a2e; font-weight: 500;">Rp {{ number_format($row['nilai_buku'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="padding: 20px; text-align: center; color: #9ca3af; font-size: 13px;">Belum ada jadwal penyusutan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Tabel Per Tahun -->
                    <div style="overflow-x: auto; display: none;" id="tableTahun">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #5a3a1a; color: white;">
                                    <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600;">Tahun</th>
                                    <th style="padding: 12px; text-align: right; font-size: 12px; font-weight: 600;">Penyusutan</th>
                                    <th style="padding: 12px; text-align: right; font-size: 12px; font-weight: 600;">Akumulasi Penyusutan</th>
                                    <th style="padding: 12px; text-align: right; font-size: 12px; font-weight: 600;">Nilai Buku</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($depreciationDataTahun as $index => $row)
                                    <tr style="border-bottom: 0.5px solid #e0d8ce;">
                                        <td style="padding: 12px; font-size: 13px; color: #1a1a2e;">{{ $row['tahun'] }}</td>
                                        <td style="padding: 12px; text-align: right; font-size: 13px; color: #1a1a2e;">Rp {{ number_format($row['penyusutan'], 0, ',', '.') }}</td>
                                        <td style="padding: 12px; text-align: right; font-size: 13px; color: #1a1a2e;">Rp {{ number_format($row['akumulasi_penyusutan'], 0, ',', '.') }}</td>
                                        <td style="padding: 12px; text-align: right; font-size: 13px; color: #1a1a2e; font-weight: 500;">Rp {{ number_format($row['nilai_buku'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="padding: 20px; text-align: center; color: #9ca3af; font-size: 13px;">Belum ada jadwal penyusutan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const metodePenyusutan = '{{ $aset->metode_penyusutan }}';
    console.log('Metode Penyusutan:', metodePenyusutan);
});

function toggleJadwal(tipe) {
    if (tipe === 'tahun') {
        document.getElementById('tableBulan').style.display = 'none';
        document.getElementById('tableTahun').style.display = 'block';
        document.getElementById('jadwalTitle').innerHTML = '<i class="fas fa-calendar me-2"></i>Jadwal Penyusutan Per Tahun';
    } else {
        document.getElementById('tableTahun').style.display = 'none';
        document.getElementById('tableBulan').style.display = 'block';
        document.getElementById('jadwalTitle').innerHTML = '<i class="fas fa-calendar me-2"></i>Jadwal Penyusutan Per Bulan';
    }
}

// Function untuk posting aset
function postAset() {
    if (!confirm('Apakah Anda yakin ingin memposting aset ini ke Jurnal Penyesuaian?')) {
        return;
    }
    
    const btn = document.getElementById('btnPosting');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
    }
    
    fetch('{{ route("master-data.aset.post", $aset->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            window.location.reload();
        } else {
            alert('❌ ' + (data.message || 'Terjadi kesalahan saat menyimpan aset'));
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Posting ke Jurnal Penyesuaian';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Terjadi kesalahan saat menyimpan aset. Silakan coba lagi.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Posting ke Jurnal Penyesuaian';
        }
    });
}

</script>
@endsection
