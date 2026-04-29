@extends('layouts.app')

@section('title', 'Tambah Penjualan')

@section('content')

<!--
╔═══════════════════════════════════════════════════════════════════════════╗
║                    SISTEM BARCODE SCANNER PROFESIONAL                     ║
║                   Seperti di Supermarket Besar (Indomaret, Alfamart)      ║
╚═══════════════════════════════════════════════════════════════════════════╝

FITUR UTAMA:
✓ Scan barcode otomatis dengan scanner fisik
✓ Pencarian real-time saat mengetik manual
✓ Notifikasi visual (toast) dan audio (beep) seperti di kasir supermarket
✓ Validasi stok otomatis sebelum menambah produk
✓ Auto-increment quantity jika produk sudah ada di keranjang
✓ Fokus otomatis ke input scanner
✓ Keyboard shortcuts (F2 untuk fokus, Escape untuk clear, Arrow keys untuk navigasi)
✓ Highlight baris produk yang baru ditambahkan
✓ Tampilan hasil pencarian yang informatif dengan badge stok

CARA KERJA:
1. SCAN BARCODE: Arahkan scanner ke barcode produk, sistem akan otomatis mendeteksi
   dan menambahkan produk ke keranjang
2. KETIK MANUAL: Ketik nama produk atau barcode untuk mencari, pilih dari hasil
3. PRODUK DITEMUKAN: Produk langsung masuk ke detail penjualan dengan beep sukses
4. PRODUK TIDAK DITEMUKAN: Notifikasi error dengan beep gagal
5. STOK HABIS: Notifikasi warning jika stok tidak tersedia

KEYBOARD SHORTCUTS:
- F2: Fokus ke input scanner
- Escape: Clear input dan tutup hasil pencarian
- Arrow Down/Up: Navigasi hasil pencarian
- Enter: Pilih produk yang di-highlight

TEKNOLOGI:
- Web Audio API untuk beep sound
- Real-time search dengan debouncing
- Automatic barcode detection (80ms timeout)
- Visual feedback dengan CSS animations
-->

<script>
// Simple, bulletproof calculation function
function hitungTotal() {
    // Get all subtotals from table rows
    let subtotalProduk = 0;
    document.querySelectorAll('#detailTableJual tbody tr').forEach(function(row) {
        const subtotalEl = row.querySelector('.subtotal');
        if (subtotalEl && subtotalEl.value) {
            const rawValue = subtotalEl.getAttribute('data-raw');
            if (rawValue) {
                subtotalProduk += parseFloat(rawValue);
            } else {
                const numStr = subtotalEl.value.replace(/\./g, '').replace(',', '.');
                subtotalProduk += parseFloat(numStr) || 0;
            }
        }
    });

    // Get ongkir value
    const ongkirEl = document.getElementById('biaya_ongkir');
    let ongkir = 0;
    if (ongkirEl) {
        ongkir = parseFloat(ongkirEl.value) || 0;
        if (ongkir === 0 && ongkirEl.selectedIndex > 0) {
            const opt = ongkirEl.options[ongkirEl.selectedIndex];
            if (opt) ongkir = parseFloat(opt.value) || 0;
        }
    }

    // Get PPN percentage
    const ppnEl = document.getElementById('ppn_persen');
    const ppnPersen = ppnEl ? (parseFloat(ppnEl.value) || 0) : 0;

    // Calculate: PPN = Subtotal × PPN% (ongkir tidak masuk PPN)
    const totalPPN   = Math.round(subtotalProduk * ppnPersen / 100);
    const totalFinal = subtotalProduk + totalPPN + ongkir;

    function fmtIDR(n) { return Math.round(n).toLocaleString('id-ID'); }

    // Update hidden inputs (form submission)
    const hiddenSubtotal = document.getElementById('subtotal_produk_hidden');
    if (hiddenSubtotal) hiddenSubtotal.value = Math.round(subtotalProduk);

    const hiddenPPN = document.getElementById('total_ppn');
    if (hiddenPPN) hiddenPPN.value = Math.round(totalPPN);

    const hiddenTotal = document.getElementById('total_final');
    if (hiddenTotal) hiddenTotal.value = Math.round(totalFinal);

    // Update display spans
    const ds = document.getElementById('display_subtotal_produk');
    if (ds) ds.textContent = fmtIDR(subtotalProduk);

    const dp = document.getElementById('display_total_ppn');
    if (dp) dp.textContent = fmtIDR(totalPPN);

    const dg = document.getElementById('display_biaya_ongkir');
    if (dg) dg.textContent = fmtIDR(ongkir);

    const df = document.getElementById('display_total_final');
    if (df) df.textContent = fmtIDR(totalFinal);
    
    // Update cart counter
    if (typeof updateCartCounter === 'function') {
        updateCartCounter();
    }
}

// Tambah baris produk (dipanggil langsung dari onclick button)
function tambahBarisProduk() {
    const table = document.getElementById('detailTableJual');
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const firstRow = tbody.rows[0];
    if (!firstRow) return;
    
    const clone = firstRow.cloneNode(true);
    
    // Reset semua input di baris baru
    clone.querySelectorAll('input').forEach(inp => {
        if (inp.classList.contains('jumlah')) {
            inp.value = 1;
        } else if (inp.classList.contains('harga')) {
            inp.removeAttribute('readonly');
            inp.value = 0;
            inp.setAttribute('readonly', 'readonly');
            inp.removeAttribute('data-raw');
        } else if (inp.classList.contains('diskon')) {
            inp.value = 0;
        } else if (inp.classList.contains('subtotal')) {
            inp.value = 0;
            inp.removeAttribute('data-raw');
        }
    });
    
    // Reset select
    clone.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
    
    // Reset stok info
    const stokInfo = clone.querySelector('.stok-info');
    if (stokInfo) stokInfo.textContent = '';
    
    tbody.appendChild(clone);
    hitungTotal();
}

// Hapus baris produk (dipanggil langsung dari onclick button)
function hapusBarisProduk(btn) {
    const table = document.getElementById('detailTableJual');
    if (!table) return;
    const rows = table.querySelectorAll('tbody tr');
    if (rows.length <= 1) return; // minimal 1 baris
    const tr = btn.closest('tr');
    if (tr) {
        tr.remove();
        hitungTotal();
    }
}

// Toggle opsi "Terima di" berdasarkan metode pembayaran
function toggleSumberDana() {
    const paymentMethod = document.getElementById('payment_method_jual').value;
    const sumberDana    = document.getElementById('sumber_dana_jual');
    if (!sumberDana) return;

    const allOptions = sumberDana.querySelectorAll('option');
    let firstVisible = null;

    allOptions.forEach(opt => {
        const tipe = opt.getAttribute('data-tipe'); // 'kas', 'bank', 'piutang'
        let show = true;

        if (paymentMethod === 'cash') {
            show = (tipe === 'kas');
        } else if (paymentMethod === 'transfer') {
            show = (tipe === 'bank');
        } else if (paymentMethod === 'credit') {
            show = (tipe === 'piutang');
        }

        opt.style.display = show ? '' : 'none';
        opt.disabled = !show;
        if (show && !firstVisible) firstVisible = opt;
    });

    // Kalau pilihan saat ini tersembunyi, pilih yang pertama visible
    const currentOpt = sumberDana.options[sumberDana.selectedIndex];
    if (!currentOpt || currentOpt.disabled) {
        if (firstVisible) sumberDana.value = firstVisible.value;
    }
}

// Jalankan saat halaman pertama kali load
document.addEventListener('DOMContentLoaded', function() {
    toggleSumberDana();
});
</script>

<style>
/* Enhanced search result styling */
.search-result-item {
    transition: all 0.2s ease;
    border-radius: 4px;
    padding: 12px !important;
    margin: 0;
    cursor: pointer;
}

.search-result-item:hover {
    background-color: #e3f2fd !important;
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.search-result-item:active {
    background-color: #bbdefb !important;
    transform: translateX(2px);
}

.search-result-item.selected {
    background-color: #e3f2fd !important;
    border-left: 4px solid #2196f3;
}

/* Barcode highlight styling */
mark.bg-warning {
    background-color: #fff3cd !important;
    color: #856404 !important;
    font-weight: bold;
    padding: 2px 4px;
    border-radius: 3px;
}

/* Search results container */
#search-results {
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Barcode scanner input focus */
#barcode-scanner:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
    border-width: 2px;
}

/* Scan indicator animations */
.input-group-text {
    transition: all 0.3s ease;
}

/* Product row highlight animation */
@keyframes rowHighlight {
    0% { background-color: #d4edda; }
    100% { background-color: transparent; }
}

/* Toast notification animations */
@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

/* Keyboard shortcut badge */
kbd {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 3px;
    padding: 2px 6px;
    font-size: 0.875rem;
    font-family: monospace;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

/* Scanner card enhancement */
.card.border-primary {
    border-width: 2px !important;
}

/* Search results scrollbar */
#search-results-body::-webkit-scrollbar {
    width: 8px;
}

#search-results-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

#search-results-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

#search-results-body::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Cart counter animation */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.15); }
    100% { transform: scale(1); }
}

