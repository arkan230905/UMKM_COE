@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="mb-3">Tambah Penjualan</h3>

    @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('transaksi.penjualan.store') }}" method="POST" id="form-penjualan">
        @csrf

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Metode Pembayaran</label>
                <select name="payment_method" id="payment_method_jual" class="form-select" required>
                    <option value="">-- Pilih Metode --</option>
                    <option value="cash">Tunai</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="credit">Kredit</option>
                </select>
            </div>
            <div class="col-md-3" id="sumber_dana_wrapper_jual">
                <label class="form-label">Terima di</label>
                <select name="sumber_dana" id="sumber_dana_jual" class="form-select">
                    @foreach($kasbank as $kb)
                        <option value="{{ $kb->kode_akun }}">
                            {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Barcode Scanner Input -->
        <div class="card mb-3 border-primary">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-barcode fa-2x text-primary"></i>
                    </div>
                    <div class="col">
                        <label class="form-label mb-1 small text-muted">Scan Barcode Produk</label>
                        <div class="input-group">
                            <input type="text" id="barcode-scanner" class="form-control form-control-lg" 
                                   placeholder="Siap untuk scan barcode..." 
                                   autocomplete="off" autofocus>
                            <div class="input-group-text bg-success text-white">
                                <i class="fas fa-wifi me-1"></i>
                                <span id="scan-indicator">Siap Scan</span>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetScannerState()" title="Reset Scanner">
                                <i class="fas fa-refresh"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Sistem otomatis mendeteksi barcode - tidak perlu klik atau tekan tombol
                        </small>
                    </div>
                    
                    <!-- Preview Produk -->
                    <div class="col-12" id="barcode-preview" style="display: none;">
                        <div class="card border-info">
                            <div class="card-body py-2">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="fas fa-box fa-2x text-info"></i>
                                    </div>
                                    <div class="col">
                                        <div class="fw-bold" id="preview-nama">-</div>
                                        <div class="small text-muted">
                                            <span id="preview-barcode">-</span> | 
                                            Harga: <span id="preview-harga">-</span> | 
                                            Stok: <span id="preview-stok">-</span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <small class="text-info">
                                            <i class="fas fa-keyboard me-1"></i>Tekan Enter untuk tambahkan
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h5>Detail Penjualan</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="detailTableJual">
                <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Harga/Satuan</th>
                        <th class="text-end">Diskon (%)</th>
                        <th class="text-end">Subtotal</th>
                        <th style="width:6%">NO<button class="btn btn-success btn-sm" type="button" id="addRowJual">+</button></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Tabel akan diisi oleh barcode scanner atau tombol tambah -->
                </tbody>
            </table>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-3 ms-auto">
                <label class="form-label">Subtotal Produk</label>
                <input type="text" name="subtotal_produk" class="form-control" value="0" readonly>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Biaya Ongkir</label>
                <input type="number" step="0.01" min="0" name="biaya_ongkir" class="form-control" value="0" id="biaya_ongkir" 
                     onclick="this.focus()" 
                     onfocus="this.select()">
            </div>
            <div class="col-md-3">
                <label class="form-label">Biaya Service</label>
                <input type="number" step="0.01" min="0" name="biaya_service" class="form-control" value="0" id="biaya_service" 
                         onclick="this.focus()" 
                         onfocus="this.select()">
            </div>
            <div class="col-md-3">
                <label class="form-label">PPN (%)</label>
                <input type="number" step="0.01" min="0" name="ppn_persen" class="form-control" value="0" id="ppn_persen" 
                         onclick="this.focus()" 
                         onfocus="this.select()">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total PPN</label>
                <input type="text" name="total_ppn" class="form-control" value="0" readonly id="total_ppn">
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4 ms-auto">
                <label class="form-label">Total Final</label>
                <input type="text" name="total" class="form-control" value="0" readonly id="total_final">
            </div>
        </div>

        <div class="text-end mt-4">
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">Batal</a>
            <button class="btn btn-success">Simpan</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('payment_method_jual');
    const sumberDanaSelect = document.getElementById('sumber_dana_jual');
    
    // Fungsi untuk update sumber dana berdasarkan metode pembayaran
    function updateSumberDana() {
        const paymentMethod = paymentMethodSelect.value;
        let targetKode = '';
        
        // Tentukan kode akun target berdasarkan metode pembayaran
        switch(paymentMethod) {
            case 'cash':
                // Pilih akun Kas (112 atau 113) - prioritaskan 112
                targetKode = '112';
                break;
            case 'transfer':
                // Pilih akun Bank (111)
                targetKode = '111';
                break;
            case 'credit':
                // Pilih akun Piutang (118)
                targetKode = '118';
                break;
        }
        
        // Cari dan pilih option yang sesuai
        if (targetKode) {
            const targetOption = Array.from(sumberDanaSelect.options).find(option => 
                option.value === targetKode
            );
            
            if (targetOption) {
                sumberDanaSelect.value = targetKode;
            } else {
                // Jika kode target tidak ditemukan, coba fallback
                if (paymentMethod === 'cash') {
                    // Coba cari akun kas lainnya (113)
                    const fallbackOption = Array.from(sumberDanaSelect.options).find(option => 
                        option.value === '113'
                    );
                    if (fallbackOption) {
                        sumberDanaSelect.value = '113';
                    }
                }
            }
        }
    }
    
    // Event listener untuk perubahan metode pembayaran
    paymentMethodSelect.addEventListener('change', updateSumberDana);
    
    // Initialize saat pertama kali load hanya jika ada metode yang dipilih
    if (paymentMethodSelect.value) {
        updateSumberDana();
    }

    // ===== BARCODE SCANNER IMPLEMENTATION =====
    
    const barcodeInput = document.getElementById('barcode-scanner');
    const scanIndicator = document.getElementById('scan-indicator');
    let barcodeBuffer = '';
    let barcodeTimeout = null;
    let isScanning = false;
    
    // Fungsi untuk memainkan suara beep
    function playBeep() {
        // Buat suara beep menggunakan Web Audio API
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 1000; // 1000 Hz
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.1);
    }
    
    // Fungsi untuk update indikator scan
    function updateScanIndicator(text, className = 'bg-success') {
        scanIndicator.textContent = text;
        scanIndicator.parentElement.className = `input-group-text ${className} text-white`;
    }
    
    // Fungsi untuk reset scanner state
    window.resetScannerState = function() {
        barcodeBuffer = '';
        isScanning = false;
        barcodeInput.value = '';
        hidePreview();
        updateScanIndicator('Siap Scan', 'bg-success');
        barcodeInput.focus();
    };
    
    // Fungsi untuk preview produk
    function previewProduct(barcode) {
        console.log('Preview product called with barcode:', barcode);
        
        if (barcode.length < 3) {
            console.log('Barcode too short, hiding preview');
            hidePreview();
            return;
        }
        
        console.log('Searching for product...');
        findProductByBarcode(barcode)
            .then(product => {
                console.log('Product found for preview:', product);
                showPreview(product);
            })
            .catch(error => {
                console.log('Product not found for preview:', error);
                hidePreview();
            });
    }
    
    // Fungsi untuk menampilkan preview produk
    function showPreview(product) {
        const previewDiv = document.getElementById('barcode-preview');
        const namaEl = document.getElementById('preview-nama');
        const barcodeEl = document.getElementById('preview-barcode');
        const hargaEl = document.getElementById('preview-harga');
        const stokEl = document.getElementById('preview-stok');
        
        // Simpan produk yang sedang di-preview untuk Enter key
        lastPreviewProduct = product;
        
        namaEl.textContent = product.nama_produk || product.nama;
        barcodeEl.textContent = product.barcode || '-';
        hargaEl.textContent = 'Rp ' + number_format(product.harga_jual || 0);
        stokEl.textContent = product.stok_tersedia || 0;
        
        previewDiv.style.display = 'block';
    }
    
    // Fungsi untuk menyembunyikan preview
    function hidePreview() {
        const previewDiv = document.getElementById('barcode-preview');
        previewDiv.style.display = 'none';
    }
    
    // Helper function untuk format number
    function number_format(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
    
        
    // Fungsi untuk mencari produk berdasarkan barcode
    function findProductByBarcode(barcode) {
        console.log('findProductByBarcode called with:', barcode);
        const url = `/transaksi/penjualan/barcode/${barcode}`;
        console.log('Fetching URL:', url);
        
        return fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    return data.data;
                } else {
                    throw new Error(data.message || 'Produk tidak ditemukan');
                }
            })
            .catch(error => {
                console.log('Fetch error:', error);
                throw error;
            });
    }
    
    // Fungsi untuk menambahkan produk ke tabel
    function addProductToTable(product) {
        console.log('addProductToTable called with product:', product);
        const table = document.getElementById('detailTableJual');
        const tbody = table.querySelector('tbody');
        
        console.log('Current tbody rows before adding:', tbody.querySelectorAll('tr').length);
        
        // Cek apakah produk sudah ada di tabel
        const existingRows = tbody.querySelectorAll('tr');
        let existingRow = null;
        
        existingRows.forEach((row, index) => {
            console.log(`Checking row ${index}:`, row);
            const select = row.querySelector('.produk-select');
            if (select && select.value == product.id) {
                existingRow = row;
            }
        });
        
        console.log('Existing row found:', existingRow);
        
        if (existingRow) {
            console.log('Updating existing row');
            // Jika produk sudah ada, tambah quantity
            const qtyInput = existingRow.querySelector('.jumlah');
            const currentQty = parseInt(qtyInput.value) || 0;
            qtyInput.value = currentQty + 1;
            
            // Update subtotal
            updateRowSubtotal(existingRow);
            
            // Highlight row yang sudah ada
            existingRow.style.backgroundColor = '#d4edda';
            setTimeout(() => {
                existingRow.style.backgroundColor = '';
            }, 1000);
        } else {
            console.log('Creating new row');
            // Tambah baris baru
            const newRow = createProductRow(product);
            console.log('New row created:', newRow);
            tbody.appendChild(newRow);
            console.log('Row appended to tbody');
            console.log('Total rows after adding:', tbody.querySelectorAll('tr').length);
            
            // Highlight baris baru
            newRow.style.backgroundColor = '#d1ecf1';
            setTimeout(() => {
                newRow.style.backgroundColor = '';
            }, 1000);
        }
        
        // Update total
        updateAllTotals();
        
        // Focus ke barcode input untuk scan berikutnya
        resetScannerState();
    }
    
    // Fungsi untuk membuat baris produk baru
    function createProductRow(product) {
        console.log('createProductRow called with:', product);
        
        // Buat baris baru dari scratch
        const newRow = document.createElement('tr');
        
        // Generate HTML untuk baris produk
        newRow.innerHTML = `
            <td>
                <select name="produk_id[]" class="form-select produk-select" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($produks as $p)
                        <option value="{{ $p->id }}" data-stok="{{ $p->stok ?? 0 }}" data-harga="{{ $p->harga_jual ?? 0 }}">
                            {{ $p->nama_produk ?? $p->nama }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted stok-info" style="font-size: 0.8em;">Stok tersedia: ${product.stok_tersedia}</small>
            </td>
            <td>
                <input type="number" name="jumlah[]" class="form-control jumlah text-end" value="1" min="1" required>
            </td>
            <td>
                <input type="text" name="harga_satuan[]" class="form-control harga text-end" value="${formatCurrency(parseFloat(product.harga_jual) || 0)}" readonly>
            </td>
            <td>
                <input type="number" name="diskon_persen[]" class="form-control diskon text-end" value="0" min="0" max="100" step="0.1">
            </td>
            <td>
                <input type="text" name="subtotal[]" class="form-control subtotal text-end" value="${formatCurrency(parseFloat(product.harga_jual) || 0)}" readonly>
            </td>
            <td style="width:6%">
                <button class="btn btn-danger btn-sm" type="button" onclick="removeRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        // Set product selection
        const select = newRow.querySelector('.produk-select');
        select.value = product.id;
        
        console.log('Product row created from scratch');
        
        // Add event listeners
        select.addEventListener('change', function() {
            updateRowFromProduct(newRow);
        });
        
        newRow.querySelector('.jumlah').addEventListener('input', function() {
            updateRowSubtotal(newRow);
        });
        
        newRow.querySelector('.diskon').addEventListener('input', function() {
            updateRowSubtotal(newRow);
        });
        
        // Calculate initial subtotal
        updateRowSubtotal(newRow);
        
        console.log('createProductRow completed, returning:', newRow);
        return newRow;
    }
    
    // Fungsi untuk update data produk saat dropdown berubah
    function updateRowFromProduct(row) {
        const select = row.querySelector('.produk-select');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const price = parseFloat(selectedOption.dataset.price) || 0;
            const stok = parseFloat(selectedOption.dataset.stok) || 0;
            const hargaInput = row.querySelector('.harga');
            const stokInfo = row.querySelector('.stok-info');
            
            // Update harga
            hargaInput.value = formatCurrency(price);
            
            // Update stok info
            if (stokInfo) {
                stokInfo.textContent = `Stok tersedia: ${stok}`;
                stokInfo.className = stok > 0 ? 'text-muted stok-info' : 'text-danger stok-info';
            }
            
            // Update subtotal
            updateRowSubtotal(row);
        }
    }
    
    // Fungsi untuk update subtotal baris
    function updateRowSubtotal(row) {
        const qtyInput = row.querySelector('.jumlah');
        const hargaInput = row.querySelector('.harga');
        const diskonInput = row.querySelector('.diskon');
        const subtotalInput = row.querySelector('.subtotal');
        
        const qty = parseFloat(qtyInput.value) || 0;
        const harga = parseFloat(hargaInput.value.replace(/[^\d]/g, '')) || 0;
        const diskonPersen = parseFloat(diskonInput.value) || 0;
        
        const subtotal = qty * harga * (1 - diskonPersen / 100);
        subtotalInput.value = formatCurrency(subtotal);
        
        updateAllTotals();
    }
    
    // Fungsi untuk update semua total
    function updateAllTotals() {
        const rows = document.querySelectorAll('#detailTableJual tbody tr');
        let totalProduk = 0;
        
        rows.forEach(row => {
            const subtotalInput = row.querySelector('.subtotal');
            const subtotal = parseFloat(subtotalInput.value.replace(/[^\d]/g, '')) || 0;
            totalProduk += subtotal;
        });
        
        // Update subtotal produk
        const subtotalProdukInput = document.querySelector('input[name="subtotal_produk"]');
        if (subtotalProdukInput) {
            subtotalProdukInput.value = formatCurrency(totalProduk);
        }
        
        // Update biaya-biaya dan total final
        calculateFinalTotal();
    }
    
    // Fungsi untuk menghitung total final
    function calculateFinalTotal() {
        const subtotalProduk = parseFloat(document.querySelector('input[name="subtotal_produk"]').value.replace(/[^\d]/g, '')) || 0;
        const biayaOngkir = parseFloat(document.getElementById('biaya_ongkir').value) || 0;
        const biayaService = parseFloat(document.getElementById('biaya_service').value) || 0;
        const ppnPersen = parseFloat(document.getElementById('ppn_persen').value) || 0;
        
        const ppnBase = subtotalProduk + biayaOngkir + biayaService;
        const totalPPN = ppnBase * (ppnPersen / 100);
        const totalFinal = ppnBase + totalPPN;
        
        document.getElementById('total_ppn').value = formatCurrency(totalPPN);
        document.getElementById('total_final').value = formatCurrency(totalFinal);
    }
    
    // Fungsi format currency
    function formatCurrency(value) {
        const roundedValue = Math.round(parseFloat(value) * 1000) / 1000;
        return 'Rp ' + roundedValue.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 3 });
    }
    
    // Fungsi untuk membuat baris kosong
    function createEmptyRow() {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <select name="produk_id[]" class="form-select produk-select" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($produks as $p)
                        <option value="{{ $p->id }}" data-stok="{{ $p->stok ?? 0 }}" data-harga="{{ $p->harga_jual ?? 0 }}">
                            {{ $p->nama_produk ?? $p->nama }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted stok-info" style="font-size: 0.8em;">Stok tersedia: -</small>
            </td>
            <td>
                <input type="number" name="jumlah[]" class="form-control jumlah text-end" value="1" min="1" required>
            </td>
            <td>
                <input type="text" name="harga_satuan[]" class="form-control harga text-end" value="0" readonly>
            </td>
            <td>
                <input type="number" name="diskon_persen[]" class="form-control diskon text-end" value="0" min="0" max="100" step="0.1">
            </td>
            <td>
                <input type="text" name="subtotal[]" class="form-control subtotal text-end" value="0" readonly>
            </td>
            <td style="width:6%">
                <button class="btn btn-danger btn-sm" type="button" onclick="removeRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        // Add event listeners
        const select = newRow.querySelector('.produk-select');
        const qtyInput = newRow.querySelector('.jumlah');
        const diskonInput = newRow.querySelector('.diskon');
        
        select.addEventListener('change', function() {
            updateRowFromProduct(newRow);
        });
        
        qtyInput.addEventListener('input', function() {
            updateRowSubtotal(newRow);
        });
        
        diskonInput.addEventListener('input', function() {
            updateRowSubtotal(newRow);
        });
        
        return newRow;
    }
    
        
    // Simple dan robust barcode scanner detection
    barcodeInput.addEventListener('input', function(e) {
        const value = e.target.value.trim();
        console.log('Input event triggered, value:', value, 'length:', value.length);
        
        // Preview produk jika ada input
        if (value.length > 0) {
            console.log('Calling previewProduct with:', value);
            previewProduct(value);
        } else {
            console.log('Input empty, hiding preview');
            hidePreview();
        }
        
        // Jika input memiliki panjang barcode (minimal 8 karakter) - auto process untuk scanner
        if (value.length >= 8) {
            updateScanIndicator('Mencari produk...', 'bg-info');
            processBarcode(value);
        }
    });
    
    // Event listener untuk paste (jika barcode di-paste)
    barcodeInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedData = e.clipboardData.getData('text').trim();
        if (pastedData.length >= 8) {
            updateScanIndicator('Mencari produk...', 'bg-info');
            processBarcode(pastedData);
        }
    });
    
    // Event listener untuk keydown (Enter key untuk manual input)
    let lastPreviewProduct = null;
    
    barcodeInput.addEventListener('keydown', function(e) {
        // Handle Enter key untuk menambahkan produk dari preview
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            
            const value = barcodeInput.value.trim();
            if (value.length >= 3 && lastPreviewProduct) {
                // Tambahkan produk yang sedang di-preview
                addProductToTable(lastPreviewProduct);
                barcodeInput.value = '';
                hidePreview();
                updateScanIndicator('Produk ditambahkan!', 'bg-success');
                playBeep();
            } else if (value.length >= 8) {
                // Fallback ke auto-process untuk barcode panjang
                processBarcode(value);
            }
            return;
        }
        
        // Abaikan navigation keys lainnya
        if ([9, 37, 38, 39, 40].includes(e.keyCode)) {
            return;
        }
        
        // Abaikan jika hanya modifier keys
        if (e.ctrlKey || e.altKey || e.metaKey) {
            return;
        }
    });
    
    // Fungsi untuk memproses barcode
    function processBarcode(barcode) {
        updateScanIndicator('Mencari produk...', 'bg-info');
        
        findProductByBarcode(barcode)
            .then(product => {
                playBeep();
                updateScanIndicator('Produk ditemukan!', 'bg-success');
                addProductToTable(product);
                
                // Clear input setelah 2 detik agar kode terlihat dulu
                setTimeout(() => {
                    barcodeInput.value = '';
                }, 2000);
            })
            .catch(error => {
                updateScanIndicator('Produk tidak ditemukan!', 'bg-danger');
                setTimeout(() => {
                    resetScannerState();
                }, 2000);
            });
    }
    
    // Event listeners untuk tombol tambah dan hapus baris
    document.getElementById('addRowJual').addEventListener('click', function() {
        const table = document.getElementById('detailTableJual');
        const tbody = table.querySelector('tbody');
        
        // Tambah baris kosong baru
        const newRow = createEmptyRow();
        tbody.appendChild(newRow);
    });
    
    // Event listener untuk tombol hapus baris (gunakan event delegation)
    document.getElementById('detailTableJual').addEventListener('click', function(e) {
        if (e.target.classList.contains('removeRow')) {
            const row = e.target.closest('tr');
            const tbody = row.parentElement;
            
            // Hapus baris jika ada lebih dari 1 baris
            if (tbody.children.length > 1) {
                row.remove();
                updateAllTotals();
            }
        }
    });
    
    // Initialize event listeners untuk baris pertama
    const firstRow = document.querySelector('#detailTableJual tbody tr:first-child');
    if (firstRow) {
        const select = firstRow.querySelector('.produk-select');
        const qtyInput = firstRow.querySelector('.jumlah');
        const diskonInput = firstRow.querySelector('.diskon');
        
        select.addEventListener('change', function() {
            updateRowFromProduct(firstRow);
        });
        
        qtyInput.addEventListener('input', function() {
            updateRowSubtotal(firstRow);
        });
        
        diskonInput.addEventListener('input', function() {
            updateRowSubtotal(firstRow);
        });
    }
    
    // Event listeners untuk biaya-biaya
    document.getElementById('biaya_ongkir').addEventListener('input', calculateFinalTotal);
    document.getElementById('biaya_service').addEventListener('input', calculateFinalTotal);
    document.getElementById('ppn_persen').addEventListener('input', calculateFinalTotal);
    
    // Initialize barcode scanner
    resetScannerState();
});
</script>

@endsection
