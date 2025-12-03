<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jurnal Umum</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 5px 0;
        }
        .info {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 6px;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: left;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            border-radius: 3px;
            background-color: #6c757d;
            color: white;
        }
        .badge-debit {
            background-color: #0dcaf0;
        }
        .badge-kredit {
            background-color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>JURNAL UMUM</h2>
        <p>
            @if($from && $to)
                Periode: {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
            @elseif($from)
                Dari: {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}
            @elseif($to)
                Sampai: {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
            @else
                Semua Periode
            @endif
        </p>
        @if($refType || $refId)
            <p>
                @if($refType) Ref Type: {{ $refType }} @endif
                @if($refId) | Ref ID: {{ $refId }} @endif
            </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%">Tanggal</th>
                <th style="width: 10%">Ref</th>
                <th style="width: 18%">Memo</th>
                <th style="width: 8%">Kode Akun</th>
                <th style="width: 24%">Nama Akun</th>
                <th style="width: 16%" class="text-end">Debit</th>
                <th style="width: 16%" class="text-end">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDebit = 0;
                $totalKredit = 0;
            @endphp
            @forelse($entries as $e)
                @foreach($e->lines as $i => $l)
                    @php
                        $totalDebit += $l->debit ?? 0;
                        $totalKredit += $l->credit ?? 0;
                    @endphp
                    <tr>
                        @if($i===0)
                            <td rowspan="{{ $e->lines->count() }}">{{ \Carbon\Carbon::parse($e->tanggal)->format('d/m/Y') }}</td>
                            <td rowspan="{{ $e->lines->count() }}">{{ $e->ref_type }}#{{ $e->ref_id }}</td>
                            <td rowspan="{{ $e->lines->count() }}">{{ $e->memo }}</td>
                        @endif
                        <td>
                            <strong>{{ $l->account->code ?? '-' }}</strong>
                            <span class="badge {{ ($l->debit ?? 0) > 0 ? 'badge-debit' : 'badge-kredit' }}">
                                {{ ($l->debit ?? 0) > 0 ? 'D' : 'K' }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $l->account->name ?? 'Akun tidak ditemukan' }}</strong>
                            @if($l->account)
                                <br><small style="color: #666;">({{ $l->account->type ?? '' }})</small>
                            @endif
                        </td>
                        <td class="text-end">{{ $l->debit > 0 ? 'Rp '.number_format($l->debit, 0, ',', '.') : '-' }}</td>
                        <td class="text-end">{{ $l->credit > 0 ? 'Rp '.number_format($l->credit, 0, ',', '.') : '-' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data jurnal</td>
                </tr>
            @endforelse
        </tbody>
        @if($entries->count() > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-end"><strong>TOTAL:</strong></td>
                <td class="text-end"><strong>Rp {{ number_format($totalDebit, 0, ',', '.') }}</strong></td>
                <td class="text-end"><strong>Rp {{ number_format($totalKredit, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div style="margin-top: 30px; font-size: 9px;">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
