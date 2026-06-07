<!-- Add BOP Proses Modal -->
<div class="modal fade" id="addBopProsesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Setup BOP Proses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bopProsesForm" action="{{ route('master-data.bop.store-proses-simple') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Nama BOP Proses -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Nama BOP Proses <span class="text-danger">*</span></label>
                            <input type="text" name="nama_bop_proses" id="nama_bop_proses" class="form-control" placeholder="Contoh: Pertumbuhan, Panen, Sortir, dll" required>
                            <small class="text-muted">Masukkan nama proses BOP yang ingin Anda buat</small>
                        </div>
                    </div>
                    
                    <!-- BOP Components -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="mb-3">Komponen BOP</h6>
                            <div class="alert alert-info">
                                <small><i class="fas fa-info-circle me-1"></i>
                                Masukkan nilai BOP <strong>per produk</strong> untuk setiap komponen. Sistem akan menjumlahkan semua komponen untuk mendapatkan Total BOP per produk.
                                </small>
                            </div>

                            <!-- BAGIAN 1: BOP PROSES BAHAN PENDUKUNG -->
                            <div class="card mb-3" style="border-left: 4px solid #198754;">
                                <div class="card-header bg-success bg-opacity-10">
                                    <h6 class="mb-0 text-success">
                                        <i class="fas fa-box me-2"></i>BOP Proses - Bahan Pendukung
                                    </h6>
                                    <small class="text-muted">Pilih bahan pendukung dari database</small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm" id="komponenBahanTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 25%;">Bahan Pendukung</th>
                                                    <th style="width: 15%;">Rp / produk</th>
                                                    <th style="width: 20%;">COA Debit</th>
                                                    <th style="width: 20%;">COA Kredit</th>
                                                    <th style="width: 12%;">Keterangan</th>
                                                    <th style="width: 8%;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="komponenBahanRows">
                                                <!-- Bahan Pendukung rows will be added here -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-success mt-2" onclick="addBahanRow()">
                                        <i class="fas fa-plus"></i> Tambah Bahan Pendukung
                                    </button>
                                </div>
                            </div>

                            <!-- BAGIAN 2: BOP PROSES LAINNYA -->
                            <div class="card" style="border-left: 4px solid #0d6efd;">
                                <div class="card-header bg-primary bg-opacity-10">
                                    <h6 class="mb-0 text-primary">
                                        <i class="fas fa-tools me-2"></i>BOP Proses - Lainnya
                                    </h6>
                                    <small class="text-muted">Komponen BOP lainnya (Listrik, Gas, Penyusutan, dll)</small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm" id="komponenLainTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 25%;">Komponen</th>
                                                    <th style="width: 15%;">Rp / produk</th>
                                                    <th style="width: 20%;">COA Debit</th>
                                                    <th style="width: 20%;">COA Kredit</th>
                                                    <th style="width: 12%;">Keterangan</th>
                                                    <th style="width: 8%;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="komponenLainRows">
                                                <tr>
                                                    <td><input type="text" name="komponen_name[]" class="form-control form-control-sm" placeholder="Nama komponen" required></td>
                                                    <td><input type="number" name="komponen_rate[]" class="form-control form-control-sm komponen-rate" min="0" step="0.01" placeholder="0" required></td>
                                                    <td>
                                                        <select name="komponen_coa_debit[]" class="form-select form-select-sm" required>
                                                            <option value="">-- Pilih --</option>
                                                            @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '117%')->orderBy('kode_akun')->get() as $coa)
                                                                <option value="{{ $coa->kode_akun }}" {{ $coa->kode_akun == '1173' ? 'selected' : '' }}>
                                                                    {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="komponen_coa_kredit[]" class="form-select form-select-sm" required>
                                                            <option value="">-- Pilih --</option>
                                                            <optgroup label="BOP">
                                                                @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '53%')->orderBy('kode_akun')->get() as $coa)
                                                                    <option value="{{ $coa->kode_akun }}">
                                                                        {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                            <optgroup label="Beban Sewa">
                                                                @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '54%')->orderBy('kode_akun')->get() as $coa)
                                                                    <option value="{{ $coa->kode_akun }}">
                                                                        {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                            <optgroup label="BOP Lain">
                                                                @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '55%')->orderBy('kode_akun')->get() as $coa)
                                                                    <option value="{{ $coa->kode_akun }}">
                                                                        {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                            <optgroup label="Harga Pokok Penjualan">
                                                                @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '56%')->orderBy('kode_akun')->get() as $coa)
                                                                    <option value="{{ $coa->kode_akun }}">
                                                                        {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        </select>
                                                    </td>
                                                    <td><input type="text" name="komponen_desc[]" class="form-control form-control-sm" placeholder="Keterangan"></td>
                                                    <td><button type="button" class="btn btn-sm btn-danger" onclick="removeLainRow(this)">Hapus</button></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addLainRow()">
                                        <i class="fas fa-plus"></i> Tambah Komponen Lain
                                    </button>
                                </div>
                            </div>

                            <!-- Total BOP -->
                            <div class="card mt-3 border-warning">
                                <div class="card-body bg-warning bg-opacity-10">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <strong class="fs-5">Total BOP / produk</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-text fw-bold">Rp</span>
                                                <input type="text" id="total_bop_per_jam" name="total_bop_per_jam" class="form-control form-control-lg text-end fw-bold" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="btn-text">Simpan</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit BOP Proses Modal -->
<div class="modal fade" id="editBopProsesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit BOP Proses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBopProsesForm" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="editBopProsesId">
                <div class="modal-body">
                    <!-- Nama BOP Proses -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Nama BOP Proses <span class="text-danger">*</span></label>
                            <input type="text" name="nama_bop_proses" id="editNamaBopProses" class="form-control" placeholder="Contoh: Pertumbuhan, Panen, Sortir, dll" required>
                            <small class="text-muted">Masukkan nama proses BOP yang ingin Anda edit</small>
                        </div>
                    </div>
                    
                    <!-- BOP Components -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="mb-3">Komponen BOP</h6>
                            <div class="alert alert-info">
                                <small><i class="fas fa-info-circle me-1"></i>
                                Masukkan nilai BOP <strong>per produk</strong> untuk setiap komponen. Sistem akan menjumlahkan semua komponen untuk mendapatkan Total BOP per produk.
                                </small>
                            </div>
                            <div id="editKomponenContainer">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="editKomponenTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 20%;">Komponen</th>
                                                <th style="width: 15%;">Rp / produk</th>
                                                <th style="width: 20%;">COA Debit</th>
                                                <th style="width: 20%;">COA Kredit</th>
                                                <th style="width: 15%;">Keterangan</th>
                                                <th style="width: 10%;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="editKomponenRows">
                                            <!-- Components will be loaded here -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary fw-bold">
                                                <td>Total BOP / produk</td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">Rp</span>
                                                        <input type="text" id="editTotalBopPerProduk" name="total_bop_per_produk" class="form-control text-end" readonly>
                                                    </div>
                                                </td>
                                                <td colspan="4"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-success mt-2" onclick="addEditKomponenRow()">
                                    <i class="fas fa-plus"></i> Tambah Komponen
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea id="editKeterangan" name="keterangan" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="editSubmitBtn">
                        <span class="btn-text">Simpan</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Detail BOP Modal -->
<div class="modal fade" id="detailBopModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-semibold">Detail BOP Proses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="detailBopContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>
