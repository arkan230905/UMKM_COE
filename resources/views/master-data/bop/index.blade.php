@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Master Data BOP (Biaya Overhead Pabrik)</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Tabel Data BOP --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th class="text-end" style="width: 15%;">Budget</th>
                            <th class="text-end" style="width: 15%;">Aktual</th>
                            <th class="text-end" style="width: 15%;">Sisa Budget</th>
                            <th class="text-center" style="width: 15%;">Status</th>
                            <th class="text-center" style="width: 20%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($akunBeban as $akun)
                            @php
                                $bop = $bops[$akun->kode_akun] ?? null;
                                $hasBudget = $bop && $bop->hasBudget();
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $akun->kode_akun }}</td>
                                <td>{{ $akun->nama_akun }}</td>
                                <td class="text-end">
                                    @if($hasBudget)
                                        <span class="text-nowrap">Rp {{ number_format($bop->budget, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($hasBudget)
                                        <span class="text-nowrap">Rp {{ number_format($bop->aktual ?? 0, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($hasBudget)
                                        @php
                                            $sisa = $bop->budget - ($bop->aktual ?? 0);
                                            $textClass = $sisa < 0 ? 'text-danger' : 'text-success';
                                        @endphp
                                        <span class="text-nowrap {{ $textClass }}">
                                            Rp {{ number_format($sisa, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($hasBudget)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Belum Ada Budget</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($hasBudget)
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                                data-bs-target="#editBopModal" 
                                                data-id="{{ $bop->id }}"
                                                data-budget="{{ $bop->budget }}"
                                                data-keterangan="{{ $bop->keterangan }}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form action="{{ route('master-data.bop.destroy', $bop->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Yakin ingin menghapus budget ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#addBopModal">
                                            <i class="fas fa-plus me-1"></i> Tambah Budget
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data akun beban</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Budget -->
<div class="modal fade" id="addBopModal" tabindex="-1" aria-labelledby="addBopModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addBopForm" action="{{ route('master-data.bop.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addBopModalLabel">Tambah Budget BOP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    <div class="mb-3">
                        <label for="akun_beban" class="form-label">Pilih Akun Beban <span class="text-danger">*</span></label>
                        <select class="form-select" id="akun_beban" name="kode_akun" required>
                            <option value="" selected disabled>-- Pilih Akun Beban --</option>
                            @foreach($akunBeban as $akun)
                                <option value="{{ $akun->kode_akun }}" data-nama="{{ $akun->nama_akun }}">
                                    {{ $akun->kode_akun }} - {{ $akun->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="budget" class="form-label">Nominal Budget <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="budget" name="budget" required
                                   onkeyup="formatAngka(this)" 
                                   onblur="formatAngka(this, 'blur')"
                                   onfocus="formatAngka(this, 'focus')">
                        </div>
                        <input type="hidden" name="budget_value" id="budget_value">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Budget -->
<div class="modal fade" id="editBopModal" tabindex="-1" aria-labelledby="editBopModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editBopForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editBopModalLabel">Edit Budget BOP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    <div class="mb-3">
                        <label for="edit_nama_akun" class="form-label text-dark">Nama Akun</label>
                        <input type="text" class="form-control text-dark" id="edit_nama_akun" readonly style="background-color: #f8f9fa;">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_budget" class="form-label text-dark">Nominal Budget <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text text-dark">Rp</span>
                            <input type="number" class="form-control text-dark" id="edit_budget" name="budget" required min="0" step="0.01">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_keterangan" class="form-label text-dark">Keterangan</label>
                        <textarea class="form-control text-dark" id="edit_keterangan" name="keterangan" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Fungsi untuk memformat angka dengan titik
    function formatAngka(input, eventType = '') {
        // Jika sedang fokus, tampilkan angka biasa
        if (eventType === 'focus') {
            input.value = input.value.replace(/\./g, '');
            return;
        }
        
        // Ambil nilai input
        let value = input.value.replace(/\D/g, '');
        
        // Format dengan titik ribuan
        if (value.length > 0) {
            value = parseInt(value).toLocaleString('id-ID');
        } else {
            value = '';
        }
        
        // Update nilai input
        input.value = value;
    }
    
    // Format angka saat form disubmit
    const addBopForm = document.getElementById('addBopForm');
    if (addBopForm) {
        addBopForm.addEventListener('submit', function(e) {
            const budgetInput = document.getElementById('budget');
            if (budgetInput) {
                budgetInput.value = budgetInput.value.replace(/\./g, '');
            }
            
            // Pastikan kode_akun terisi
            const kodeAkunInput = document.getElementById('kode_akun');
            if (!kodeAkunInput || !kodeAkunInput.value) {
                e.preventDefault();
                alert('Kode akun tidak valid. Silakan coba lagi.');
                return false;
            }
            
            return true;
        });
    }
    // Format number with thousand separators
    function formatNumber(input) {
        // Get the raw input value
        let value = input.value.replace(/\D/g, '');
        
        // Store the raw value in a hidden field
        document.getElementById('budget_value').value = value;
        
        // Format with thousand separators
        if (value.length > 0) {
            value = parseInt(value).toLocaleString('id-ID');
        }
        
        // Update the display value
        input.value = value;
    }
    }
    
    // Handle form submission
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('addBopForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Get the raw value from the hidden field
                const budgetInput = document.getElementById('budget');
                const budgetValue = document.getElementById('budget_value');
                
                // Update the form value with the raw number before submission
                budgetInput.value = budgetValue.value;
            });
        }
    // Inisialisasi format angka
    document.addEventListener('DOMContentLoaded', function() {
        // Format angka untuk input budget
        document.querySelectorAll('input[type="text"][name="budget"]').forEach(function(input) {
            input.addEventListener('keyup', function(e) {
                formatAngka(this);
            });
        });
        
        // Format angka saat form disubmit
        const form = document.getElementById('addBopForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const budgetInput = document.getElementById('budget');
                if (budgetInput) {
                    // Bersihkan format angka sebelum submit
                    budgetInput.value = budgetInput.value.replace(/\./g, '');
                }
            });
        }

        // Inisialisasi modal edit budget
        var editBopModal = document.getElementById('editBopModal');
        if (editBopModal) {
            editBopModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var budget = button.getAttribute('data-budget');
                var keterangan = button.getAttribute('data-keterangan');
                var namaAkun = button.closest('tr').querySelector('td:nth-child(3)').textContent;
                
                var modal = this;
                modal.querySelector('#editBopForm').action = '/master-data/bop/' + id;
                modal.querySelector('#edit_nama_akun').value = namaAkun.trim();
                modal.querySelector('#edit_budget').value = budget;
                modal.querySelector('#edit_keterangan').value = keterangan || '';
            });
        }

        // Format input number dengan pemisah ribuan
        document.querySelectorAll('input[type="number"]').forEach(function(input) {
            input.addEventListener('change', function() {
                this.value = parseFloat(this.value).toFixed(2);
            });
        });
    });
</script>
@endpush
