<?php
// Koneksi database (sesuaikan dengan konfigurasi Anda)
// $conn = new mysqli("localhost", "root", "", "eadt_umkm");

// Data dummy untuk testing
$pegawais = [
    ['id' => 1, 'nama' => 'Budi Santoso', 'tarif' => 729, 'tunjangan_jabatan' => 0, 'tunjangan_transport' => 150000, 'tunjangan_konsumsi' => 375000, 'bpjs' => 100000],
    ['id' => 2, 'nama' => 'Siti Nurhaliza', 'tarif' => 729, 'tunjangan_jabatan' => 0, 'tunjangan_transport' => 150000, 'tunjangan_konsumsi' => 375000, 'bpjs' => 100000],
    ['id' => 3, 'nama' => 'Ahmad Wijaya', 'tarif' => 729, 'tunjangan_jabatan' => 0, 'tunjangan_transport' => 150000, 'tunjangan_konsumsi' => 375000, 'bpjs' => 100000],
];

$kasbank = [
    ['kode' => '111', 'nama' => 'Bank BCA'],
    ['kode' => '112', 'nama' => 'Tunai'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Penggajian</title>
    
    <!-- Tabler Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <style>
        :root {
            --color-primary: #185FA5;
            --color-secondary: #F5F5F5;
            --color-tertiary: #D0D0D0;
            --color-text: #333333;
            --color-text-muted: #999999;
            --color-blue-light: #E6F1FB;
            --color-blue-dark: #0C447C;
            --color-green-light: #EAF3DE;
            --color-green-dark: #27500A;
            --border-radius-lg: 12px;
            --border-radius-md: 8px;
            --spacing-md: 1rem;
            --spacing-lg: 1.25rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--color-secondary);
            color: var(--color-text);
            line-height: 1.5;
            height: 100%;
        }

        body {
            padding: 2rem 0;
        }

        .wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: var(--spacing-lg);
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 17px;
            font-weight: 600;
            color: var(--color-text);
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: white;
            border: 0.5px solid var(--color-tertiary);
            border-radius: var(--border-radius-md);
            color: var(--color-text);
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background-color: var(--color-secondary);
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius-md);
            margin-bottom: 1.5rem;
            font-size: 13px;
        }

        .alert-success {
            background-color: #D4EDDA;
            border: 0.5px solid #C3E6CB;
            color: #155724;
        }

        .alert-error {
            background-color: #F8D7DA;
            border: 0.5px solid #F5C6CB;
            color: #721C24;
        }

        /* Card */
        .card {
            background-color: white;
            border: 0.5px solid var(--color-tertiary);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--color-text);
        }

        .card-title i {
            font-size: 16px;
            color: var(--color-text-muted);
        }

        /* Grid */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Form Group */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--color-text);
        }

        /* Input Wrapper */
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            height: 36px;
            background-color: white;
            border: 0.5px solid var(--color-tertiary);
            border-radius: var(--border-radius-md);
            overflow: hidden;
        }

        .input-wrapper.readonly {
            background-color: var(--color-secondary);
        }

        .input-prefix {
            padding: 0 10px;
            color: var(--color-text-muted);
            font-size: 13px;
            font-weight: 500;
            border-right: 0.5px solid var(--color-tertiary);
            flex-shrink: 0;
        }

        .input-wrapper input,
        .input-wrapper select {
            flex: 1;
            border: none;
            outline: none;
            padding: 0 10px;
            font-size: 13px;
            background-color: transparent;
            color: var(--color-text);
            font-family: inherit;
        }

        .input-wrapper input::placeholder {
            color: var(--color-text-muted);
        }

        .input-wrapper input:disabled,
        .input-wrapper input[readonly] {
            background-color: transparent;
            color: var(--color-text);
            cursor: not-allowed;
            font-style: italic;
        }

        .input-suffix {
            padding: 0 10px;
            color: var(--color-text-muted);
            font-size: 13px;
            font-weight: 500;
            border-left: 0.5px solid var(--color-tertiary);
            flex-shrink: 0;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background-color: var(--color-blue-light);
            color: var(--color-blue-dark);
            font-size: 11px;
            border-radius: 3px;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        /* Hint Text */
        .hint-text {
            font-size: 11px;
            color: var(--color-text-muted);
            margin-top: 0.25rem;
        }

        /* Dashed Box */
        .dashed-box {
            border: 1px dashed var(--color-tertiary);
            background-color: var(--color-secondary);
            border-radius: var(--border-radius-md);
            padding: 0.75rem;
            font-size: 12px;
            color: var(--color-text-muted);
            margin-bottom: 1rem;
            text-align: center;
        }

        /* Checkbox */
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--color-primary);
        }

        .checkbox-wrapper label {
            font-size: 13px;
            color: var(--color-text);
            cursor: pointer;
            font-weight: 500;
        }

        /* Divider */
        .divider {
            height: 0.5px;
            background-color: var(--color-tertiary);
            margin: 1rem 0;
        }

        /* Result Row */
        .result-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 0.5px solid var(--color-tertiary);
        }

        .result-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .result-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .result-label {
            font-size: 11px;
            color: var(--color-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .result-value {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text);
        }

        /* Info Box */
        .info-box {
            background-color: var(--color-green-light);
            border: 0.5px solid var(--color-green-dark);
            border-radius: var(--border-radius-md);
            padding: 0.75rem;
            font-size: 12px;
            color: var(--color-green-dark);
            margin-top: 0.75rem;
        }

        /* Total Box */
        .total-box {
            background-color: var(--color-secondary);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .total-box-label {
            font-size: 12px;
            color: var(--color-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .total-box-value {
            font-size: 22px;
            font-weight: 500;
            color: var(--color-primary);
            margin-bottom: 0.5rem;
        }

        .total-box-subtext {
            font-size: 11px;
            color: var(--color-text-muted);
        }

        /* Button */
        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--color-primary);
            color: var(--color-blue-light);
            border: none;
            border-radius: var(--border-radius-md);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Utility */
        .text-blue {
            color: var(--color-primary);
        }

        .hidden {
            display: none;
        }

        /* Final Gaji Row */
        .final-gaji-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 0.5px solid var(--color-tertiary);
        }

        .final-gaji-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--color-primary);
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <h1>Tambah Penggajian</h1>
            <a href="javascript:history.back()" class="btn-back">
                <i class="ti ti-arrow-left"></i>
                Kembali
            </a>
        </div>

        <form method="POST" id="formPenggajian">
            <!-- Card 1: Data Pegawai -->
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-user"></i>
                    Data Pegawai
                </div>

                <div class="grid-2">
                    <!-- Pilih Pegawai -->
                    <div class="form-group">
                        <label class="form-label">Pegawai</label>
                        <div class="input-wrapper">
                            <select name="pegawai_id" id="pegawai_id" required onchange="updateTarif()">
                                <option value="">-- Pilih Pegawai --</option>
                                <?php foreach ($pegawais as $p): ?>
                                    <option value="<?php echo $p['id']; ?>" 
                                        data-tarif="<?php echo $p['tarif']; ?>"
                                        data-tunjangan-jabatan="<?php echo $p['tunjangan_jabatan']; ?>"
                                        data-tunjangan-transport="<?php echo $p['tunjangan_transport']; ?>"
                                        data-tunjangan-konsumsi="<?php echo $p['tunjangan_konsumsi']; ?>"
                                        data-bpjs="<?php echo $p['bpjs']; ?>">
                                        <?php echo $p['nama']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="form-group">
                        <label class="form-label">Metode Pembayaran</label>
                        <div class="input-wrapper">
                            <select name="coa_kasbank" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($kasbank as $kb): ?>
                                    <option value="<?php echo $kb['kode']; ?>">
                                        <?php echo $kb['nama']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tanggal Penggajian -->
                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label">Tanggal Penggajian</label>
                    <div class="input-wrapper" style="width: 220px;">
                        <i class="ti ti-calendar input-prefix" style="border-right: 0.5px solid var(--color-tertiary);"></i>
                        <input type="date" name="tanggal_penggajian" required value="<?php echo date('Y-m-d'); ?>" style="border-left: none;">
                    </div>
                    <div class="hint-text">Tanggal pelaksanaan pembayaran gaji</div>
                </div>
            </div>

            <!-- Card 2: Komponen Produksi -->
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-box"></i>
                    Komponen Produksi
                </div>

                <!-- Dashed Box -->
                <div class="dashed-box">
                    Toggle input harian/bulanan dihapus
                </div>

                <div class="grid-2">
                    <!-- Total Produk -->
                    <div class="form-group">
                        <label class="form-label">Total Produk Bulan Ini</label>
                        <div class="input-wrapper">
                            <input type="number" name="total_produk_bulanan" id="total_produk" value="0" min="0" oninput="hitungOtomatis()">
                            <span class="input-suffix">produk</span>
                        </div>
                    </div>

                    <!-- Hari Kerja -->
                    <div class="form-group">
                        <label class="form-label">Hari Kerja</label>
                        <div class="input-wrapper">
                            <input type="number" name="hari_kerja" id="hari_kerja" value="26" min="1" max="31" oninput="hitungOtomatis()">
                            <span class="input-suffix">hari</span>
                        </div>
                    </div>
                </div>

                <!-- Rata-rata / Hari -->
                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label">
                        Rata-rata / Hari
                        <span class="badge">referensi</span>
                    </label>
                    <div class="input-wrapper readonly">
                        <input type="text" id="rata_rata_hari" readonly value="0">
                        <span class="input-suffix">produk/hari</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Perhitungan Otomatis -->
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-calculator"></i>
                    Perhitungan Otomatis
                </div>

                <!-- Result Rows -->
                <div class="result-row">
                    <div class="result-item">
                        <span class="result-label">Total Produk</span>
                        <span class="result-value" id="display_total_produk">0</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Tarif / Produk</span>
                        <span class="result-value">Rp <span id="display_tarif">0</span></span>
                    </div>
                </div>

                <div class="result-row">
                    <div class="result-item">
                        <span class="result-label">Gaji Produksi</span>
                        <span class="result-value">Rp <span id="display_gaji_mentah">0</span></span>
                    </div>
                </div>

                <!-- Divider -->
                <div class="divider"></div>

                <!-- Pembulatan -->
                <div class="checkbox-wrapper">
                    <input type="checkbox" id="aktif_bulat" name="pembulatan_aktif" onchange="togglePembulatan()">
                    <label for="aktif_bulat">Pembulatan Gaji</label>
                </div>

                <!-- Panel Pembulatan (Hidden by default) -->
                <div id="panel_bulat" class="hidden">
                    <div class="form-group">
                        <label class="form-label">Bulatkan ke Kelipatan</label>
                        <div class="input-wrapper">
                            <select name="pembulatan_step" id="step_bulat" onchange="hitungOtomatis()">
                                <option value="1000">Rp 1.000</option>
                                <option value="10000">Rp 10.000</option>
                                <option value="100000" selected>Rp 100.000</option>
                                <option value="500000">Rp 500.000</option>
                            </select>
                        </div>
                    </div>

                    <div class="info-box" id="info_selisih" style="display: none;">
                        <strong>Selisih Pembulatan:</strong> Rp <span id="selisih_value">0</span>
                    </div>
                </div>

                <!-- Gaji Produksi Final -->
                <div class="final-gaji-row">
                    <div class="result-item">
                        <span class="result-label">Gaji Produksi Final</span>
                        <span class="result-value final-gaji-value">Rp <span id="display_gaji_final">0</span></span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Tunjangan dan Asuransi -->
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-gift"></i>
                    Tunjangan dan Asuransi
                </div>

                <div class="grid-2">
                    <!-- Tunjangan Jabatan -->
                    <div class="form-group">
                        <label class="form-label">Tunjangan Jabatan</label>
                        <div class="input-wrapper">
                            <span class="input-prefix">Rp</span>
                            <input type="number" name="tunjangan_jabatan" id="tunj_jabatan" value="0" min="0" oninput="hitungOtomatis()">
                        </div>
                    </div>

                    <!-- Tunjangan Transport -->
                    <div class="form-group">
                        <label class="form-label">Tunjangan Transport</label>
                        <div class="input-wrapper">
                            <span class="input-prefix">Rp</span>
                            <input type="number" name="tunjangan_transport" id="tunj_transport" value="150000" min="0" oninput="hitungOtomatis()">
                        </div>
                    </div>

                    <!-- Tunjangan Konsumsi -->
                    <div class="form-group">
                        <label class="form-label">Tunjangan Konsumsi</label>
                        <div class="input-wrapper">
                            <span class="input-prefix">Rp</span>
                            <input type="number" name="tunjangan_konsumsi" id="tunj_konsumsi" value="375000" min="0" oninput="hitungOtomatis()">
                        </div>
                    </div>

                    <!-- BPJS / Asuransi -->
                    <div class="form-group">
                        <label class="form-label">Asuransi BPJS</label>
                        <div class="input-wrapper">
                            <span class="input-prefix">Rp</span>
                            <input type="number" name="asuransi" id="bpjs" value="100000" min="0" oninput="hitungOtomatis()">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Gaji -->
            <div class="total-box">
                <div class="total-box-label">Total Gaji Bulan Ini</div>
                <div class="total-box-value" id="display_total_gaji">Rp 0</div>
                <div class="total-box-subtext">Gaji produksi + tunjangan – asuransi</div>
            </div>

            <!-- Hidden input untuk gaji final -->
            <input type="hidden" name="gaji_produksi_final" id="h-final" value="0">

            <!-- Button Submit -->
            <button type="submit" class="btn-submit">
                Simpan Penggajian
            </button>
        </form>
    </div>

    <script>
        // Konstanta
        let TARIF_PRODUK = 729;

        // Format Rupiah
        function formatRupiah(num) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(num);
        }

        // Parse Rupiah
        function parseRupiah(str) {
            return parseInt(str.replace(/\D/g, '')) || 0;
        }

        // Update Tarif dari pegawai yang dipilih
        function updateTarif() {
            const select = document.getElementById('pegawai_id');
            const option = select.options[select.selectedIndex];
            
            TARIF_PRODUK = parseInt(option.dataset.tarif) || 729;
            
            // Update tunjangan default
            document.getElementById('tunj_jabatan').value = parseInt(option.dataset.tunjanganJabatan) || 0;
            document.getElementById('tunj_transport').value = parseInt(option.dataset.tunjanganTransport) || 150000;
            document.getElementById('tunj_konsumsi').value = parseInt(option.dataset.tunjanganKonsumsi) || 375000;
            document.getElementById('bpjs').value = parseInt(option.dataset.bpjs) || 100000;
            
            hitungOtomatis();
        }

        // Toggle Pembulatan
        function togglePembulatan() {
            const checkbox = document.getElementById('aktif_bulat');
            const panel = document.getElementById('panel_bulat');
            const infoBox = document.getElementById('info_selisih');
            
            if (checkbox.checked) {
                panel.classList.remove('hidden');
                infoBox.style.display = 'block';
            } else {
                panel.classList.add('hidden');
                infoBox.style.display = 'none';
            }
            
            hitungOtomatis();
        }

        // Hitung Otomatis
        function hitungOtomatis() {
            // Ambil nilai input
            const totalProduk = parseInt(document.getElementById('total_produk').value) || 0;
            const hariKerja = parseInt(document.getElementById('hari_kerja').value) || 26;
            const tunjanganJabatan = parseInt(document.getElementById('tunj_jabatan').value) || 0;
            const tunjanganTransport = parseInt(document.getElementById('tunj_transport').value) || 0;
            const tunjanganKonsumsi = parseInt(document.getElementById('tunj_konsumsi').value) || 0;
            const bpjs = parseInt(document.getElementById('bpjs').value) || 0;
            const aktifBulat = document.getElementById('aktif_bulat').checked;
            const stepBulat = parseInt(document.getElementById('step_bulat').value) || 100000;

            // Hitung rata-rata per hari
            const rataRataHari = hariKerja > 0 ? Math.round(totalProduk / hariKerja) : 0;
            document.getElementById('rata_rata_hari').value = formatRupiah(rataRataHari);

            // Hitung gaji mentah
            const gajiMentah = totalProduk * TARIF_PRODUK;

            // Hitung gaji final dengan pembulatan
            let gajiFinal = gajiMentah;
            let selisih = 0;

            if (aktifBulat) {
                gajiFinal = Math.ceil(gajiMentah / stepBulat) * stepBulat;
                selisih = gajiFinal - gajiMentah;
                document.getElementById('selisih_value').textContent = formatRupiah(selisih);
            }

            // Hitung total gaji
            const totalTunjangan = tunjanganJabatan + tunjanganTransport + tunjanganKonsumsi;
            const totalGaji = gajiFinal + totalTunjangan - bpjs;

            // Update display
            document.getElementById('display_total_produk').textContent = formatRupiah(totalProduk);
            document.getElementById('display_tarif').textContent = formatRupiah(TARIF_PRODUK);
            document.getElementById('display_gaji_mentah').textContent = formatRupiah(gajiMentah);
            document.getElementById('display_gaji_final').textContent = formatRupiah(gajiFinal);
            document.getElementById('display_total_gaji').textContent = 'Rp ' + formatRupiah(totalGaji);

            // Isi hidden input untuk gaji final
            document.getElementById('h-final').value = gajiFinal;
        }

        // Form Submit
        document.getElementById('formPenggajian').addEventListener('submit', function(e) {
            // Validasi sebelum submit
            const pegawaiId = document.getElementById('pegawai_id').value;
            const totalProduk = parseInt(document.getElementById('total_produk').value) || 0;

            if (!pegawaiId) {
                e.preventDefault();
                alert('Pilih pegawai terlebih dahulu!');
                return;
            }

            if (totalProduk <= 0) {
                e.preventDefault();
                alert('Total produk harus lebih dari 0!');
                return;
            }

            // Update hidden input sebelum submit
            const gajiFinal = parseRupiah(document.getElementById('display_gaji_final').textContent);
            document.getElementById('h-final').value = gajiFinal;
            
            alert('Data penggajian berhasil disimpan!');
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            hitungOtomatis();
        });
    </script>
</body>
</html>