#cart-counter {
    transition: all 0.3s ease;
}
</style>

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
                <label class="form-label">Waktu</label>
                <input type="time" name="waktu" class="form-control" value="{{ now()->format('H:i') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Metode Pembayaran</label>
                <select name="payment_method" id="payment_method_jual" class="form-select" required onchange="toggleSumberDana()">
                    <option value="cash" selected>Tunai</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="credit">Kredit</option>
                </select>
            </div>
            <div class="col-md-3" id="sumber_dana_wrapper_jual">
                <label class="form-label">Terima di</label>
                <select name="sumber_dana" id="sumber_dana_jual" class="form-select">
                    @foreach($kasbank as $kb)
                        @php
                            $kode = $kb->kode_akun;
                            if ($kode === '118' || stripos($kb->nama_akun, 'piutang') !== false) {
                                $tipe = 'piutang';
                            } elseif ($kode === '111' || stripos($kb->nama_akun, 'bank') !== false) {
                                $tipe = 'bank';
                            } else {
                                $tipe = 'kas'; // 112, 113, dll
                            }
                        @endphp
                        <option value="{{ $kb->kode_akun }}" data-tipe="{{ $tipe }}">
                            {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Barcode Scanner Input -->
        <div class="card mb-3 border-primary shadow-sm">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-barcode fa-3x text-primary"></i>
                    </div>
                    <div class="col">
                        <label class="form-label mb-2 fw-bold text-primary">
                            <i class="fas fa-qrcode me-1"></i>Scan Barcode Produk
                        </label>
                        <div class="input-group input-group-lg">
                            <input type="text" id="barcode-scanner" class="form-control form-control-lg border-primary" 
                                   placeholder="Scan barcode atau ketik untuk mencari produk..." 
                                   autocomplete="off" autofocus
                                   style="font-size: 1.1rem; font-weight: 500;">
                            <div class="input-group-text bg-success text-white" style="min-width: 150px;">
                                <i class="fas fa-wifi me-2"></i>
                                <span id="scan-indicator" class="fw-bold">Siap Scan</span>
                            </div>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetScannerState()" title="Reset Scanner">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div class="mt-2 d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Cara Pakai:</strong> Scan barcode dengan scanner atau ketik untuk mencari produk
                            </small>
                            <div class="d-flex align-items-center gap-3">
                                <small class="text-muted">
                                    <kbd>F2</kbd> untuk fokus ke scanner
                                </small>
                                <div class="badge bg-primary" id="cart-counter" style="font-size: 0.9rem; padding: 6px 12px;">
                                    <i class="fas fa-shopping-cart me-1"></i>
                                    <span id="cart-count">0</span> item
                                </div>
                            </div>
                        </div>
                        
                        <!-- Real-time search results -->
                        <div id="search-results" class="mt-3" style="display: none;">
                            <div class="card border-info shadow">
                                <div class="card-header bg-info text-white py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-search me-2"></i><strong>Hasil Pencarian</strong></span>
                                        <span class="badge bg-white text-info"><span id="search-count">0</span> produk</span>
                                    </div>
                                </div>
                                <div class="card-body p-0" id="search-results-body" style="max-height: 300px; overflow-y: auto;">
                                    <!-- Results will be populated here -->
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
                        <th style="width:6%"><button class="btn btn-success btn-sm" type="button" id="addRowJual" onclick="tambahBarisProduk()">+</button></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="produk_id[]" class="form-select produk-select" required onchange="
                                const tr = this.closest('tr');
                                const hargaInput = tr.querySelector('.harga');
                                const subtotalInput = tr.querySelector('.subtotal');
                                const selectedOption = this.options[this.selectedIndex];
                                
                                if (!this.value) {
                                    hargaInput.removeAttribute('readonly');
                                    hargaInput.value = 0;
                                    hargaInput.setAttribute('readonly', 'readonly');
                                    subtotalInput.value = 0;
                                    hitungTotal();
                                    return;
                                }
                                
                                const harga = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                                const qty = parseFloat(tr.querySelector('.jumlah').value) || 1;
                                const diskon = parseFloat(tr.querySelector('.diskon').value) || 0;
                                const subtotal = qty * harga * (1 - diskon / 100);
                                
                                hargaInput.removeAttribute('readonly');
                                hargaInput.value = harga.toLocaleString('id-ID');
                                hargaInput.setAttribute('readonly', 'readonly');
                                hargaInput.setAttribute('data-raw', harga);
                                subtotalInput.value = subtotal.toLocaleString('id-ID');
                                subtotalInput.setAttribute('data-raw', subtotal);
                                
                                hitungTotal();
                            ">
                                <option value="">-- Pilih Produk --</option>
                                <optgroup label="Produk Individual">
                                    @foreach($produks as $p)
                                        <option value="{{ $p->id }}" 
                                                data-price="{{ $p->harga_jual ?? 0 }}"
                                                data-stok="{{ $p->stok ?? 0 }}"
                                                data-type="produk"
                                                data-nama="{{ $p->nama_produk ?? $p->nama }}">
                                            {{ $p->nama_produk ?? $p->nama }} (Stok: {{ number_format($p->stok ?? 0, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </optgroup>
                                @if($paketMenus->count() > 0)
                                <optgroup label="Paket Menu">
                                    @foreach($paketMenus as $paket)
                                        <option value="paket_{{ $paket->id }}" 
                                                data-price="{{ round($paket->harga_paket ?? 0) }}"
                                                data-stok="999"
                                                data-type="paket"
                                                data-paket-id="{{ $paket->id }}"
                                                data-paket-details="{{ json_encode($paket->details->map(function($d) { return ['produk_id' => $d->produk_id, 'jumlah' => $d->jumlah, 'nama_produk' => $d->produk->nama_produk ?? $d->produk->nama]; })) }}">
                                            {{ $paket->nama_paket }} - Rp {{ number_format($paket->harga_paket, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                @endif
                            </select>
                            <small class="text-muted stok-info"></small>
                        </td>
                        <td><input type="number" step="1" min="1" name="jumlah[]" class="form-control jumlah" value="1" required onchange="
                            const tr = this.closest('tr');
                            const hargaInput = tr.querySelector('.harga');
                            const harga = parseFloat(hargaInput.getAttribute('data-raw') || hargaInput.value.replace(/\./g,'').replace(',','.')) || 0;
                            const qty = parseFloat(this.value) || 1;
                            const diskon = parseFloat(tr.querySelector('.diskon').value) || 0;
                            const subtotal = qty * harga * (1 - diskon / 100);
                            const subtotalInput = tr.querySelector('.subtotal');
                            subtotalInput.value = subtotal.toLocaleString('id-ID');
                            subtotalInput.setAttribute('data-raw', subtotal);
                            hitungTotal();
                        " oninput="
                            const tr = this.closest('tr');
                            const hargaInput = tr.querySelector('.harga');
                            const harga = parseFloat(hargaInput.getAttribute('data-raw') || hargaInput.value.replace(/\./g,'').replace(',','.')) || 0;
                            const qty = parseFloat(this.value) || 1;
                            const diskon = parseFloat(tr.querySelector('.diskon').value) || 0;
                            const subtotal = qty * harga * (1 - diskon / 100);
                            const subtotalInput = tr.querySelector('.subtotal');
                            subtotalInput.value = subtotal.toLocaleString('id-ID');
                            subtotalInput.setAttribute('data-raw', subtotal);
                            hitungTotal();
                        "></td>
                        <td><input type="text" name="harga_satuan[]" class="form-control harga" value="0" readonly required style="background-color: #e9ecef; cursor: not-allowed;"></td>
                        <td><input type="number" step="0.01" min="0" max="100" name="diskon_persen[]" class="form-control diskon" value="0" onchange="
                            const tr = this.closest('tr');
                            const hargaInput = tr.querySelector('.harga');
                            const harga = parseFloat(hargaInput.getAttribute('data-raw') || hargaInput.value.replace(/\./g,'').replace(',','.')) || 0;
                            const qty = parseFloat(tr.querySelector('.jumlah').value) || 1;
                            const diskon = parseFloat(this.value) || 0;
                            const subtotal = qty * harga * (1 - diskon / 100);
                            const subtotalInput = tr.querySelector('.subtotal');
                            subtotalInput.value = subtotal.toLocaleString('id-ID');
                            subtotalInput.setAttribute('data-raw', subtotal);
                            hitungTotal();
                        "></td>
                        <td><input type="text" class="form-control subtotal" value="0" readonly></td>
                        <td><button type="button" class="btn btn-danger btn-sm removeRow" onclick="hapusBarisProduk(this)">-</button></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Hidden inputs for form submission -->
        <input type="hidden" name="subtotal_produk" id="subtotal_produk_hidden" value="0">
        <input type="hidden" name="total_ppn" id="total_ppn" value="0">
        <input type="hidden" name="total" id="total_final" value="0">

        <!-- Ringkasan Pembayaran Card -->
        <div class="card mt-4 border-0" style="background:#f8f9fa; border-radius:12px;">
            <div class="card-body px-4 py-3">

                <!-- Row: Subtotal Produk -->
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted">Subtotal Produk</span>
                    <span class="fw-semibold">Rp <span id="display_subtotal_produk">0</span></span>
                </div>

                <!-- Row: PPN -->
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-2">PPN (%)</span>
                        <input type="number" name="ppn_persen" id="ppn_persen"
                               class="form-control form-control-sm"
                               style="width:80px;"
                               value="11" min="0" max="100" step="0.01"
                               oninput="hitungTotal();"
                               onchange="hitungTotal();">
                        <span class="text-muted ms-4">Total PPN</span>
                    </div>
                    <span class="fw-semibold">Rp <span id="display_total_ppn">0</span></span>
                </div>

                <!-- Row: Biaya Ongkir -->
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted me-2">Biaya Ongkir</span>
                        <select name="biaya_ongkir" id="biaya_ongkir"
                                class="form-select form-select-sm"
                                style="width:220px;"
                                onchange="hitungTotal();">
                            <option value="0">Tanpa Ongkir</option>
                            @foreach($ongkirSettings as $ongkir)
                                <option value="{{ (int)$ongkir->harga_ongkir }}">
                                    {{ $ongkir->getJarakLabel() }} - Rp {{ number_format($ongkir->harga_ongkir, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <span class="fw-semibold">Rp <span id="display_biaya_ongkir">0</span></span>
                </div>

                <!-- Separator -->
                <hr class="my-2">

                <!-- Row: Total Bayar -->
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="fw-semibold">Total Bayar</span>
                    <span class="fw-bold text-success" style="font-size:1.25rem;">Rp <span id="display_total_final">0</span></span>
                </div>

            </div>
        </div>

        <div class="text-end mt-4">
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">Batal</a>
            <button type="button" class="btn btn-primary" id="btn-bayar" onclick="submitForPayment()">Bayar</button>
        </div>
    </form>

    <script>
    function submitForPayment() {
        // Validate form
        const form = document.getElementById('form-penjualan');
        
        // Check if there are items in the table
        const tableRows = document.querySelectorAll('#detailTableJual tbody tr');
        if (tableRows.length === 0) {
            alert('Tambahkan minimal satu produk');
            return;
        }
        
        // Check if all rows have valid data
        let hasValidItem = false;
        tableRows.forEach(row => {
            const produkSelect = row.querySelector('.produk-select');
            if (produkSelect && produkSelect.value) {
                hasValidItem = true;
            }
        });
        
        if (!hasValidItem) {
            alert('Tambahkan minimal satu produk');
            return;
        }
        
        // Get payment method
        const paymentMethod = document.getElementById('payment_method_jual').value;
        
        // Get total
        const totalInput = document.getElementById('total_final');
        const total = parseFloat(totalInput.value) || 0;
        
        if (total <= 0) {
            alert('Total pembayaran harus lebih dari 0');
            return;
        }
        
        // Store form data in session via AJAX
        const formData = new FormData(form);
        
        // Add table data
        const tableData = [];
        tableRows.forEach(row => {
            const produkSelect = row.querySelector('.produk-select');
            if (produkSelect && produkSelect.value) {
                const subtotalEl = row.querySelector('.subtotal');
                const subtotalRaw = subtotalEl.getAttribute('data-raw');
                const subtotalVal = subtotalRaw
                    ? parseFloat(subtotalRaw)
                    : parseFloat(subtotalEl.value.replace(/\./g, '').replace(',', '.')) || 0;

                const hargaEl = row.querySelector('.harga');
                const hargaRaw = hargaEl.getAttribute('data-raw');
                const hargaVal = hargaRaw
                    ? parseFloat(hargaRaw)
                    : parseFloat(hargaEl.value.replace(/\./g, '').replace(',', '.')) || 0;

                tableData.push({
                    produk_id: produkSelect.value,
                    jumlah: row.querySelector('.jumlah').value,
                    harga_satuan: hargaVal,
                    diskon_persen: row.querySelector('.diskon').value,
                    subtotal: subtotalVal
                });
            }
        });
        
        // Prepare data for payment
        const biayaOngkir = parseFloat(document.getElementById('biaya_ongkir').value) || 0;
        const subtotalProdukVal = document.getElementById('subtotal_produk_hidden').value;
        const totalPPNVal = document.getElementById('total_ppn').value;
        const paymentData = {
            tanggal: document.querySelector('input[name="tanggal"]').value,
            waktu: document.querySelector('input[name="waktu"]').value,
            payment_method: paymentMethod,
            sumber_dana: document.getElementById('sumber_dana_jual').value,
            subtotal_produk: parseFloat(subtotalProdukVal) || 0,
            biaya_ongkir: biayaOngkir,
            ppn_persen: parseFloat(document.getElementById('ppn_persen').value) || 0,
            total_ppn: parseFloat(totalPPNVal) || 0,
            total: total,
            items: tableData
        };
        
        // Store in session and redirect to payment page
        fetch('{{ route("transaksi.penjualan.prepare-payment") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(paymentData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to payment page
                window.location.href = data.redirect_url;
            } else {
                alert('Error: ' + (data.message || 'Terjadi kesalahan'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
        });
    }
    </script>
</div>

<script>
// Product data for barcode lookup
const productData = {
    @foreach($produks as $p)
    @if($p->barcode)
    '{{ trim($p->barcode) }}': {
        id: {{ $p->id }},
        nama: '{{ addslashes($p->nama_produk ?? $p->nama) }}',
        harga: {{ round($p->harga_jual ?? 0) }},
        stok: {{ $p->stok ?? 0 }},
        barcode: '{{ trim($p->barcode) }}'
    },
    @endif
    @endforeach
};

// Create searchable product array for real-time search
const searchableProducts = [
    @foreach($produks as $p)
    {
        id: {{ $p->id }},
        nama: '{{ addslashes($p->nama_produk ?? $p->nama) }}',
        harga: {{ round($p->harga_jual ?? 0) }},
        stok: {{ $p->stok ?? 0 }},
        barcode: '{{ trim($p->barcode ?? '') }}',
        type: 'produk',
        searchText: '{{ strtolower(addslashes($p->nama_produk ?? $p->nama)) }} {{ trim($p->barcode ?? '') }}'.toLowerCase()
    },
    @endforeach
    @foreach($paketMenus as $paket)
    {
        id: 'paket_{{ $paket->id }}',
        nama: '{{ addslashes($paket->nama_paket) }}',
        harga: {{ round($paket->harga_paket ?? 0) }},
        stok: 999,
        barcode: '',
        type: 'paket',
        paket_id: {{ $paket->id }},
        paket_details: {!! json_encode($paket->details->map(function($d) { return ['produk_id' => $d->produk_id, 'jumlah' => $d->jumlah, 'nama_produk' => $d->produk->nama_produk ?? $d->produk->nama]; })) !!},
        searchText: '{{ strtolower(addslashes($paket->nama_paket)) }} paket menu'.toLowerCase()
    },
    @endforeach
];

// Debug: Log productData to console
console.log('=== BARCODE SCANNER DEBUG ===');
console.log('Product Data loaded:', Object.keys(productData).length, 'products');
console.log('Product Data keys (barcodes):', Object.keys(productData));
console.log('Searchable Products loaded:', searchableProducts.length, 'products');

// Verify searchableProducts has prices and barcodes
if (searchableProducts.length > 0) {
    console.log('First product in searchableProducts:', searchableProducts[0]);
    console.log('First product harga:', searchableProducts[0].harga);
    console.log('First product barcode:', searchableProducts[0].barcode);
}

// Log all products with barcodes
const productsWithBarcode = searchableProducts.filter(p => p.barcode && p.barcode.length > 0);
console.log('Products with barcode:', productsWithBarcode.length);
if (productsWithBarcode.length > 0) {
    console.log('Sample products with barcode:', productsWithBarcode.slice(0, 3));
}

// Global utility functions (must be outside DOMContentLoaded)
function formatCurrency(value) {
    if (value === null || value === undefined || isNaN(value)) {
        return 'Rp 0';
    }
    const roundedValue = Math.round(parseFloat(value) * 1000) / 1000;
    return 'Rp ' + roundedValue.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 3 });
}

function parseCurrency(formattedValue) {
    if (!formattedValue) return 0;
    return parseFloat(formattedValue.toString().replace(/[^\d]/g, '')) || 0;
}

// SIMPLE function to set price manually
function setPriceManual(option, harga) {
    console.log('setPriceManual called with harga:', harga);
    
    // Find the select element and then the row
    const select = option.parentElement.parentElement;
    const tr = select.closest('tr');
    const hargaInput = tr.querySelector('.harga');
    
    // Remove readonly, set value, add readonly back
    hargaInput.removeAttribute('readonly');
    hargaInput.value = harga;
    hargaInput.setAttribute('readonly', 'readonly');
    
    console.log('Price set to:', hargaInput.value);
    
    // Recalculate
    recalcRow(tr);
    hitungTotal();
}

// Global functions for barcode system
function handleProdukChange(selectElement) {
    const tr = selectElement.closest('tr');
    const hargaInput = tr.querySelector('.harga');
    
    console.log('handleProdukChange called');
    console.log('Selected value:', selectElement.value);
    
    if (!selectElement.value) {
        hargaInput.value = 0;
        recalcRow(tr);
        hitungTotal();
        return;
    }
    
    // Ambil harga dari data-price
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const harga = parseFloat(selectedOption.getAttribute('data-price')) || 0;
    
    console.log('Data-price attribute:', selectedOption.getAttribute('data-price'));
    console.log('Parsed harga:', harga);
    
    // Set harga — store raw number in data-raw, display formatted
    hargaInput.removeAttribute('readonly');
    hargaInput.value = harga.toLocaleString('id-ID');
    hargaInput.setAttribute('readonly', 'readonly');
    hargaInput.setAttribute('data-raw', harga);
    
    // Recalculate row and total
    recalcRow(tr);
    hitungTotal();
    
    // Update stock info
    const stok = parseFloat(selectedOption.getAttribute('data-stok')) || 0;
    const stokInfo = tr.querySelector('.stok-info');
    if (stokInfo) {
        stokInfo.textContent = `Stok tersedia: ${stok.toLocaleString()}`;
        stokInfo.style.color = stok > 0 ? '#28a745' : '#dc3545';
    }
}

function updateSubtotal(qtyInput) {
    const tr = qtyInput.closest('tr');
    recalcRow(tr);
    hitungTotal();
}

function recalcRow(tr) {
    const q = Math.round(parseFloat(tr.querySelector('.jumlah').value) || 0);
    tr.querySelector('.jumlah').value = q;
    const hargaEl = tr.querySelector('.harga');
    // Baca data-raw (nilai numerik murni), fallback strip titik ribuan
    const p = parseFloat(hargaEl.getAttribute('data-raw') || hargaEl.value.replace(/\./g, '').replace(',', '.')) || 0;
    const dPct = Math.min(Math.max(parseFloat(tr.querySelector('.diskon').value) || 0, 0), 100);
    const sub = q * p;
    const dNom = sub * (dPct/100.0);
    const line = Math.max(sub - dNom, 0);
    // Format as Indonesian number without Rp prefix (e.g., "25.000")
    tr.querySelector('.subtotal').value = Math.round(line).toLocaleString('id-ID');
    // Store raw value for calculations
    tr.querySelector('.subtotal').setAttribute('data-raw', line);
    hitungTotal();
}


function setPriceFromSelect(tr) {
    const sel = tr.querySelector('.produk-select');
    const opt = sel.options[sel.selectedIndex];
    
    // If no product is selected, reset to defaults
    if (!opt || !opt.value) {
        tr.querySelector('.harga').value = 0;
        const stokInfo = tr.querySelector('.stok-info');
        if (stokInfo) stokInfo.textContent = '';
        const qtyInput = tr.querySelector('.jumlah');
        qtyInput.setAttribute('data-max-stok', '0');
        qtyInput.setAttribute('data-type', 'produk');
        recalcRow(tr);
        hitungTotal();
        return;
    }
    
    const selectedVal = opt.value;
    const type = opt.getAttribute('data-type') || 'produk';
    
    // Read price from data attribute
    let price = parseFloat(opt.getAttribute('data-price') || '0') || 0;
    let stok  = parseFloat(opt.getAttribute('data-stok')  || '0') || 0;
    
    // Fallback: look up in searchableProducts if price is still 0
    if (price === 0 && type !== 'paket') {
        const found = searchableProducts.find(p => String(p.id) === String(selectedVal));
        if (found) {
            price = found.harga;
            stok  = found.stok;
        }
    }
    
    // Set price — store raw number in data-raw, display formatted
    const hargaInput = tr.querySelector('.harga');
    hargaInput.removeAttribute('readonly');
    hargaInput.value = price.toLocaleString('id-ID');
    hargaInput.setAttribute('readonly', 'readonly');
    hargaInput.setAttribute('data-raw', price);
    
    // Update stok info
    const stokInfo = tr.querySelector('.stok-info');
    if (stokInfo) {
        if (type === 'paket') {
            try {
                const paketDetails = JSON.parse(opt.getAttribute('data-paket-details') || '[]');
                const detailText = paketDetails.map(d => `${d.nama_produk} (${d.jumlah})`).join(', ');
                stokInfo.innerHTML = `<span class="text-primary"><i class="fas fa-box me-1"></i><strong>Paket:</strong> ${detailText}</span>`;
            } catch(e) {
                stokInfo.textContent = 'Paket Menu';
            }
        } else {
            stokInfo.textContent = `Stok tersedia: ${stok.toLocaleString()}`;
            stokInfo.style.color = stok > 0 ? '#28a745' : '#dc3545';
        }
    }
    
    const qtyInput = tr.querySelector('.jumlah');
    qtyInput.setAttribute('data-max-stok', stok);
    qtyInput.setAttribute('data-type', type);
    
    if (type === 'paket') {
        qtyInput.setAttribute('data-paket-details', opt.getAttribute('data-paket-details') || '[]');
        qtyInput.setAttribute('data-paket-id', opt.getAttribute('data-paket-id') || '');
    }
    
    recalcRow(tr);
    hitungTotal();
}

function validateStock(tr) {
    const qtyInput = tr.querySelector('.jumlah');
    let qty = parseFloat(qtyInput.value) || 0;
    qty = Math.round(qty); // Round to nearest integer
    qtyInput.value = qty; // Update display
    
    const maxStok = parseFloat(qtyInput.getAttribute('data-max-stok') || '0') || 0;
    
    if (qty > maxStok) {
        alert(`Stok tidak cukup! Stok tersedia: ${maxStok.toLocaleString()}, Anda input: ${qty.toLocaleString()}`);
        qtyInput.value = maxStok;
        qtyInput.style.borderColor = '#dc3545';
        return false;
    } else {
        qtyInput.style.borderColor = '';
        return true;
    }
}

function highlightRow(row) {
    row.style.backgroundColor = '#d4edda';
    setTimeout(() => {
        row.style.backgroundColor = '';
        row.style.transition = 'background-color 0.5s ease';
    }, 500);
}

function findExistingProductRow(productId) {
    const table = document.getElementById('detailTableJual');
    const rows = table.querySelectorAll('tbody tr');
    
    console.log('Looking for existing product ID:', productId);
    
    for (let row of rows) {
        const select = row.querySelector('.produk-select');
        if (select && select.value) {
            if (select.value == productId) {
                return row;
            }
        }
    }
    return null;
}

// Automatic Barcode Scanner System
// ── Fungsi-fungsi yang masih dipakai oleh onclick di HTML ────────

// Dipanggil dari onclick di search results
function selectProductFromSearch(productId, productName, price, stock) {
    const tbl = document.getElementById('detailTableJual');
    const tbody = tbl.querySelector('tbody');
    const product = { id: productId, nama: productName, harga: price, stok: stock };

    // Cari baris yang sudah ada produk ini
    for (const row of tbody.querySelectorAll('tr')) {
        const sel = row.querySelector('.produk-select');
        if (sel && String(sel.value) === String(productId)) {
            const qi = row.querySelector('.jumlah');
            const nq = (parseFloat(qi.value) || 0) + 1;
            if (nq > stock) { alert('Stok tidak cukup! Tersedia: ' + stock); return; }
            qi.value = nq; recalcRow(row); hitungTotal();
            row.style.backgroundColor = '#d4edda';
            setTimeout(() => row.style.backgroundColor = '', 600);
            document.getElementById('barcode-scanner').value = '';
            document.getElementById('search-results').style.display = 'none';
            document.getElementById('barcode-scanner').focus();
            return;
        }
    }
    // Baris baru
    let targetRow = null;
    for (const row of tbody.querySelectorAll('tr')) {
        const sel = row.querySelector('.produk-select');
        if (!sel || !sel.value) { targetRow = row; break; }
    }
    if (!targetRow) { targetRow = createNewRow(); tbody.appendChild(targetRow); }
    const sel = targetRow.querySelector('.produk-select');
    sel.value = productId;
    targetRow.querySelector('.jumlah').value = 1;
    targetRow.querySelector('.diskon').value = 0;
    sel.dispatchEvent(new Event('change', { bubbles: true }));
    recalcRow(targetRow); hitungTotal();
    targetRow.style.backgroundColor = '#d4edda';
    setTimeout(() => targetRow.style.backgroundColor = '', 600);
    document.getElementById('barcode-scanner').value = '';
    document.getElementById('search-results').style.display = 'none';
    document.getElementById('barcode-scanner').focus();
}

// Dipanggil dari onclick di search results untuk paket
function selectPaketFromSearch(paketId, paketName, price, paketDetails) {
    const tbl = document.getElementById('detailTableJual');
    const tbody = tbl.querySelector('tbody');
    let targetRow = null;
    for (const row of tbody.querySelectorAll('tr')) {
        const sel = row.querySelector('.produk-select');
        if (!sel || !sel.value) { targetRow = row; break; }
    }
    if (!targetRow) { targetRow = createNewRow(); tbody.appendChild(targetRow); }
    const sel = targetRow.querySelector('.produk-select');
    sel.value = paketId;
    targetRow.querySelector('.jumlah').value = 1;
    targetRow.querySelector('.diskon').value = 0;
    sel.dispatchEvent(new Event('change', { bubbles: true }));
    recalcRow(targetRow); hitungTotal();
    targetRow.style.backgroundColor = '#d4edda';
    setTimeout(() => targetRow.style.backgroundColor = '', 600);
    document.getElementById('barcode-scanner').value = '';
    document.getElementById('search-results').style.display = 'none';
    document.getElementById('barcode-scanner').focus();
}

// Reset scanner (tombol refresh)
function resetScannerState() {
    document.getElementById('barcode-scanner').value = '';
    const sr = document.getElementById('search-results');
    if (sr) sr.style.display = 'none';
    const ind = document.getElementById('scan-indicator');
    if (ind) { ind.textContent = 'Siap Scan'; ind.parentElement.className = 'input-group-text bg-success text-white'; }
    document.getElementById('barcode-scanner').focus();
}

// Buat baris baru di tabel
function createNewRow() {
    const tbl = document.getElementById('detailTableJual');
    const clone = tbl.querySelector('tbody tr').cloneNode(true);
    clone.querySelectorAll('input').forEach(inp => {
        if (inp.classList.contains('jumlah')) inp.value = 1;
        else if (inp.classList.contains('harga')) { inp.value = 0; inp.removeAttribute('data-raw'); }
        else if (inp.classList.contains('diskon')) inp.value = 0;
        else if (inp.classList.contains('subtotal')) { inp.value = '0'; inp.removeAttribute('data-raw'); }
    });
    clone.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
    const si = clone.querySelector('.stok-info');
    if (si) si.textContent = '';
    return clone;
}

// Toggle product list modal

    const searchResults = document.getElementById('search-results');
    const searchResultsBody = document.getElementById('search-results-body');
    const searchCount = document.getElementById('search-count');
    
    if (!query || query.length < 1) {
        searchResults.style.display = 'none';
        return;
    }
    
    const queryLower = query.toLowerCase();
    
    // Search in products: barcode prefix match OR name contains query
    const results = searchableProducts.filter(product => {
        // Priority 1: Barcode starts with query (exact prefix match)
        if (product.barcode && product.barcode.toLowerCase().startsWith(queryLower)) {
            return true;
        }
        // Priority 2: Product name or searchText contains query
        if (product.searchText && product.searchText.includes(queryLower)) {
            return true;
        }
        return false;
    })
    .sort((a, b) => {
        const aBarcode = a.barcode && a.barcode.toLowerCase().startsWith(queryLower);
        const bBarcode = b.barcode && b.barcode.toLowerCase().startsWith(queryLower);
        if (aBarcode && !bBarcode) return -1;
        if (!aBarcode && bBarcode) return 1;
        return a.nama.localeCompare(b.nama);
    })
    .slice(0, 10);
    
    if (results.length > 0) {
        searchCount.textContent = results.length;
        
        let html = '';
        results.forEach(product => {
            let stockBadge, barcodeDisplay, onclickAction;
            
            if (product.type === 'paket') {
                // For paket menu
                stockBadge = `<span class="badge bg-info">Paket</span>`;
                barcodeDisplay = `<small class="text-info"><i class="fas fa-box"></i> Paket Menu</small>`;
                onclickAction = `selectPaketFromSearch('${product.id}', '${product.nama.replace(/'/g, "\\'")}', ${product.harga}, ${JSON.stringify(product.paket_details).replace(/"/g, '&quot;')})`;
            } else {
                // For regular product
                stockBadge = product.stok > 0 ? 
                    `<span class="badge bg-success">${product.stok}</span>` : 
                    `<span class="badge bg-danger">Habis</span>`;
                
                // Highlight matching part in barcode for prefix matches
                if (product.barcode) {
                    if (product.barcode.startsWith(query)) {
                        // Highlight the matching prefix
                        const matchedPart = product.barcode.substring(0, query.length);
                        const remainingPart = product.barcode.substring(query.length);
                        barcodeDisplay = `<code class="text-primary"><mark class="bg-warning text-dark">${matchedPart}</mark>${remainingPart}</code>`;
                    } else {
                        barcodeDisplay = `<code class="text-primary">${product.barcode}</code>`;
                    }
                } else {
                    barcodeDisplay = '<small class="text-muted">No barcode</small>';
                }
                
                onclickAction = `selectProductFromSearch(${product.id}, '${product.nama.replace(/'/g, "\\'")}', ${product.harga}, ${product.stok})`;
            }
            
            html += `
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom search-result-item" 
                     style="cursor: pointer;" 
                     onclick="${onclickAction}"
                     onmouseover="this.style.backgroundColor='#f8f9fa'" 
                     onmouseout="this.style.backgroundColor=''">
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark">${product.nama}</div>
                        <small class="text-muted">${barcodeDisplay} * Rp ${product.harga.toLocaleString('id-ID')}</small>
                    </div>
                    <div class="text-end">
                        ${stockBadge}
                        <button type="button" class="btn btn-sm btn-primary ms-2" onclick="event.stopPropagation(); ${onclickAction}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        });
        
        searchResultsBody.innerHTML = html;
        searchResults.style.display = 'block';
    } else {
        searchCount.textContent = '0';
        searchResultsBody.innerHTML = `
            <div class="text-center text-muted py-2">
                <i class="fas fa-search me-1"></i>
                Tidak ada produk yang cocok dengan "<strong>${query}</strong>"
            </div>
        `;
        searchResults.style.display = 'block';
    }
}

// Select product from search results
function selectProductFromSearch(productId, productName, price, stock) {
    const product = {
        id: productId,
        nama: productName,
        harga: price,
        stok: stock
    };
    
    try {
        addProductByBarcode(product);
        showNotification('Produk ditambahkan: ' + productName, 'success');
        
        // Clear search
        document.getElementById('barcode-scanner').value = '';
        document.getElementById('search-results').style.display = 'none';
        
        // Reset scan indicator to "Siap Scan" after product selection
        const scanIndicator = document.getElementById('scan-indicator');
        if (scanIndicator) {
            scanIndicator.textContent = 'Siap Scan';
            scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
        }
        
        // Focus back to barcode input
        setTimeout(() => {
            document.getElementById('barcode-scanner').focus();
        }, 100);
        
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Select paket menu from search results
function selectPaketFromSearch(paketId, paketName, price, paketDetails) {
    try {
        // Find first empty row or create new one
        const table = document.getElementById('detailTableJual');
        const tbody = table.querySelector('tbody');
        let targetRow = null;
        const rows = tbody.querySelectorAll('tr');
        
        // Look for empty row (no product selected)
        for (let row of rows) {
            const select = row.querySelector('.produk-select');
            if (!select || !select.value) {
                targetRow = row;
                break;
            }
        }
        
        // If no empty row found, create new one
        if (!targetRow) {
            targetRow = createNewRow();
            tbody.appendChild(targetRow);
        }
        
        // Fill the row with paket data
        const select = targetRow.querySelector('.produk-select');
        const qtyInput = targetRow.querySelector('.jumlah');
        const hargaInput = targetRow.querySelector('.harga');
        const diskonInput = targetRow.querySelector('.diskon');
        
        select.value = paketId;
        qtyInput.value = 1;
        hargaInput.value = formatCurrency(price);
        diskonInput.value = 0;
        
        // Update stock info for paket
        setPriceFromSelect(targetRow);
        
        // Recalculate
        recalcRow(targetRow);
        hitungTotal();
        
        // Highlight row
        highlightRow(targetRow);
        
        showNotification('Paket menu ditambahkan: ' + paketName, 'success');
        
        // Clear search
        document.getElementById('barcode-scanner').value = '';
        document.getElementById('search-results').style.display = 'none';
        
        // Reset scan indicator to "Siap Scan" after product selection
        const scanIndicator = document.getElementById('scan-indicator');
        if (scanIndicator) {
            scanIndicator.textContent = 'Siap Scan';
            scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
        }
        
        // Focus back to barcode input
        setTimeout(() => {
            document.getElementById('barcode-scanner').focus();
        }, 100);
        
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Enhanced barcode input handler with real-time search
function handleBarcodeInputEnhanced(value) {
    // Clear existing search timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // If value is empty, hide search results
    if (!value) {
        document.getElementById('search-results').style.display = 'none';
        return;
    }
    
    // Don't show search results during barcode scanning (when processing is active)
    if (isProcessing) {
        document.getElementById('search-results').style.display = 'none';
        return;
    }
    
    // Tampilkan hasil pencarian untuk semua jenis input (angka maupun huruf)
    searchTimeout = setTimeout(() => {
        if (!isProcessing) {
            performRealTimeSearch(value);
        }
    }, SEARCH_DELAY);
}

// Safety mechanism to reset processing state if stuck
function resetProcessingState() {
    const scanIndicator = document.getElementById('scan-indicator');
    if (isProcessing && scanIndicator && scanIndicator.textContent === 'Memproses...') {
        console.log('Resetting stuck processing state');
        isProcessing = false;
        scanIndicator.textContent = 'Siap Scan';
        scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
    }
}

// Check for stuck processing every 5 seconds
setInterval(resetProcessingState, 5000);

// Auto-focus system
function maintainFocus() {
    const barcodeInput = document.getElementById('barcode-scanner');
    if (document.activeElement !== barcodeInput) {
        barcodeInput.focus();
    }
}

// Automatic barcode detection
function handleBarcodeInput(char) {
    // Hide search results immediately when rapid input is detected (barcode scanning)
    document.getElementById('search-results').style.display = 'none';
    
    // Add character to buffer
    barcodeBuffer += char;
    
    // Clear existing timeout
    if (barcodeTimeout) {
        clearTimeout(barcodeTimeout);
    }
    
    // Set new timeout - if no new characters come within BARCODE_TIMEOUT, process the barcode
    barcodeTimeout = setTimeout(() => {
        if (barcodeBuffer.length >= MIN_BARCODE_LENGTH && !isProcessing) {
            processAutomaticBarcode(barcodeBuffer.trim());
        }
        barcodeBuffer = ''; // Clear buffer
    }, BARCODE_TIMEOUT);
}

// Process barcode automatically - Enhanced version
function processAutomaticBarcode(barcode) {
    if (isProcessing) return;
    
    isProcessing = true;
    // Bersihkan barcode dari semua whitespace dan karakter non-printable
    barcode = barcode.replace(/[\s\r\n\t]/g, '').trim();
    console.log('Auto-processing barcode:', JSON.stringify(barcode), 'length:', barcode.length);
    
    const barcodeInput = document.getElementById('barcode-scanner');
    const scanIndicator = document.getElementById('scan-indicator');
    const searchResults = document.getElementById('search-results');
    
    // Hide search results when processing exact barcode
    searchResults.style.display = 'none';
    
    // Clear input immediately
    barcodeInput.value = '';
    
    // Update UI to show processing
    scanIndicator.textContent = 'Memproses...';
    scanIndicator.parentElement.className = 'input-group-text bg-warning text-dark';
    
    try {
        // Cari produk: coba exact match dulu, lalu fallback ke searchableProducts
        let product = productData[barcode];
        
        // Fallback: cari di searchableProducts jika tidak ketemu di productData
        if (!product) {
            const found = searchableProducts.find(p => p.barcode === barcode || p.barcode === barcode.trim());
            if (found) {
                product = { id: found.id, nama: found.nama, harga: found.harga, stok: found.stok, barcode: found.barcode };
            }
        }
        
        if (product) {
            console.log('Product found:', product);
            
            if (product.stok <= 0) {
                throw new Error('Produk ' + product.nama + ' stok habis!');
            }
            
            addProductByBarcode(product);
            
            scanIndicator.textContent = '✓ ' + product.nama;
            scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
            playBeep(true);
            showNotification('Produk ditambahkan: ' + product.nama, 'success');
            
        } else {
            console.log('Product not found for barcode:', barcode, '| productData keys:', Object.keys(productData));
            
            scanIndicator.textContent = 'Tidak ditemukan';
            scanIndicator.parentElement.className = 'input-group-text bg-danger text-white';
            showNotification('Barcode ' + barcode + ' tidak ditemukan', 'error');
            playBeep(false);
        }
    } catch (error) {
        console.error('Error processing barcode:', error);
        scanIndicator.textContent = 'Error';
        scanIndicator.parentElement.className = 'input-group-text bg-danger text-white';
        showNotification(error.message || 'Terjadi kesalahan', 'error');
        playBeep(false);
    }
    
    // Reset status setelah 2 detik
    setTimeout(() => {
        scanIndicator.textContent = 'Siap Scan';
        scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
        isProcessing = false;
        barcodeInput.focus();
    }, 2000);
}

// Show notification
function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Manual reset function - Enhanced
function resetScannerState() {
    console.log('Manually resetting scanner state');
    isProcessing = false;
    barcodeBuffer = '';
    
    if (barcodeTimeout) {
        clearTimeout(barcodeTimeout);
        barcodeTimeout = null;
    }
    
    if (searchTimeout) {
        clearTimeout(searchTimeout);
        searchTimeout = null;
    }
    
    const scanIndicator = document.getElementById('scan-indicator');
    const barcodeInput = document.getElementById('barcode-scanner');
    const searchResults = document.getElementById('search-results');
    
    if (scanIndicator) {
        scanIndicator.textContent = 'Siap Scan';
        scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
    }
    
    if (barcodeInput) {
        barcodeInput.value = '';
        barcodeInput.focus();
    }
    
    if (searchResults) {
        searchResults.style.display = 'none';
    }
    
    showNotification('Scanner direset - siap untuk scan', 'success');
}

// Legacy functions (kept for compatibility)
function searchBarcode() {
    const barcodeInput = document.getElementById('barcode-scanner');
    const barcode = barcodeInput.value.trim();
    
    if (!barcode) {
        showNotification('Masukkan barcode', 'error');
        return;
    }
    
    processAutomaticBarcode(barcode);
}

function processBarcode(barcode) {
    processAutomaticBarcode(barcode);
}

function addProductByBarcode(product) {
    console.log('Adding product to cart:', product);
    
    const table = document.getElementById('detailTableJual');
    const tbody = table.querySelector('tbody');
    
    // Check if product already exists in table
    const existingRow = findExistingProductRow(product.id);
    
    if (existingRow) {
        console.log('Incrementing existing product quantity');
        // Increment quantity
        const qtyInput = existingRow.querySelector('.jumlah');
        const currentQty = parseFloat(qtyInput.value) || 0;
        const newQty = currentQty + 1;
        
        // Check stock
        if (newQty > product.stok) {
            throw new Error('Stok tidak cukup! Stok tersedia: ' + product.stok);
        }
        
        qtyInput.value = Math.round(newQty);
        recalcRow(existingRow);
        hitungTotal();
        
        // Highlight row
        highlightRow(existingRow);
    } else {
        console.log('Adding new product to table');
        // Find first empty row or create new one
        let targetRow = null;
        const rows = tbody.querySelectorAll('tr');
        
        // Look for empty row (no product selected)
        for (let row of rows) {
            const select = row.querySelector('.produk-select');
            if (!select || !select.value) {
                console.log('Found empty row to use');
                targetRow = row;
                break;
            }
        }
        
        // If no empty row found, create new one
        if (!targetRow) {
            console.log('Creating new row');
            targetRow = createNewRow();
            tbody.appendChild(targetRow);
        }
        
        // Fill the row with product data
        const select = targetRow.querySelector('.produk-select');
        const qtyInput = targetRow.querySelector('.jumlah');
        const diskonInput = targetRow.querySelector('.diskon');
        
        console.log('Setting product data in row:', {
            productId: product.id,
            name: product.nama,
            price: product.harga
        });
        
        // Set nilai select dan trigger change agar setPriceFromSelect berjalan
        select.value = product.id;
        qtyInput.value = 1;
        diskonInput.value = 0;
        
        // Dispatch change event — ini akan trigger table change listener
        // yang memanggil setPriceFromSelect dan set data-raw dengan benar
        select.dispatchEvent(new Event('change', { bubbles: true }));
        
        // Recalculate dan highlight
        recalcRow(targetRow);
        hitungTotal();
        highlightRow(targetRow);
    }
}

// Create new empty row
function createNewRow() {
    const table = document.getElementById('detailTableJual');
    const firstRow = table.querySelector('tbody tr');
    const clone = firstRow.cloneNode(true);
    
    // Reset all inputs
    clone.querySelectorAll('input').forEach(inp => {
        if (inp.classList.contains('jumlah')) inp.value = 1;
        else if (inp.classList.contains('harga')) inp.value = 0;
        else if (inp.classList.contains('diskon')) inp.value = 0;
        else if (inp.classList.contains('subtotal')) inp.value = 'Rp 0';
    });
    
    // Reset select
    clone.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
    
    // Reset stock info
    const stockInfo = clone.querySelector('.stok-info');
    if (stockInfo) {
        stockInfo.textContent = '';
    }
    
    return clone;
}

function highlightRow(row) {
    row.style.backgroundColor = '#d4edda';
    setTimeout(() => {
        row.style.backgroundColor = '';
        row.style.transition = 'background-color 0.5s ease';
    }, 500);
}

function updateBarcodeStatus(message, type) {
    // Legacy function - now handled by automatic system
    console.log('Status:', message, type);
}

// Toggle product list modal
function playBeep(success) {
    // Professional beep sound like in supermarkets
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        if (success) {
            // Success beep: single high-pitched beep (like successful scan)
            oscillator.frequency.value = 1200;
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.15);
        } else {
            // Error beep: double low-pitched beep (like failed scan)
            oscillator.frequency.value = 400;
            oscillator.type = 'sine';
            
            // First beep
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
            
            // Second beep (slightly delayed)
            setTimeout(() => {
                const oscillator2 = audioContext.createOscillator();
                const gainNode2 = audioContext.createGain();
                oscillator2.connect(gainNode2);
                gainNode2.connect(audioContext.destination);
                oscillator2.frequency.value = 350;
                oscillator2.type = 'sine';
                gainNode2.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode2.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
                oscillator2.start(audioContext.currentTime);
                oscillator2.stop(audioContext.currentTime + 0.1);
            }, 150);
        }
    } catch (e) {
        // Audio not supported, ignore
        console.log('Audio not supported:', e);
    }
}

// Global variables for barcode scanner
let globalBarcodeInput = null;
let globalTable = null;

document.addEventListener('DOMContentLoaded', function() {
    const table   = document.getElementById('detailTableJual');
    const barcodeInput = document.getElementById('barcode-scanner');
    
    // Set global variables
    globalBarcodeInput = barcodeInput;
    globalTable = table;
    
    // Verify elements exist
    if (!barcodeInput) {
        console.error('❌ CRITICAL: barcode-scanner input not found!');
        return;
    }
    if (!table) {
        console.error('❌ CRITICAL: detailTableJual table not found!');
        return;
    }
    
    console.log('✅ Elements found:', {
        barcodeInput: !!barcodeInput,
        table: !!table
    });

    // ═════════════════════════════════════════════════════════════
    // PROFESSIONAL BARCODE SCANNER SYSTEM
    // ═════════════════════════════════════════════════════════════
    // Fitur:
    // ✓ Deteksi otomatis barcode dari scanner fisik
    // ✓ Pencarian real-time saat mengetik manual
    // ✓ Notifikasi visual dan audio seperti di supermarket
    // ✓ Validasi stok otomatis
    // ✓ Increment quantity jika produk sudah ada di keranjang
    // ✓ Fokus otomatis ke input scanner
    // ✓ Keyboard shortcut (F2, Escape)
    // ═════════════════════════════════════════════════════════════
    
    let scanBuffer   = '';   // accumulates chars from scanner
    let scanTimer    = null; // fires when scanner stops sending chars
    let searchTimer  = null; // debounce for manual search
    const SCAN_DONE_MS  = 80;  // if no new char in 80ms → barcode complete
    const SEARCH_DELAY_MS = 200; // debounce for live search

    // Focus barcode input on load
    console.log('Focusing barcode input...');
    barcodeInput.focus();
    
    // Initialize cart counter
    console.log('Initializing cart counter...');
    updateCartCounter();

    // ── 1. keydown: capture every character the scanner sends ────
    console.log('✅ Attaching keydown event listener...');
    barcodeInput.addEventListener('keydown', function(e) {
        console.log('Keydown event:', e.key, 'Current value:', barcodeInput.value);
        
        if (e.key === 'Enter') {
            e.preventDefault();
            console.log('Enter pressed! Processing barcode...');
            // Scanner finished — process whatever is in the field
            const val = barcodeInput.value.trim();
            console.log('Value to process:', val);
            if (scanTimer) { clearTimeout(scanTimer); scanTimer = null; }
            if (val) {
                processBarcodeValue(val);
            } else {
                console.log('Empty value, skipping...');
            }
            return;
        }
        
        // Handle Escape key
        if (e.key === 'Escape') { 
            barcodeInput.value = ''; 
            hideSearch(); 
        }
        
        // Handle Arrow Down
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const first = document.querySelector('.search-result-item');
            if (first) {
                first.classList.add('selected');
                first.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
        
        // Handle Arrow Up
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            const items = document.querySelectorAll('.search-result-item');
            if (items.length > 0) {
                items[items.length - 1].classList.add('selected');
                items[items.length - 1].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    });

    // ── 2. input: fired after value changes (typing OR scanner) ──
    console.log('✅ Attaching input event listener...');
    barcodeInput.addEventListener('input', function() {
        const val = barcodeInput.value.trim();
        console.log('Input event fired! Value:', val, 'Length:', val.length);

        // Clear search dropdown if empty
        if (!val) { 
            console.log('Empty value, hiding search');
            hideSearch(); 
            return; 
        }

        // Cancel previous timers
        if (scanTimer)   { clearTimeout(scanTimer);  scanTimer  = null; }
        if (searchTimer) { clearTimeout(searchTimer); searchTimer = null; }

        // If value looks like a complete barcode (all digits, 8+ chars)
        // wait a tiny bit for Enter to arrive; if it doesn't, process anyway
        if (/^\d{8,}$/.test(val)) {
            console.log('Looks like barcode (8+ digits), waiting for Enter or timeout...');
            hideSearch();
            scanTimer = setTimeout(() => {
                console.log('Scan timeout reached, processing barcode...');
                processBarcodeValue(val);
            }, SCAN_DONE_MS);
            return;
        }

        // Otherwise treat as manual search (letters or short digits)
        console.log('Manual typing detected, showing search results...');
        searchTimer = setTimeout(() => showSearchResults(val), SEARCH_DELAY_MS);
    });

    // ── 3. processBarcodeValue: the core lookup ───────────────────
    function processBarcodeValue(barcode) {
        console.log('=== processBarcodeValue CALLED ===');
        console.log('Raw barcode input:', JSON.stringify(barcode));
        
        barcode = barcode.replace(/\s/g, '');
        console.log('Cleaned barcode:', JSON.stringify(barcode));
        console.log('Barcode length:', barcode.length);
        
        barcodeInput.value = '';   // clear field immediately
        barcodeInput.focus();

        const indicator = document.getElementById('scan-indicator');

        // Look up in productData (keyed by barcode string)
        console.log('Looking up in productData...');
        console.log('Available barcodes in productData:', Object.keys(productData));
        let product = productData[barcode];
        console.log('Direct lookup result:', product);
        
        // Fallback: cari di searchableProducts jika tidak ketemu di productData
        if (!product) {
            console.log('Not found in productData, trying searchableProducts...');
            const found = searchableProducts.find(p => p.barcode === barcode || p.barcode === barcode.trim());
            console.log('searchableProducts lookup result:', found);
            if (found) {
                product = { id: found.id, nama: found.nama, harga: found.harga, stok: found.stok, barcode: found.barcode };
                console.log('Product found in searchableProducts:', product);
            }
        }

        if (!product) {
            // ── NOT FOUND ──
            console.log('❌ PRODUCT NOT FOUND');
            setIndicator(indicator, 'Produk tidak ditemukan', 'danger');
            showToast('❌ Produk dengan barcode ' + barcode + ' tidak ditemukan', 'danger');
            playBeep(false);
            setTimeout(() => setIndicator(indicator, 'Siap Scan', 'success'), 2000);
            return;
        }

        console.log('✅ PRODUCT FOUND:', product);

        if (product.stok <= 0) {
            console.log('⚠️ STOCK EMPTY');
            setIndicator(indicator, 'Stok habis!', 'warning');
            showToast('⚠️ ' + product.nama + ' — stok habis', 'warning');
            playBeep(false);
            setTimeout(() => setIndicator(indicator, 'Siap Scan', 'success'), 2000);
            return;
        }

        // ── FOUND — add to table ──
        console.log('Adding product to table...');
        addOrIncrementProduct(product);
        
        // Count total items in cart
        const tbody = table.querySelector('tbody');
        let totalItems = 0;
        tbody.querySelectorAll('tr').forEach(row => {
            const sel = row.querySelector('.produk-select');
            if (sel && sel.value) {
                const qty = parseFloat(row.querySelector('.jumlah').value) || 0;
                totalItems += qty;
            }
        });
        
        setIndicator(indicator, '✓ ' + product.nama, 'success');
        showToast(`✅ ${product.nama} ditambahkan | Total: ${totalItems} item`, 'success');
        playBeep(true);
        setTimeout(() => setIndicator(indicator, 'Siap Scan', 'success'), 1500);
    }

    // ── 4. addOrIncrementProduct ──────────────────────────────────
    function addOrIncrementProduct(product) {
        const tbody = table.querySelector('tbody');

        // Check if product already in table → increment qty
        for (const row of tbody.querySelectorAll('tr')) {
            const sel = row.querySelector('.produk-select');
            if (sel && String(sel.value) === String(product.id)) {
                const qtyInput = row.querySelector('.jumlah');
                const currentQty = parseFloat(qtyInput.value) || 0;
                const newQty = currentQty + 1;
                if (newQty > product.stok) {
                    showToast('⚠️ Stok tidak cukup! Tersedia: ' + product.stok + ' | Di keranjang: ' + currentQty, 'warning');
                    return;
                }
                qtyInput.value = newQty;
                recalcRow(row);
                hitungTotal();
                updateCartCounter();
                flashRow(row, '#d4edda');
                
                // Show quantity update notification
                showToast(`✅ ${product.nama} (${newQty}x) - Rp ${(product.harga * newQty).toLocaleString('id-ID')}`, 'success');
                return;
            }
        }

        // Find first empty row or create new one
        let targetRow = null;
        for (const row of tbody.querySelectorAll('tr')) {
            const sel = row.querySelector('.produk-select');
            if (!sel || !sel.value) { targetRow = row; break; }
        }
        if (!targetRow) {
            targetRow = createNewRow();
            tbody.appendChild(targetRow);
        }

        // Fill the row
        const sel      = targetRow.querySelector('.produk-select');
        const qtyInput = targetRow.querySelector('.jumlah');
        const diskon   = targetRow.querySelector('.diskon');

        sel.value    = product.id;
        qtyInput.value = 1;
        diskon.value = 0;

        // Trigger change so the inline onchange handler sets harga + data-raw
        sel.dispatchEvent(new Event('change', { bubbles: true }));

        recalcRow(targetRow);
        hitungTotal();
        updateCartCounter();
        flashRow(targetRow, '#d4edda');
    }

    // ── 5. Live search (manual typing) ───────────────────────────
    function showSearchResults(query) {
        const box   = document.getElementById('search-results');
        const body  = document.getElementById('search-results-body');
        const count = document.getElementById('search-count');
        if (!box || !body) return;

        const q = query.toLowerCase();
        const results = searchableProducts.filter(p =>
            (p.barcode && p.barcode.toLowerCase().startsWith(q)) ||
            (p.searchText && p.searchText.includes(q))
        ).sort((a, b) => {
            const ab = a.barcode && a.barcode.toLowerCase().startsWith(q);
            const bb = b.barcode && b.barcode.toLowerCase().startsWith(q);
            return (ab === bb) ? a.nama.localeCompare(b.nama) : (ab ? -1 : 1);
        }).slice(0, 10);

        if (count) count.textContent = results.length;

        if (results.length === 0) {
            body.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-search fa-3x mb-3 opacity-50"></i>
                    <p class="mb-0">Tidak ada produk yang cocok dengan "<strong>${query}</strong>"</p>
                    <small>Coba kata kunci lain atau scan barcode produk</small>
                </div>
            `;
        } else {
            body.innerHTML = results.map(p => {
                const stokBadge = p.stok > 0
                    ? `<span class="badge bg-success"><i class="fas fa-box me-1"></i>${p.stok}</span>`
                    : `<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Habis</span>`;
                
                // Highlight matching barcode
                let barcodeDisplay = '';
                if (p.barcode) {
                    if (p.barcode.toLowerCase().startsWith(q)) {
                        const matchedPart = p.barcode.substring(0, q.length);
                        const remainingPart = p.barcode.substring(q.length);
                        barcodeDisplay = `<code class="text-primary"><mark class="bg-warning text-dark">${matchedPart}</mark>${remainingPart}</code>`;
                    } else {
                        barcodeDisplay = `<code class="text-primary">${p.barcode}</code>`;
                    }
                }
                
                const onclick = p.type === 'paket'
                    ? `selectPaketFromSearch('${p.id}','${p.nama.replace(/'/g,"\\'")}',${p.harga},${JSON.stringify(p.paket_details||[]).replace(/"/g,'&quot;')})`
                    : `selectProductFromSearch(${p.id},'${p.nama.replace(/'/g,"\\'")}',${p.harga},${p.stok})`;
                
                return `
                    <div class="d-flex justify-content-between align-items-center py-2 px-3 border-bottom search-result-item"
                         style="cursor:pointer"
                         onclick="${onclick}"
                         onmouseover="this.style.background='#e3f2fd'"
                         onmouseout="this.style.background=''">
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark mb-1">${p.nama}</div>
                            <div class="d-flex align-items-center gap-2">
                                ${barcodeDisplay ? barcodeDisplay + ' <span class="text-muted">•</span>' : ''}
                                <span class="text-success fw-bold">Rp ${p.harga.toLocaleString('id-ID')}</span>
                                ${p.type === 'paket' ? '<span class="badge bg-info ms-2"><i class="fas fa-box-open me-1"></i>Paket</span>' : ''}
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            ${stokBadge}
                            <button type="button" class="btn btn-sm btn-primary" onclick="event.stopPropagation();${onclick}">
                                <i class="fas fa-plus me-1"></i>Tambah
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }
        box.style.display = 'block';
    }

    function hideSearch() {
        const box = document.getElementById('search-results');
        if (box) box.style.display = 'none';
    }

    // Hide search when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#search-results') && e.target !== barcodeInput) hideSearch();
    });
    
    // Keyboard navigation in search results
    document.addEventListener('keydown', function(e) {
        const searchBox = document.getElementById('search-results');
        if (!searchBox || searchBox.style.display === 'none') return;
        
        const items = Array.from(document.querySelectorAll('.search-result-item'));
        if (items.length === 0) return;
        
        const selected = document.querySelector('.search-result-item.selected');
        let currentIndex = selected ? items.indexOf(selected) : -1;
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (currentIndex < items.length - 1) {
                if (selected) selected.classList.remove('selected');
                items[currentIndex + 1].classList.add('selected');
                items[currentIndex + 1].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (currentIndex > 0) {
                if (selected) selected.classList.remove('selected');
                items[currentIndex - 1].classList.add('selected');
                items[currentIndex - 1].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        } else if (e.key === 'Enter' && selected) {
            e.preventDefault();
            selected.click();
        }
    });

    // ── 6. Helpers ────────────────────────────────────────────────
    function setIndicator(el, text, type) {
        if (!el) return;
        el.textContent = text;
        const map = { success: 'bg-success text-white', danger: 'bg-danger text-white', warning: 'bg-warning text-dark' };
        el.parentElement.className = 'input-group-text ' + (map[type] || map.success);
    }

    function flashRow(row, color) {
        // Enhanced visual feedback with animation
        row.style.transition = '';
        row.style.backgroundColor = color;
        row.style.boxShadow = '0 0 20px rgba(40, 167, 69, 0.5)';
        row.style.transform = 'scale(1.02)';
        
        setTimeout(() => { 
            row.style.transition = 'all 0.6s ease'; 
            row.style.backgroundColor = ''; 
            row.style.boxShadow = '';
            row.style.transform = 'scale(1)';
        }, 600);
    }

    function showToast(msg, type) {
        const colors = { 
            success: { bg: '#28a745', icon: 'fa-check-circle' }, 
            danger: { bg: '#dc3545', icon: 'fa-times-circle' }, 
            warning: { bg: '#ffc107', icon: 'fa-exclamation-triangle' } 
        };
        const config = colors[type] || colors.success;
        const toast = document.createElement('div');
        toast.style.cssText = `position:fixed;top:20px;right:20px;z-index:9999;padding:16px 24px;border-radius:8px;color:${type==='warning'?'#000':'#fff'};background:${config.bg};font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.3);min-width:300px;display:flex;align-items:center;gap:12px;animation:slideIn 0.3s ease-out;`;
        toast.innerHTML = `<i class="fas ${config.icon}" style="font-size:20px;"></i><span style="flex:1;">${msg}</span>`;
        document.body.appendChild(toast);
        
        // Add slide-in animation
        const style = document.createElement('style');
        style.textContent = '@keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }';
        if (!document.querySelector('style[data-toast-animation]')) {
            style.setAttribute('data-toast-animation', 'true');
            document.head.appendChild(style);
        }
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-in';
            toast.style.transform = 'translateX(400px)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }
    
    function updateCartCounter() {
        const tbody = table.querySelector('tbody');
        let totalItems = 0;
        let totalProducts = 0;
        
        tbody.querySelectorAll('tr').forEach(row => {
            const sel = row.querySelector('.produk-select');
            if (sel && sel.value) {
                totalProducts++;
                const qty = parseFloat(row.querySelector('.jumlah').value) || 0;
                totalItems += qty;
            }
        });
        
        const cartCount = document.getElementById('cart-count');
        const cartCounter = document.getElementById('cart-counter');
        
        if (cartCount) {
            cartCount.textContent = totalItems;
            
            // Animate counter update
            if (totalItems > 0) {
                cartCounter.style.animation = 'pulse 0.3s ease';
                setTimeout(() => {
                    cartCounter.style.animation = '';
                }, 300);
            }
        }
    }

    // Keep focus on barcode input (non-aggressive)
    function ensureBarcodeInputFocus() {
        const ae = document.activeElement;
        if (!ae || ae === barcodeInput) return;
        if (ae.matches('input, select, textarea, button') ||
            ae.classList.contains('form-control') ||
            ae.classList.contains('form-select')) return;
        if (document.querySelector('.modal.show')) return;
        barcodeInput.focus();
    }
    setInterval(ensureBarcodeInputFocus, 3000);

    // F2 shortcut to focus barcode input
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F2') { e.preventDefault(); barcodeInput.focus(); barcodeInput.select(); }
    });

    // ─────────────────────────────────────────────────────────────
    // END BARCODE SCANNER SYSTEM
    // ─────────────────────────────────────────────────────────────
    
    console.log('✅ ✅ ✅ BARCODE SCANNER SYSTEM INITIALIZED SUCCESSFULLY! ✅ ✅ ✅');
    console.log('All event listeners attached. Ready to scan!');


    // Re-focus when clicking anywhere on the page (except inputs/buttons)
    document.addEventListener('click', function(e) {
        // Hide search results when clicking outside
        const searchResults = document.getElementById('search-results');
        const barcodeScanner = document.getElementById('barcode-scanner');
        
        if (!e.target.closest('#search-results') && e.target !== barcodeScanner) {
            searchResults.style.display = 'none';
        }
        
        // Don't interfere with form elements
        if (e.target.matches('input, button, select, textarea, option, .btn, .form-control, .form-select, .modal *, .dropdown-menu *')) {
            return;
        }
        
        // Don't interfere if clicking on dropdown options
        if (e.target.closest('select') || e.target.closest('.dropdown-menu')) {
            return;
        }
        
        // Don't interfere if clicking on table cells or form elements
        if (e.target.closest('table') || e.target.closest('.form-group')) {
            return;
        }
        
        setTimeout(() => {
            // Only focus if no form element is currently focused and user isn't interacting with form
            const activeElement = document.activeElement;
            if (!activeElement || 
                (!activeElement.matches('input, select, textarea, button') && 
                 !activeElement.classList.contains('form-control') &&
                 !activeElement.classList.contains('form-select'))) {
                barcodeInput.focus();
            }
        }, 50); // Increased delay to allow user interaction to complete
    });
    
    // Handle window focus/blur
    window.addEventListener('focus', function() {
        setTimeout(() => {
            // Only focus barcode input if no other form element is focused
            const activeElement = document.activeElement;
            if (!activeElement || 
                (!activeElement.matches('input, select, textarea, button') && 
                 !activeElement.classList.contains('form-control') &&
                 !activeElement.classList.contains('form-select'))) {
                barcodeInput.focus();
            }
        }, 200); // Increased delay to allow user interaction
    });
    
    // Listen to payment method changes
    document.getElementById('payment_method_jual').addEventListener('change', toggleSumberDana);
    
    // Listen to sumber dana changes and save to localStorage
    document.getElementById('sumber_dana_jual').addEventListener('change', function() {
        const paymentMethod = document.getElementById('payment_method_jual').value;
        if (paymentMethod === 'cash' || paymentMethod === 'transfer') {
            localStorage.setItem('recent_sumber_dana_' + paymentMethod, this.value);
        }
    });

    // addBtn handled via onclick="tambahBarisProduk()" directly on the button

    table.addEventListener('change', (e) => {
        if (e.target && e.target.classList.contains('produk-select')) {
            const tr = e.target.closest('tr');
            const hargaInput = tr.querySelector('.harga');

            if (!e.target.value) {
                hargaInput.removeAttribute('readonly');
                hargaInput.value = 0;
                hargaInput.setAttribute('readonly', 'readonly');
                hargaInput.removeAttribute('data-raw');
                recalcRow(tr);
                hitungTotal();
                return;
            }

            // Ambil harga dari data-price
            const selectedOption = e.target.options[e.target.selectedIndex];
            const harga = parseFloat(selectedOption.getAttribute('data-price')) || 0;

            // Set harga — store raw number in data-raw, display formatted
            hargaInput.removeAttribute('readonly');
            hargaInput.value = harga.toLocaleString('id-ID');
            hargaInput.setAttribute('readonly', 'readonly');
            hargaInput.setAttribute('data-raw', harga);

            // Recalculate row and total
            recalcRow(tr);
            hitungTotal();

            // Update stock info
            const stok = parseFloat(selectedOption.getAttribute('data-stok')) || 0;
            const stokInfo = tr.querySelector('.stok-info');
            if (stokInfo) {
                stokInfo.textContent = `Stok tersedia: ${stok.toLocaleString()}`;
                stokInfo.style.color = stok > 0 ? '#28a745' : '#dc3545';
            }
        }
    });
    
    // Initialize price for existing rows on page load
    table.querySelectorAll('tbody tr').forEach(tr => {
        const select = tr.querySelector('.produk-select');
        if (select && select.value) {
            setPriceFromSelect(tr);
        }
    });
    
    // SIMPLE jQuery event handler - PASTI berhasil
    // (moved outside DOMContentLoaded - handled by vanilla JS below)
    
    table.addEventListener('input', (e) => {
        if (e.target && (e.target.classList.contains('jumlah') || e.target.classList.contains('harga') || e.target.classList.contains('diskon'))) {
            const tr = e.target.closest('tr');
            
            console.log('Input changed:', e.target.className, 'value:', e.target.value);
            
            // Mark input as actively being used to prevent focus stealing
            e.target.setAttribute('data-user-focused', 'true');
            
            // Validate stock if qty changed
            if (e.target.classList.contains('jumlah')) {
                validateStock(tr);
            }
            
            recalcRow(tr); 
            hitungTotal();
        }
    });
    
    // Add focus and blur handlers for quantity inputs to prevent cursor jumping
    table.addEventListener('focus', (e) => {
        if (e.target && e.target.classList.contains('jumlah')) {
            // Mark quantity input as actively focused
            e.target.setAttribute('data-user-focused', 'true');
        }
        if (e.target && e.target.classList.contains('produk-select')) {
            // Stop barcode auto-focus when dropdown is focused
            e.target.setAttribute('data-dropdown-focused', 'true');
        }
    }, true);
    
    table.addEventListener('blur', (e) => {
        if (e.target && e.target.classList.contains('jumlah')) {
            // Remove focus flag after a delay to allow for recalculation
            setTimeout(() => {
                e.target.removeAttribute('data-user-focused');
            }, 500);
        }
        if (e.target && e.target.classList.contains('produk-select')) {
            // Remove dropdown focus flag
            e.target.removeAttribute('data-dropdown-focused');
            
            // Resume barcode focus after a short delay
            setTimeout(() => {
                if (!document.querySelector('select:focus, input:focus')) {
                    barcodeInput.focus();
                }
            }, 200);
        }
    }, true);
    
    // Listen to additional cost changes - Updated for dropdown
    const ongkirEl = document.getElementById('biaya_ongkir');
    const ppnEl = document.getElementById('ppn_persen');

    if (ongkirEl) {
        ongkirEl.addEventListener('change', function() { hitungTotal(); });
        ongkirEl.addEventListener('input',  function() { hitungTotal(); });
        ongkirEl.addEventListener('click',  function() { setTimeout(hitungTotal, 50); });
    }

    if (ppnEl) {
        ppnEl.addEventListener('change', function() { hitungTotal(); });
        ppnEl.addEventListener('input',  function() { hitungTotal(); });
    }
    // removeRow handled via onclick="hapusBarisProduk(this)" directly on the button

    // Init first row
    setPriceFromSelect(table.querySelector('tbody tr'));
    recalcRow(table.querySelector('tbody tr'));
    hitungTotal();
    
    // Validate before submit
    document.getElementById('form-penjualan').addEventListener('submit', function(e) {
        let hasError = false;
        table.querySelectorAll('tbody tr').forEach(tr => {
            const sel = tr.querySelector('.produk-select');
            if (sel.value) {
                if (!validateStock(tr)) {
                    hasError = true;
                }
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('Mohon perbaiki jumlah produk yang melebihi stok tersedia!');
            return false;
        }
    });
});
</script>
@endsection

