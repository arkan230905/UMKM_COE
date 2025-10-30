@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">BOP Budget - {{ \Carbon\Carbon::parse($periode)->isoFormat('MMMM YYYY') }}</h5>
            <div>
                <form method="GET" class="form-inline">
                    <input type="month" name="periode" id="periode" class="form-control" 
                           value="{{ $periode }}" onchange="this.form.submit()">
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="bop-budget-table" class="table table-bordered table-striped" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th class="text-end">Budget</th>
                            <th class="text-end">Aktual</th>
                            <th class="text-end">Selisih</th>
                            <th class="text-center">%</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus BOP Budget ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<style>
    .text-danger { font-weight: bold; }
    .text-success { font-weight: bold; }
    .table th, .table td { vertical-align: middle; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {
        const table = $('#bop-budget-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("master-data.bop-budget.index") }}',
                data: function(d) {
                    d.periode = '{{ $periode }}';
                }
            },
            columns: [
                { 
                    data: 'DT_RowIndex', 
                    name: 'DT_RowIndex', 
                    orderable: false, 
                    searchable: false, 
                    width: '30px',
                    className: 'text-center'
                },
                { 
                    data: 'kode_akun', 
                    name: 'kode_akun',
                    render: function(data, type, row) {
                        // Format kode akun dengan padding nol
                        return data ? data.padStart(6, '0').match(/.{1,2}/g).join('.') : '';
                    }
                },
                { 
                    data: 'nama_akun', 
                    name: 'nama_akun' 
                },
                { 
                    data: 'jumlah_budget', 
                    name: 'jumlah_budget', 
                    className: 'text-end',
                    render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ')
                },
                { 
                    data: 'actual_amount', 
                    name: 'actual_amount', 
                    className: 'text-end',
                    render: function(data, type, row) {
                        var classColor = parseFloat(row.actual_amount) > parseFloat(row.jumlah_budget) ? 'text-danger' : 'text-success';
                        return '<span class="' + classColor + '">' + $.fn.dataTable.render.number('.', ',', 0, 'Rp ').display(data) + '</span>';
                    }
                },
                { 
                    data: 'variance', 
                    name: 'variance', 
                    className: 'text-end',
                    render: function(data, type, row) {
                        var classColor = parseFloat(data) < 0 ? 'text-danger' : 'text-success';
                        return '<span class="' + classColor + '">' + $.fn.dataTable.render.number('.', ',', 0, 'Rp ').display(data) + '</span>';
                    }
                },
                { 
                    data: 'variance_percent', 
                    name: 'variance_percent', 
                    className: 'text-center',
                    render: function(data, type, row) {
                        var classColor = parseFloat(data) < 0 ? 'text-danger' : 'text-success';
                        return '<span class="' + classColor + '">' + parseFloat(data).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%</span>';
                    }
                },
                { 
                    data: 'keterangan', 
                    name: 'keterangan', 
                    defaultContent: '-',
                    render: function(data) {
                        return data || '-';
                    }
                },
                { 
                    data: 'action', 
                    name: 'action', 
                    orderable: false, 
                    searchable: false, 
                    className: 'text-center',
                    width: '100px'
                }
            ],
            order: [[1, 'asc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
            },
            drawCallback: function() {
                $('.delete-btn').click(function(e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    const url = '{{ route("master-data.bop-budget.destroy", ":id") }}'.replace(':id', id);
                    $('#deleteForm').attr('action', url);
                    $('#deleteModal').modal('show');
                });
            }
        });

        // Handle form submission for delete
        $('#deleteForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.status) {
                        $('#deleteModal').modal('hide');
                        showAlert('success', response.message);
                        table.ajax.reload();
                    } else {
                        showAlert('error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showAlert('error', response?.message || 'Terjadi kesalahan saat menghapus data');
                }
            });
        });

        // Show alert function
        function showAlert(type, message) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>`;
            
            $('.alert').remove();
            $('.card-body').prepend(alertHtml);
            
            // Auto hide alert after 5 seconds
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
    });
</script>
@endpush
