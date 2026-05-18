<?php
// ============================================================================
// LOGIKA SERVER-SIDE
// ============================================================================

// Inisialisasi variabel
$message = '';
$message_type = '';
$form_data = [];

// Cek jika form di-submit
if (isset($_POST['simpan'])) {
    // Sanitasi input
    $pegawai_id = intval($_POST['pegawai_id'] ?? 0);
    $metode_bayar = htmlspecialchars($_POST['metode_bayar'] ?? '');
    $tanggal_penggajian = htmlspecialchars($_POST['tanggal_penggajian'] ?? '');
    $total_produk = intval($_POST['total_produk'] ?? 0);
    $hari_kerja = intval($_POST['hari_kerja'] ?? 26);
    $tunj_jabatan = intval($_POST['tunj_jabatan'] ?? 0);
    $tunj_transport = intval($_POST['tunj_transport'] ?? 0);
    $tunj_konsumsi = intval($_POST['tunj_konsumsi'] ?? 0);
    $bpjs = intval($_POST['bpjs'] ?? 0);
    $gaji_final = intval($_POST['gaji_final'] ?? 0);
    $aktif_bulat = isset($_POST['aktif_bulat']) ? 1 : 0;
    $step_bulat = intval($_POST['step_bulat'] ?? 100000);

    // Validasi input
    $errors = [];
    if ($pegawai_id <= 0) $errors[] = 'Pilih pegawai terlebih dahulu';
    if (empty($metode_bayar)) $errors[] = 'Pilih metode pembayaran';
    if (empty($tanggal_penggajian)) $errors[] = 'Tanggal penggajian harus diisi';
    if ($total_produk <= 0) $errors[] = 'Total produk harus lebih dari 0';
    if ($hari_kerja <= 0) $errors[] = 'Hari kerja harus lebih dari 0';

    if (empty($errors)) {
        // Hitung ulang di server sebagai validasi
        $tarif_produk = 729;
        $gaji_mentah = $total_produk * $tarif_produk;
        
        if ($aktif_bulat) {
            $gaji_final_hitung = ceil($gaji_mentah / $step_bulat) * $step_bulat;
        } else {
            $gaji_final_hitung = $gaji_mentah;
        }
        
        $total_gaji = $gaji_final_hitung + $tunj_jabatan + $tunj_transport + $tunj_konsumsi - $bpjs;

        // Simpan ke database atau proses lebih lanjut
        // Contoh: INSERT INTO penggajian (pegawai_id, metode_bayar, ...) VALUES (...)
        
        $form_data = [
            'pegawai_id' => $pegawai_id,
            'metode_bayar' => $metode_bayar,
            'tanggal_penggajian' => $tanggal_penggajian,
            'total_produk' => $total_produk,
            'hari_kerja' => $hari_kerja,
            'tunj_jabatan' => $tunj_jabatan,
            'tunj_transport' => $tunj_transport,
            'tunj_konsumsi' => $tunj_konsumsi,
            'bpjs' => $bpjs,
            'gaji_final' => $gaji_final_hitung,
            'total_gaji' => $total_gaji,
            'aktif_bulat' => $aktif_bulat,
            'step_bulat' => $step_bulat
        ];

        $message = 'Data penggajian berhasil disimpan!';
        $message_type = 'success';

        // Uncomment untuk redirect setelah berhasil
        // header('Location: index.php?success=1');
        // exit;
    } else {
        $message = 'Error: ' . implode(', ', $errors);
        $message_type = 'error';
    }
}

// Data dummy untuk select pegawai (ganti dengan query database)
$pegawais = [
    ['id' => 1, 'nama' => 'Budi Santoso'],
    ['id' => 2, 'nama' => 'Siti Nurhaliza'],
    ['id' => 3, 'nama' => 'Ahmad Wijaya'],
];

