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
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Proses Produksi</label>
                            <select name="proses_produksi_id" id="proses_produksi_id" class="form-select" required>
                                <option value="">Pilih Proses</option>
                                @foreach($prosesProduksis as $proses)
                                    <option value="{{ $proses->id }}" 
                                            data-kapasitas="{{ $proses->kapasitas_per_jam ?? 0 }}"
                                            data-tarif="{{ $proses->tarif_btkl ?? 0 }}"
                                            data-nama="{{ $proses->nama_proses }}"
                                            data-jabatan="{{ $proses->jabatan->nama_jabatan ?? 'Tidak diketahui' }}">
                                        {{ $proses->nama_proses }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div id="btklInfo" class="alert alert-info d-none">
                                <small>
                                    <i class="bi bi-info-circle me-1"></i>
                                    <span id="btklInfoText"></span>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Process Info -->
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label">Kapasitas (pcs/jam)</label>
                            <input type="number" id="kapasitas" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">BTKL / Jam</label>
                            <input type="number" id="btkl_per_jam" name="btkl_per_jam" class="form-control" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">BTKL / pcs</label>
                            <input type="number" id="btkl_per_pcs" class="form-control" readonly>
                        </div>
                    </div>
                    
                    <!-- BOP Components -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="mb-3">Komponen BOP</h6>
                            <div id="komponenContainer">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="komponenTable">
                                        <thead>
                                            <tr>
                                                <th>Komponen</th>
                                                <th>Rp / Jam</th>
                                                <th>Keterangan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="komponenRows">
                                            <tr>
                                                <td><input type="text" name="komponen_name[]" class="form-control" placeholder="Nama komponen"></td>
                                                <td><input type="number" name="komponen_rate[]" class="form-control komponen-rate" min="0" step="0.01" placeholder="0"></td>
                                                <td><input type="text" name="komponen_desc[]" class="form-control" placeholder="Keterangan"></td>
                                                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">Hapus</button></td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary fw-bold">
                                                <td>Total BOP /jam</td>
                                                <td><input type="number" id="total_bop_per_jam" name="total_bop_per_jam" class="form-control" min="0" step="0.01" readonly></td>
                                                <td></td>
                                                <td></td>
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
                    
                    <!-- Calculated Values -->
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label class="form-label">BOP / pcs</label>
                            <input type="number" id="bop_per_pcs" class="form-control" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Biaya / produk</label>
                            <input type="number" id="biaya_per_produk" class="form-control" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Biaya / jam</label>
                            <input type="number" id="biaya_per_jam" class="form-control" readonly>
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
                    <button type="submit" class="btn btn-primary">Simpan</button>
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
            <form id="editBopProsesForm">
                @csrf
                <input type="hidden" name="id" id="editBopProsesId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Proses Produksi</label>
                            <select name="proses_produksi_id" id="editProsesProduksiId" class="form-select" readonly>
                                <option value="">Pilih Proses</option>
                                @foreach($prosesProduksis as $proses)
                                    <option value="{{ $proses->id }}" 
                                            data-kapasitas="{{ $proses->kapasitas_per_jam ?? 0 }}"
                                            data-tarif="{{ $proses->tarif_btkl ?? 0 }}"
                                            data-nama="{{ $proses->nama_proses }}"
                                            data-jabatan="{{ $proses->jabatan->nama_jabatan ?? 'Tidak diketahui' }}">
                                        {{ $proses->nama_proses }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div id="editBtklInfo" class="alert alert-info d-none">
                                <small>
                                    <i class="bi bi-info-circle me-1"></i>
                                    <span id="editBtklInfoText"></span>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label">Kapasitas (pcs/jam)</label>
                            <input type="number" id="editKapasitas" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">BTKL / Jam</label>
                            <input type="number" id="editBtklPerJam" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">BTKL / pcs</label>
                            <input type="number" id="editBtklPerPcs" class="form-control" readonly>
                        </div>
                    </div>
                    
                    <!-- BOP Components -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="mb-3">Komponen BOP</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Komponen</th>
                                            <th>Rp / Jam</th>
                                            <th>Keterangan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="editKomponenRows">
                                        <!-- Components will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-success mt-2" onclick="addEditKomponenRow()">
                                <i class="fas fa-plus"></i> Tambah Komponen
                            </button>
                        </div>
                    </div>
                </div>
                    
                    <!-- Edit Calculated Values -->
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label class="form-label">Total BOP / jam</label>
                            <input type="number" id="editTotalBopPerJam" class="form-control" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">BOP / pcs</label>
                            <input type="number" id="editBopPerPcs" class="form-control" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Biaya / produk</label>
                            <input type="number" id="editBiayaPerProduk" class="form-control" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Biaya / jam</label>
                            <input type="number" id="editBiayaPerJam" class="form-control" readonly>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea id="editKeteranganProses" name="keterangan" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveEditedBop()">Update Data</button>
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
