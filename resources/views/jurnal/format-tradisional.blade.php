{{-- Format Jurnal Tradisional dengan Indentasi --}}
<div class="jurnal-tradisional">
    <div class="jurnal-header mb-3">
        <h5>{{ $journal->memo }}</h5>
        <p class="text-muted">Tanggal: {{ \Carbon\Carbon::parse($journal->tanggal)->format('d/m/Y') }}</p>
    </div>
    
    <div class="jurnal-content">
        <table class="table table-borderless">
            <thead>
                <tr>
                    <th width="60%">Nama Akun</th>
                    <th width="20%" class="text-end">Debit</th>
                    <th width="20%" class="text-end">Credit</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $debitLines = $journal->lines->where('debit', '>', 0);
                    $creditLines = $journal->lines->where('credit', '>', 0);
                @endphp
                
                {{-- Tampilkan semua akun debit dulu --}}
                @foreach($debitLines as $line)
                <tr>
                    <td>{{ $line->coa->nama_akun }}</td>
                    <td class="text-end">Rp {{ number_format($line->debit, 0, ',', '.') }}</td>
                    <td class="text-end">-</td>
                </tr>
                @endforeach
                
                {{-- Kemudian tampilkan akun kredit dengan indentasi --}}
                @foreach($creditLines as $line)
                <tr>
                    <td style="padding-left: 2rem;">{{ $line->coa->nama_akun }}</td>
                    <td class="text-end">-</td>
                    <td class="text-end">Rp {{ number_format($line->credit, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                
                {{-- Total --}}
                <tr class="border-top">
                    <td><strong>TOTAL</strong></td>
                    <td class="text-end"><strong>Rp {{ number_format($journal->lines->sum('debit'), 0, ',', '.') }}</strong></td>
                    <td class="text-end"><strong>Rp {{ number_format($journal->lines->sum('credit'), 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
.jurnal-tradisional {
    font-family: 'Courier New', monospace;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.jurnal-tradisional table {
    margin-bottom: 0;
}

.jurnal-tradisional th {
    border-bottom: 2px solid #dee2e6;
    font-weight: bold;
    background-color: #e9ecef;
}

.jurnal-tradisional td {
    padding: 8px 12px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
}

.jurnal-tradisional .border-top {
    border-top: 2px solid #dee2e6 !important;
}

/* Indentasi untuk akun kredit */
.jurnal-tradisional td[style*="padding-left"] {
    font-style: italic;
    color: #495057;
}
</style>