{{-- JURNAL 1: ACCRUAL --}}
<div class="mb-4">
    <h6 class="mb-3">
        <strong>✅ Jurnal 1: Penggajian</strong>
    </h6>
    
    @if($jurnalAccrual->count() > 0)
        <p class="text-muted small">Tanggal: <strong>{{ $penggajian->tanggal_penggajian->format('d-m-Y') }}</strong></p>
        
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th width="10%">Kode</th>
                        <th width="35%">Nama Akun</th>
                        <th width="15%" class="text-end">Debit</th>
                        <th width="15%" class="text-end">Kredit</th>
                        <th width="25%" class="text-center">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalDebit = 0; $totalKredit = 0; @endphp
                    @foreach($jurnalAccrual as $jurnal)
                        <tr>
                            <td><strong>{{ $jurnal->coa->kode_akun ?? '' }}</strong></td>
                            <td>{{ $jurnal->coa->nama_akun ?? '' }}</td>
                            <td class="text-end">
                                @if($jurnal->debit > 0)
                                    <span class="text-dark"><strong>Rp {{ number_format($jurnal->debit, 0, ',', '.') }}</strong></span>
                                    @php $totalDebit += $jurnal->debit; @endphp
                                @endif
                            </td>
                            <td class="text-end">
                                @if($jurnal->kredit > 0)
                                    <span class="text-dark"><strong>Rp {{ number_format($jurnal->kredit, 0, ',', '.') }}</strong></span>
                                    @php $totalKredit += $jurnal->kredit; @endphp
                                @endif
                            </td>
                            <td class="text-muted small text-center">{{ $jurnal->keterangan }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="2">TOTAL</th>
                        <th class="text-end"><strong>Rp {{ number_format($totalDebit, 0, ',', '.') }}</strong></th>
                        <th class="text-end"><strong>Rp {{ number_format($totalKredit, 0, ',', '.') }}</strong></th>
                        <th class="text-center">
                            @if($totalDebit === $totalKredit)
                                <span class="badge bg-success">✓ Seimbang</span>
                            @else
                                <span class="badge bg-danger">✗ Tidak Seimbang</span>
                            @endif
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="alert alert-warning small">
            ⚠️ Jurnal accrual belum dibuat
        </div>
    @endif
</div>

<hr>

{{-- JURNAL 2: PEMBAYARAN --}}
<div class="mb-4">
    <h6 class="mb-3">
        <strong>✅ Jurnal 2: Pembayaran Gaji</strong>
    </h6>
    
    @if($jurnalPembayaran->count() > 0)
        <p class="text-muted small">Tanggal: <strong>{{ $penggajian->tanggal_dibayar ? \Carbon\Carbon::parse($penggajian->tanggal_dibayar)->format('d-m-Y') : '-' }}</strong></p>
        
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th width="10%">Kode</th>
                        <th width="35%">Nama Akun</th>
                        <th width="15%" class="text-end">Debit</th>
                        <th width="15%" class="text-end">Kredit</th>
                        <th width="25%" class="text-center">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalDebit2 = 0; $totalKredit2 = 0; @endphp
                    @foreach($jurnalPembayaran as $jurnal)
                        <tr>
                            <td><strong>{{ $jurnal->coa->kode_akun ?? '' }}</strong></td>
                            <td>{{ $jurnal->coa->nama_akun ?? '' }}</td>
                            <td class="text-end">
                                @if($jurnal->debit > 0)
                                    <span class="text-dark"><strong>Rp {{ number_format($jurnal->debit, 0, ',', '.') }}</strong></span>
                                    @php $totalDebit2 += $jurnal->debit; @endphp
                                @endif
                            </td>
                            <td class="text-end">
                                @if($jurnal->kredit > 0)
                                    <span class="text-dark"><strong>Rp {{ number_format($jurnal->kredit, 0, ',', '.') }}</strong></span>
                                    @php $totalKredit2 += $jurnal->kredit; @endphp
                                @endif
                            </td>
                            <td class="text-muted small text-center">{{ $jurnal->keterangan }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="2">TOTAL</th>
                        <th class="text-end"><strong>Rp {{ number_format($totalDebit2, 0, ',', '.') }}</strong></th>
                        <th class="text-end"><strong>Rp {{ number_format($totalKredit2, 0, ',', '.') }}</strong></th>
                        <th class="text-center">
                            @if($totalDebit2 === $totalKredit2)
                                <span class="badge bg-success">✓ Seimbang</span>
                            @else
                                <span class="badge bg-danger">✗ Tidak Seimbang</span>
                            @endif
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        @if($penggajian->status_pembayaran === 'belum_lunas')
            <div class="alert alert-info small">
                ⏳ Jurnal pembayaran akan dibuat setelah status diubah menjadi "Lunas"
            </div>
        @else
            <div class="alert alert-warning small">
                ⚠️ Jurnal pembayaran belum ditemukan
            </div>
        @endif
    @endif
</div>
