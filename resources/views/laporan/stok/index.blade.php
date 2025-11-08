@extends('layouts.app')

@section('title', 'Laporan Stok Produk')

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/datatables/media/css/jquery.dataTables.min.css') }}">
@endpush

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Laporan Stok Produk</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Laporan Stok</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Filter Laporan</h4>
                </div>
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <div class="form-group row mb-4">
                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-2">Kategori</label>
                            <div class="col-sm-12 col-md-7">
                                <select name="kategori_id" class="form-control select2">
                                    <option value="">Semua Kategori</option>
                                    @foreach($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row mb-4">
                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-2">Minimum Stok</label>
                            <div class="col-sm-12 col-md-7">
                                <input type="number" name="min_stock" class="form-control" value="{{ request('min_stock') }}" placeholder="Kosongkan untuk menampilkan semua">
                            </div>
                        </div>
                        <div class="form-group row mb-4">
                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-2"></label>
                            <div class="col-sm-12 col-md-7">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('laporan.stok') }}" class="btn btn-secondary ml-2">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Data Stok Produk</h4>
                    <div class="card-header-action">
                        <a href="{{ route('laporan.stok') }}?{{ http_build_query(array_merge(request()->all(), ['export' => 'pdf'])) }}" 
                           class="btn btn-danger" target="_blank">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('laporan.stok') }}?{{ http_build_query(array_merge(request()->all(), ['export' => 'excel'])) }}" 
                           class="btn btn-success ml-2">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-1">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Kode Produk</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Stok</th>
                                    <th>Satuan</th>
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($produk as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->kode_produk ?? '-' }}</td>
                                    <td>{{ $item->nama }}</td>
                                    <td>{{ $item->kategori->nama ?? '-' }}</td>
                                    <td class="text-right">{{ number_format($item->stok, 0, ',', '.') }}</td>
                                    <td>{{ $item->satuan->nama ?? '-' }}</td>
                                    <td class="text-right">Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data stok produk</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('library/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable
            $('#table-1').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [0] },
                    { "searchable": false, "targets": [0] },
                    { "className": "text-right", "targets": [4, 6, 7] }
                ],
                "order": [[2, 'asc']] // Urutkan berdasarkan nama produk
            });

            // Inisialisasi select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Pilih kategori',
                allowClear: true
            });
        });
    </script>
@endpush
                        <option value="{{ $m->id }}" {{ ($itemId ?? '')==$m->id ? 'selected' : '' }}>{{ $m->nama_bahan }}</option>
                    @endforeach
                </select>
            @else
                <select name="item_id" class="form-select">
                    <option value="">-- Semua Produk --</option>
                    @foreach(($products ?? []) as $p)
                        <option value="{{ $p->id }}" {{ ($itemId ?? '')==$p->id ? 'selected' : '' }}>{{ $p->nama_produk }}</option>
                    @endforeach
                </select>
            @endif
        </div>
        <div class="col-md-3">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control">
        </div>
        <div class="col-md-3 text-end">
            <button class="btn btn-primary" type="submit">Terapkan</button>
        </div>
    </form>

    @if(!empty($itemId))
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="mb-2">Jumlah Stok Awal</h6>
                <div>
                    <span class="me-3"><strong>Qty:</strong> {{ rtrim(rtrim(number_format($saldoAwalQty ?? 0,4,',','.'),'0'),',') }}</span>
                    <span><strong>Nilai:</strong> Rp {{ number_format($saldoAwalNilai ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="mb-2">Kartu Stok</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:12%">Tanggal</th>
                                <th>Referensi</th>
                                <th class="text-end">Masuk (Qty)</th>
                                <th class="text-end">Masuk (Rp)</th>
                                <th class="text-end">Keluar (Qty)</th>
                                <th class="text-end">Keluar (Rp)</th>
                                <th class="text-end">Jumlah Stok (Qty)</th>
                                <th class="text-end">Jumlah Stok (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(($running ?? []) as $row)
                                <tr>
                                    <td>{{ $row['tanggal'] }}</td>
                                    <td>{{ $row['ref'] }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($row['in_qty'],4,',','.'),'0'),',') }}</td>
                                    <td class="text-end">{{ $row['in_nilai']>0 ? 'Rp '.number_format($row['in_nilai'],0,',','.') : '-' }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($row['out_qty'],4,',','.'),'0'),',') }}</td>
                                    <td class="text-end">{{ $row['out_nilai']>0 ? 'Rp '.number_format($row['out_nilai'],0,',','.') : '-' }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($row['saldo_qty'],4,',','.'),'0'),',') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['saldo_nilai'],0,',','.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="mb-2">Jumlah Stok per Item</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%">#</th>
                                <th>Nama</th>
                                <th class="text-end">Jumlah Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=1; @endphp
                            @if(($tipe ?? 'material')==='material')
                                @foreach(($materials ?? []) as $m)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $m->nama_bahan }}</td>
                                        <td class="text-end">
                                            {{-- Tampilkan jumlah stok --}}
                                            @if(isset($saldoPerItem[$m->id]))
                                                {{ number_format($saldoPerItem[$m->id], 0, ',', '.') }}
                                            @else
                                                0
                                            @endif
                                            
                                            {{-- Tampilkan satuan --}}
                                            @php
                                                $satuan = $m->satuan;
                                                
                                                // Jika satuan adalah string yang berisi JSON
                                                if (is_string($satuan) && strpos($satuan, '{') === 0) {
                                                    $decoded = json_decode($satuan, true);
                                                    if (json_last_error() === JSON_ERROR_NONE) {
                                                        echo isset($decoded['nama']) ? ' ' . $decoded['nama'] : '';
                                                    } else {
                                                        echo ' ' . $satuan;
                                                    }
                                                } 
                                                // Jika satuan adalah object atau array
                                                elseif (is_object($satuan) || is_array($satuan)) {
                                                    $satuan = (array) $satuan;
                                                    echo ' ' . ($satuan['nama'] ?? '');
                                                }
                                                // Jika satuan adalah string biasa
                                                else {
                                                    echo ' ' . $satuan;
                                                }
                                            @endphp
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                @foreach(($products ?? []) as $p)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $p->nama_produk }}</td>
                                        <td class="text-end">
                                            @if(isset($saldoPerItem[$p->id]))
                                                {{ number_format($saldoPerItem[$p->id], 0, ',', '.') }}
                                            @else
                                                0
                                            @endif
                                            pcs
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
