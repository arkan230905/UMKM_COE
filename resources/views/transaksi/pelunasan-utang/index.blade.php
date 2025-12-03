@extends('layouts.app')

@section('title', 'Daftar Pelunasan Utang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/datatables/media/css/jquery.dataTables.min.css') }}">
    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.5rem;
            margin-left: 0.25rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #4e73df;
            color: white !important;
            border: 1px solid #4e73df;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #eaecf4;
            color: #4e73df !important;
        }
    </style>
@endpush

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Daftar Pelunasan Utang</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Pelunasan Utang</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between w-100">
                        <h4>Daftar Pelunasan Utang</h4>
                        <a href="{{ route('transaksi.pelunasan-utang.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Pelunasan
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-1">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Kode Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Pembelian</th>
                                    <th>Vendor</th>
                                    <th class="text-right">Jumlah</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pelunasanUtang as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->kode_transaksi }}</td>
                                    <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $item->pembelian->kode_pembelian ?? '-' }}</td>
                                    <td>{{ $item->pembelian->vendor->nama ?? '-' }}</td>
                                    <td class="text-right">{{ format_rupiah($item->jumlah) }}</td>
                                    <td>{!! $item->status_badge !!}</td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('transaksi.pelunasan-utang.show', $item->id) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('transaksi.pelunasan-utang.print', $item->id) }}" 
                                               class="btn btn-sm btn-warning" 
                                               target="_blank"
                                               title="Cetak">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <form action="{{ route('transaksi.pelunasan-utang.destroy', $item->id) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('library/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/pelunasan-utang.js') }}"></script>
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
                    { "orderable": false, "targets": [0, 7] },
                    { "searchable": false, "targets": [0, 7] },
                    { "className": "text-center", "targets": [0, 7] },
                    { "className": "text-right", "targets": [5] }
                ],
                "order": [[2, 'desc']] // Urutkan berdasarkan tanggal terbaru
            });

            // Inisialisasi select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Pilih salah satu',
                allowClear: true
            });

            // Format mata uang
            function formatRupiah(angka) {
                var number_string = angka.toString().replace(/[^,\d]/g, '');
                var split = number_string.split(',');
                var sisa = split[0].length % 3;
                var rupiah = split[0].substr(0, sisa);
                var ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                
                if (ribuan) {
                    var separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                
                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return rupiah ? 'Rp ' + rupiah : '';
            }

            // Format input angka
            $('.currency').on('keyup', function() {
                var value = $(this).val().replace(/[^\d]/g, '');
                $(this).val(formatRupiah(value));
            });
        });
    </script>
    
    @if(session('success'))
    <script>
        $(document).ready(function() {
            toastr.success('{{ session('success') }}', 'Sukses');
        });
    </script>
    @endif
    
    @if($errors->any())
    <script>
        $(document).ready(function() {
            @foreach($errors->all() as $error)
                toastr.error('{{ $error }}', 'Error');
            @endforeach
        });
    </script>
    @endif
@endpush
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle bayar button click
        const modalBayar = document.getElementById('modalBayar');
        modalBayar.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const pembelianId = button.getAttribute('data-id');
            const sisaUtang = parseFloat(button.getAttribute('data-sisa'));
            const noPembelian = button.getAttribute('data-no-pembelian');
            
            const modal = this;
            modal.querySelector('#pembelian_id').value = pembelianId;
            modal.querySelector('#no_pembelian').value = noPembelian;
            modal.querySelector('#sisa_utang').textContent = formatRupiah(sisaUtang);
            modal.querySelector('#jumlah').max = sisaUtang;
            modal.querySelector('#jumlah').value = sisaUtang;
        });

        // Format Rupiah
        function formatRupiah(angka) {
            return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    });
</script>
@endpush
