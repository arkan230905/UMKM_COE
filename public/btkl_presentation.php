<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah BTKL - Presentation Mode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container-fluid {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            margin: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .alert-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
        }
        .presentation-badge {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="header-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="bi bi-user-clock me-2"></i>Tambah Proses Produksi (BTKL)
                        <span class="presentation-badge ms-2">PRESENTATION MODE</span>
                    </h2>
                    <p class="mb-0 mt-2 opacity-75">Sistem Manajemen BTKL - Demo untuk Presentasi</p>
                </div>
                <a href="javascript:history.back()" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="btklForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="kode_proses" class="form-label">Kode Proses <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="kode_proses" 
                                   id="kode_proses" 
                                   class="form-control" 
                                   value="PROC-001"
                                   readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="nama_btkl" class="form-label">Nama Proses <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="nama_btkl" 
                                   id="nama_btkl" 
                                   class="form-control" 
                                   placeholder="Contoh: Penggorengan Adonan"
                                   value="Pengolahan Produk">
                        </div>

                        <div class="col-md-6">
                            <label for="jabatan_id" class="form-label">Jabatan BTKL <span class="text-danger">*</span></label>
                            <select name="jabatan_id" 
                                    id="jabatan_id" 
                                    class="form-select">
                                <option value="">-- Pilih Jabatan --</option>
                                <option value="1" selected>Operator Produksi</option>
                                <option value="2">Teknisi Produksi</option>
                                <option value="3">Supervisor Produksi</option>
                                <option value="4">Helper Produksi</option>
                                <option value="5">Quality Control</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tarif BTKL per Jam <span class="text-info">(Otomatis)</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp/jam</span>
                                <input type="text" 
                                       id="tarif_per_jam_display" 
                                       class="form-control" 
                                       value="150,000"
                                       readonly>
                            </div>
                            <div class="alert alert-info py-2 mt-2">
                                <small>Rp 50,000 x 3 pegawai = Rp 150,000</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select name="satuan" 
                                    id="satuan" 
                                    class="form-select">
                                <option value="Jam" selected>Jam</option>
                                <option value="Unit">Unit</option>
                                <option value="Batch">Batch</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="kapasitas_per_jam" class="form-label">Kapasitas per Jam <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="kapasitas_per_jam" 
                                   id="kapasitas_per_jam" 
                                   class="form-control" 
                                   value="100"
                                   min="0">
                            <small class="form-text text-muted">Berapa pcs bisa diproduksi per jam</small>
                        </div>

                        <div class="col-md-12">
                            <label for="deskripsi_proses" class="form-label">Deskripsi Proses</label>
                            <textarea name="deskripsi_proses" 
                                      id="deskripsi_proses" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Deskripsi detail proses produksi">Proses produksi untuk pengolahan produk makanan dengan standar kualitas tinggi.</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Biaya Per Produk <span class="text-info">(Otomatis)</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp/pcs</span>
                                <input type="text" 
                                       id="biaya_per_produk_display" 
                                       class="form-control" 
                                       value="1,500"
                                       readonly>
                            </div>
                            <div class="alert alert-warning py-2 mt-2">
                                <small>Rp 150,000 / 100 pcs = Rp 1,500</small>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" onclick="showSuccess()">
                                    <i class="bi bi-save me-1"></i> Simpan Data
                                </button>
                                <a href="javascript:history.back()" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Batal
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="alert alert-success mt-3">
            <h5><i class="bi bi-info-circle me-2"></i>Mode Presentasi Aktif</h5>
            <p class="mb-0">Halaman ini dalam mode presentasi. Data yang ditampilkan adalah contoh untuk demonstrasi. Sistem akan menampilkan:</p>
            <ul class="mb-0">
                <li>Dropdown Jabatan BTKL dengan posisi yang relevan</li>
                <li>Perhitungan otomatis tarif BTKL berdasarkan jumlah pegawai</li>
                <li>Kalkulasi biaya per produk</li>
                <li>Interface yang responsif dan modern</li>
            </ul>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateCalculations() {
            const jabatanSelect = document.getElementById('jabatan_id');
            const kapasitasInput = document.getElementById('kapasitas_per_jam');
            const tarifDisplay = document.getElementById('tarif_per_jam_display');
            const biayaPerProdukDisplay = document.getElementById('biaya_per_produk_display');
            
            // Demo data for different positions
            const jabatanData = {
                '1': { tarif: 50000, pegawai: 3 },
                '2': { tarif: 60000, pegawai: 2 },
                '3': { tarif: 80000, pegawai: 1 },
                '4': { tarif: 35000, pegawai: 4 },
                '5': { tarif: 70000, pegawai: 2 }
            };
            
            const selectedJabatan = jabatanData[jabatanSelect.value] || { tarif: 50000, pegawai: 3 };
            const tarifBtkl = selectedJabatan.tarif * selectedJabatan.pegawai;
            const kapasitas = parseInt(kapasitasInput.value) || 100;
            const biayaPerProduk = tarifBtkl / kapasitas;
            
            tarifDisplay.value = tarifBtkl.toLocaleString('id-ID');
            biayaPerProdukDisplay.value = biayaPerProduk.toLocaleString('id-ID');
        }
        
        function showSuccess() {
            alert('✅ Data BTKL berhasil disimpan!\n\nKode Proses: PROC-001\nNama: Pengolahan Produk\nTarif: Rp 150,000/jam\nKapasitas: 100 pcs/jam\n\n(NOTE: Ini adalah demo presentasi)');
        }
        
        document.getElementById('jabatan_id').addEventListener('change', updateCalculations);
        document.getElementById('kapasitas_per_jam').addEventListener('input', updateCalculations);
        
        // Initialize calculations
        updateCalculations();
    </script>
</body>
</html>
