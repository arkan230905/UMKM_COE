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
                    
                    <!-- COA Jurnal Produksi -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">COA Debit (BDP-BOP)</label>
                            <select name="coa_debit_id" id="coa_debit_id" class="form-select">
                                <option value="">-- Pilih COA Debit --</option>
                                @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '117%')->orderBy('kode_akun')->get() as $coa)
                                    <option value="{{ $coa->kode_akun }}" {{ $coa->kode_akun == '1173' ? 'selected' : '' }}>
                                        {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">COA untuk debit BDP-BOP (default: 1173)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">COA Kredit (Hutang/Persediaan)</label>
                            <select name="coa_kredit_id" id="coa_kredit_id" class="form-select">
                                <option value="">-- Pilih COA Kredit --</option>
                                <optgroup label="Hutang">
                                    @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '21%')->orderBy('kode_akun')->get() as $coa)
                                        <option value="{{ $coa->kode_akun }}" {{ $coa->kode_akun == '210' ? 'selected' : '' }}>
                                            {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Persediaan Bahan Pendukung">
                                    @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '115%')->orderBy('kode_akun')->get() as $coa)
                                        <option value="{{ $coa->kode_akun }}">
                                            {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <small class="text-muted">COA untuk kredit (Hutang Usaha atau Persediaan)</small>
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
                            <div id="komponenContainer">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="komponenTable">
                                        <thead>
                                            <tr>
                                                <th>Komponen</th>
                                                <th>Rp / produk</th>
                                                <th>Keterangan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="komponenRows">
                                            <tr>
                                                <td><input type="text" name="komponen_name[]" class="form-control" placeholder="Nama komponen" required></td>
                                                <td><input type="number" name="komponen_rate[]" class="form-control komponen-rate" min="0" step="0.01" placeholder="0" required></td>
                                                <td><input type="text" name="komponen_desc[]" class="form-control" placeholder="Keterangan"></td>
                                                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">Hapus</button></td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary fw-bold">
                                                <td>Total BOP / produk</td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rp</span>
                                                        <input type="text" id="total_bop_per_jam" name="total_bop_per_jam" class="form-control text-end" readonly>
                                                    </div>
                                                </td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-success mt-2" onclick="addKomponenRow()">
                                    <i class="fas fa-plus"></i> Tambah Komponen
                                </button>
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
                    
                    <!-- COA Jurnal Produksi -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">COA Debit (BDP-BOP)</label>
                            <select name="coa_debit_id" id="editCoaDebitId" class="form-select">
                                <option value="">-- Pilih COA Debit --</option>
                                @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '117%')->orderBy('kode_akun')->get() as $coa)
                                    <option value="{{ $coa->kode_akun }}">
                                        {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">COA untuk debit BDP-BOP (default: 1173)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">COA Kredit (Hutang/Persediaan)</label>
                            <select name="coa_kredit_id" id="editCoaKreditId" class="form-select">
                                <option value="">-- Pilih COA Kredit --</option>
                                <optgroup label="Hutang">
                                    @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '21%')->orderBy('kode_akun')->get() as $coa)
                                        <option value="{{ $coa->kode_akun }}">
                                            {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Persediaan Bahan Pendukung">
                                    @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '115%')->orderBy('kode_akun')->get() as $coa)
                                        <option value="{{ $coa->kode_akun }}">
                                            {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <small class="text-muted">COA untuk kredit (Hutang Usaha atau Persediaan)</small>
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
                                                <th>Komponen</th>
                                                <th>Rp / produk</th>
                                                <th>Keterangan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="editKomponenRows">
                                            <!-- Components will be loaded here -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary fw-bold">
                                                <td>Total BOP / produk</td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rp</span>
                                                        <input type="text" id="editTotalBopPerProduk" name="total_bop_per_produk" class="form-control text-end" readonly>
                                                    </div>
                                                </td>
                                                <td colspan="2"></td>
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