$metode_bayar_list = [
    'transfer' => 'Transfer Bank',
    'tunai' => 'Tunai',
    'cek' => 'Cek',
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
            --color-tertiary: #E0E0E0;
            --color-success: #28A745;
            --color-danger: #DC3545;
            --color-info: #17A2B8;
            --color-text: #333333;
            --color-text-muted: #666666;
            --border-radius-lg: 8px;
            --border-radius-md: 4px;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.25rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--color-secondary);
            color: var(--color-text);
            line-height: 1.5;
        }

        .wrapper {
            background-color: var(--color-secondary);
            padding: var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            max-width: 600px;
            margin: 2rem auto;
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
            background-color: transparent;
            border: 1px solid var(--color-tertiary);
            border-radius: var(--border-radius-md);
            color: var(--color-text);
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background-color: var(--color-tertiary);
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
            border: 1px solid #C3E6CB;
            color: #155724;
        }

        .alert-error {
            background-color: #F8D7DA;
            border: 1px solid #F5C6CB;
            color: #721C24;
        }

        /* Card */
        .card {
            background-color: var(--color-primary);
            border: 0.5px solid var(--color-tertiary);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            margin-bottom: 1.5rem;
            color: white;
        }

        .card-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-title i {
            font-size: 16px;
        }

        /* Grid */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .grid-2-custom {
            display: grid;
            grid-template-columns: 1fr 150px;
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
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            padding: 0 var(--spacing-md);
            color: var(--color-text-muted);
            font-size: 13px;
            font-weight: 500;
        }

        .input-wrapper input,
        .input-wrapper select {
            flex: 1;
            border: none;
            outline: none;
            padding: 0 var(--spacing-md);
            font-size: 13px;
            background-color: transparent;
            color: var(--color-text);
        }

        .input-wrapper input::placeholder {
            color: var(--color-text-muted);
        }

        .input-wrapper input:disabled,
        .input-wrapper input[readonly] {
            background-color: transparent;
            color: var(--color-text);
            cursor: not-allowed;
        }

        .input-suffix {
            padding: 0 var(--spacing-md);
            color: var(--color-text-muted);
            font-size: 13px;
            font-weight: 500;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background-color: var(--color-info);
            color: white;
            font-size: 11px;
            border-radius: 3px;
            font-weight: 600;
        }

        /* Hint Text */
        .hint-text {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 0.25rem;
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
            color: white;
            cursor: pointer;
            font-weight: 500;
        }

        /* Divider */
        .divider {
            height: 1px;
            background-color: rgba(255, 255, 255, 0.2);
            margin: 1rem 0;
        }

        /* Info Box */
        .info-box {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
            border-radius: var(--border-radius-md);
            padding: 0.75rem;
            font-size: 12px;
            color: white;
            margin-top: 0.5rem;
        }

        /* Total Box */
        .total-box {
            background-color: var(--color-secondary);
            border-radius: var(--border-radius-md);
            padding: 1.5rem;
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
            font-weight: 700;
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
            color: white;
            border: none;
            border-radius: var(--border-radius-md);
            font-size: 13px;
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
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <h1>Tambah Penggajian</h1>
            <a href="index.php" class="btn-back">
                <i class="ti ti-arrow-left"></i>
                Kembali
            </a>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="formPenggajian">
            <!-- Card 1: Data Pegawai -->
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-user-check"></i>
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
                                    <option value="<?php echo $p['id']; ?>">
                                        <?php echo htmlspecialchars($p['nama']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="form-group">
                        <label class="form-label">Metode Bayar</label>
                        <div class="input-wrapper">
                            <select name="metode_bayar" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($metode_bayar_list as $key => $val): ?>
                                    <option value="<?php echo $key; ?>">
                                        <?php echo $val; ?>
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
                        <i class="ti ti-calendar input-prefix"></i>
                        <input type="date" name="tanggal_penggajian" required value="<?php echo date('Y-m-d'); ?>">
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

                <div class="grid-2-custom">
                    <!-- Total Produk -->
                    <div class="form-group">
                        <label class="form-label">Total Produk</label>
                        <div class="input-wrapper">
                            <input type="number" name="total_produk" id="total_produk" value="0" min="0" oninput="hitungOtomatis()">
                            <span class="input-suffix">produk</span>
                        </div>
                    </div>

                    <!-- Hari Kerja -->
                    <div class="form-group">
                        <label class="form-label">Hari Kerja</label>
                        <div class="input-wrapper">
                            <input type="number" name="hari_kerja" id="hari_kerja" value="26" min="1" max="31" oninput="hitungOtomatis()">
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

                <!-- Baris 1: Total Produk, Tarif, Gaji Produksi -->
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Total Produk</label>
                        <div class="input-wrapper readonly">
                            <input type="text" id="display_total_produk" readonly value="0">
                            <span class="input-suffix">produk</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tarif / Produk</label>
                        <div class="input-wrapper readonly">
                            <span class="input-prefix">Rp</span>
                            <input type="text" id="display_tarif" readonly value="729">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label">Gaji Produksi (Mentah)</label>
                    <div class="input-wrapper readonly">
                        <span class="input-prefix">Rp</span>
                        <input type="text" id="display_gaji_mentah" readonly value="0">
                    </div>
                </div>

                <!-- Divider -->
                <div class="divider"></div>

                <!-- Pembulatan -->
                <div class="checkbox-wrapper">
                    <input type="checkbox" id="aktif_bulat" name="aktif_bulat" onchange="togglePembulatan()">
                    <label for="aktif_bulat">Aktifkan Pembulatan</label>
                </div>

                <!-- Panel Pembulatan (Hidden by default) -->
                <div id="panel_bulat" class="hidden">
                    <div class="form-group">
                        <label class="form-label">Bulatkan ke Kelipatan</label>
                        <div class="input-wrapper">
                            <select name="step_bulat" id="step_bulat" onchange="hitungOtomatis()">
                                <option value="1000">Rp 1.000</option>
                                <option value="10000">Rp 10.000</option>
                                <option value="100000" selected>Rp 100.000</option>
                                <option value="500000">Rp 500.000</option>
                            </select>
                        </div>
                    </div>

                    <div class="info-box" id="info_selisih" style="display: none;">
                        <strong>Selisih Pembulatan:</strong> <span id="selisih_value">Rp 0</span>
                    </div>
                </div>

                <!-- Gaji Produksi Final -->
                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label">Gaji Produksi Final</label>
                    <div class="input-wrapper readonly">
                        <span class="input-prefix">Rp</span>
                        <input type="text" id="display_gaji_final" readonly value="0" class="text-blue">
                    </div>
                </div>
            </div>

            <!-- Card 4: Tunjangan & Asuransi -->
            <div class="card">
                <div class="card-title">
                    <i class="ti ti-coin"></i>
                    Tunjangan & Asuransi
                </div>

                <div class="grid-2">
                    <!-- Tunjangan Jabatan -->
                    <div class="form-group">
                        <label class="form-label">Tunjangan Jabatan</label>
                        <div class="input-wrapper">
                            <span class="input-prefix">Rp</span>
                            <input type="number" name="tunj_jabatan" id="tunj_jabatan" value="0" min="0" oninput="hitungOtomatis()">
                        </div>
                    </div>

                    <!-- Tunjangan Transport -->
                    <div class="form-group">
                        <label class="form-label">Tunjangan Transport</label>
                        <div class="input-wrapper">
                            <span class="input-prefix">Rp</span>
                            <input type="number" name="tunj_transport" id="tunj_transport" value="150000" min="0" oninput="hitungOtomatis()">
                        </div>
                    </div>

                    <!-- Tunjangan Konsumsi -->
                    <div class="form-group">
                        <label class="form-label">Tunjangan Konsumsi</label>
                        <div class="input-wrapper">
                            <span class="input-prefix">Rp</span>
                            <input type="number" name="tunj_konsumsi" id="tunj_konsumsi" value="375000" min="0" oninput="hitungOtomatis()">
                        </div>
                    </div>

                    <!-- BPJS / Asuransi -->
                    <div class="form-group">
                        <label class="form-label">BPJS / Asuransi</label>
                        <div class="input-wrapper">
                            <span class="input-prefix">Rp</span>
                            <input type="number" name="bpjs" id="bpjs" value="100000" min="0" oninput="hitungOtomatis()">
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
            <input type="hidden" name="gaji_final" id="h-final" value="0">

            <!-- Button Submit -->
            <button type="submit" name="simpan" class="btn-submit">
                <i class="ti ti-check"></i>
                Simpan Penggajian
            </button>
        </form>
    </div>

    <script>
        // Konstanta
        const TARIF_PRODUK = 729;

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

        // Update Tarif (dummy function)
        function updateTarif() {
            // Bisa diisi dengan logika untuk mengambil tarif dari database
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
                document.getElementById('selisih_value').textContent = 'Rp ' + formatRupiah(selisih);
            }

            // Hitung total gaji
            const totalTunjangan = tunjanganJabatan + tunjanganTransport + tunjanganKonsumsi;
            const totalGaji = gajiFinal + totalTunjangan - bpjs;

            // Update display
            document.getElementById('display_total_produk').value = formatRupiah(totalProduk);
            document.getElementById('display_tarif').value = formatRupiah(TARIF_PRODUK);
            document.getElementById('display_gaji_mentah').value = formatRupiah(gajiMentah);
            document.getElementById('display_gaji_final').value = formatRupiah(gajiFinal);
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
            const gajiFinal = parseRupiah(document.getElementById('display_gaji_final').value);
            document.getElementById('h-final').value = gajiFinal;
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            hitungOtomatis();
        });
    </script>
</body>
</html>
