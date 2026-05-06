@extends('layouts.app')

@section('title', 'Beban Operasional')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-file-invoice-dollar me-2"></i>Beban Operasional
        </h2>
        <div>
            <a href="{{ route('master-data.bop.index') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-chart-pie me-2"></i>BOP Proses
            </a>
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus me-2"></i>Tambah Beban Operasional
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label for="filterSearch" class="form-label">Cari Beban</label>
                    <input type="text" class="form-control" id="filterSearch" placeholder="Nama beban..." onkeyup="filterData()">
                </div>
                <div class="col-md-6">
                    <label for="filterStatus" class="form-label">Status</label>
                    <select class="form-select" id="filterStatus" onchange="filterData()">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 5%">No</th>
                            <th style="width: 15%">Kode</th>
                            <th style="width: 35%">Nama Beban</th>
                            <th style="width: 20%" class="text-end">Budget Bulanan</th>
                            <th style="width: 10%">Status</th>
                            <th style="width: 15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($bebanOperasional as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td><span class="badge bg-secondary">{{ $item->kode }}</span></td>
                            <td>{{ $item->nama_beban }}</td>
                            <td class="text-end">{{ $item->budget_bulanan_formatted }}</td>
                            <td>{!! $item->status_badge !!}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-warning" onclick="editItem({{ $item->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger" onclick="deleteItem({{ $item->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">Belum ada data Beban Operasional</p>
                                <button class="btn btn-primary" onclick="openAddModal()">
                                    <i class="fas fa-plus me-2"></i>Tambah Pertama
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Beban Operasional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addForm" onsubmit="saveData(event)">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Beban <span class="text-danger">*</span></label>
                        <input type="text" name="nama_beban" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Budget Bulanan <span class="text-danger">*</span></label>
                        <input type="number" name="budget_bulanan" class="form-control" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Beban Operasional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" onsubmit="updateData(event)">
                @csrf
                @method('PUT')
                <input type="hidden" id="editId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Beban <span class="text-danger">*</span></label>
                        <input type="text" id="editNamaBeban" name="nama_beban" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Budget Bulanan <span class="text-danger">*</span></label>
                        <input type="number" id="editBudget" name="budget_bulanan" class="form-control" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openAddModal() {
    document.getElementById('addForm').reset();
    new bootstrap.Modal(document.getElementById('addModal')).show();
}

function saveData(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    // Disable submit button to prevent double submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';

    fetch('{{ route("master-data.beban-operasional.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Gagal menyimpan data');
            // Re-enable button on error
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Simpan';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
        // Re-enable button on error
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Simpan';
    });
}

function editItem(id) {
    fetch(`{{ url('master-data/beban-operasional') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editId').value = data.data.id;
                document.getElementById('editNamaBeban').value = data.data.nama_beban;
                document.getElementById('editBudget').value = data.data.budget_bulanan;
                new bootstrap.Modal(document.getElementById('editModal')).show();
            }
        });
}

function updateData(event) {
    event.preventDefault();
    const id = document.getElementById('editId').value;
    const formData = new FormData(event.target);
    const submitBtn = event.target.querySelector('button[type="submit"]');

    // Disable submit button to prevent double submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengupdate...';

    fetch(`{{ url('master-data/beban-operasional') }}/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-HTTP-Method-Override': 'PUT',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Gagal mengupdate data');
            // Re-enable button on error
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Update';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate data');
        // Re-enable button on error
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Update';
    });
}

function deleteItem(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) return;

    fetch(`{{ url('master-data/beban-operasional') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Gagal menghapus data');
        }
    });
}

function filterData() {
    const search = document.getElementById('filterSearch').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    const rows = document.querySelectorAll('#tableBody tr');

    rows.forEach(row => {
        const namaBeban = row.cells[2]?.textContent.toLowerCase() || '';
        const itemStatus = row.cells[4]?.textContent.toLowerCase() || '';
        
        const matchSearch = namaBeban.includes(search);
        const matchStatus = !status || itemStatus.includes(status);

        row.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
