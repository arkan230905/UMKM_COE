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
                <a href="{{ route('master-data.aset.edit', $aset->id) }}" style="padding: 10px 20px; background-color: #7A4F2A; color: white; border: none; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 14px; cursor: pointer;">
                    <i class="fas fa-edit me-2"></i>Edit Aset
                </a>
                @if($aset->status_posting !== 'posted')
                    <button type="button" onclick="showPostingModal()" style="padding: 10px 20px; background-color: #10b981; color: white; border: none; border-radius: 8px; font-weight: 500; font-size: 14px; cursor: pointer;">
                        <i class="fas fa-check-circle me-2"></i>Posting
                    </button>
                @else
                    <button type="button" onclick="unpostAsset()" style="padding: 10px 20px; background-color: #f59e0b; color: white; border: none; border-radius: 8px; font-weight: 500; font-size: 14px; cursor: pointer;">
                        <i class="fas fa-times-circle me-2"></i>Batalkan Posting
                    </button>
                @endif
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
                            @if($asetSummary['sudah_diposting'] ?? false)
                                <span style="background-color: #DBEAFE; color: #1e40af; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Sudah Diposting</span>
                            @else
                                <span style="background-color: #FEF3C7; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Belum Diposting</span>
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
                <div style="padding: 16px; border-bottom: 0.5px solid #e0d8ce; background-color: #FFFFFF;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #5a3a1a; margin: 0;">
                        <i class="fas fa-calendar me-2"></i>Jadwal Penyusutan Per Bulan
                    </h3>
                </div>
                
                <!-- Isi Card -->
                <div style="padding: 16px;">
                    <div style="overflow-x: auto;">
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL POSTING ASET -->
<div class="modal fade" id="postingModal" tabindex="-1" aria-labelledby="postingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #10b981; color: white; border: none;">
                <h5 class="modal-title" id="postingModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Posting Aset ke Jurnal
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Posting aset akan membuat jurnal dengan <strong>nominal penyusutan bulan ini</strong>:
                    <ul class="mb-0 mt-2">
                        <li><strong>Debit:</strong> COA Aset ({{ $aset->assetCoa->nama_akun ?? 'Belum diset' }})</li>
                        <li><strong>Kredit:</strong> Kas/Bank atau Hutang (pilih di bawah)</li>
                    </ul>
                </div>
                
                @if(!$aset->asset_coa_id)
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
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
                        <i class="fas fa-check-circle me-1"></i> Posting
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const metodePenyusutan = '{{ $aset->metode_penyusutan }}';
    console.log('Metode Penyusutan:', metodePenyusutan);
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
    
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-hourglass-split me-1"></i> Memproses...';
    
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
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Posting';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memposting aset');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Posting';
    });
}

function unpostAsset() {
    if (!confirm('Apakah Anda yakin ingin membatalkan posting aset ini? Jurnal terkait akan dihapus.')) {
        return;
    }
    
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-hourglass-split me-1"></i> Memproses...';
    
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
            btn.innerHTML = '<i class="fas fa-times-circle me-1"></i> Batalkan Posting';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat membatalkan posting');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-times-circle me-1"></i> Batalkan Posting';
    });
}
</script>
@endsection
