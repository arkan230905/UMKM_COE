@extends('layouts.app')

@section('title', 'Tambah Penjualan')

@section('content')

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
    padding: 8px !important;
    margin: 2px 0;
}

.search-result-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-result-item.selected {
    background-color: #e3f2fd !important;
    border-left: 3px solid #2196f3;
}

/* Barcode highlight styling */
mark.bg-warning {
    background-color: #fff3cd !important;
    color: #856404 !important;
    font-weight: bold;
    padding: 1px 2px;
    border-radius: 2px;
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
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
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
                                   placeholder="Ketik atau scan barcode..." 
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
                            Pencarian berdasarkan awalan barcode - ketik angka untuk mencari produk yang barcodenya diawali dengan angka tersebut
                        </small>
                        
                        <!-- Real-time search results -->
                        <div id="search-results" class="mt-2" style="display: none;">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white py-1">
                                    <small><i class="fas fa-search me-1"></i>Hasil Pencarian (<span id="search-count">0</span> produk)</small>
                                </div>
                                <div class="card-body p-2" id="search-results-body" style="max-height: 200px; overflow-y: auto;">
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
                const subtotalVal = row.querySelector('.subtotal').value;
                tableData.push({
                    produk_id: produkSelect.value,
                    jumlah: row.querySelector('.jumlah').value,
                    harga_satuan: parseFloat(row.querySelector('.harga').value) || 0,
                    diskon_persen: row.querySelector('.diskon').value,
                    subtotal: parseFloat(subtotalVal) || parseCurrency(subtotalVal)
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
    '{{ $p->barcode ?? '' }}': {
        id: {{ $p->id }},
        nama: '{{ addslashes($p->nama_produk ?? $p->nama) }}',
        harga: {{ round($p->harga_jual ?? 0) }},
        stok: {{ $p->stok ?? 0 }},
        barcode: '{{ $p->barcode ?? '' }}'
    },
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
        barcode: '{{ $p->barcode ?? '' }}',
        type: 'produk',
        searchText: '{{ strtolower(addslashes($p->nama_produk ?? $p->nama)) }} {{ $p->barcode ?? '' }}'.toLowerCase()
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
console.log('Product Data loaded:', Object.keys(productData).length, 'products');
console.log('Searchable Products loaded:', searchableProducts.length, 'products');

// Verify searchableProducts has prices
if (searchableProducts.length > 0) {
    console.log('First product in searchableProducts:', searchableProducts[0]);
    console.log('First product harga:', searchableProducts[0].harga);
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
    
    // Set harga
    hargaInput.value = harga;
    
    console.log('Input value after setting:', hargaInput.value);
    
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
    const p = parseFloat(hargaEl.value) || 0;
    const dPct = Math.min(Math.max(parseFloat(tr.querySelector('.diskon').value) || 0, 0), 100);
    const sub = q * p;
    const dNom = sub * (dPct/100.0);
    const line = Math.max(sub - dNom, 0);
    // Format as Indonesian number without Rp prefix (e.g., "25.000")
    tr.querySelector('.subtotal').value = Math.round(line).toLocaleString('id-ID');
    // Store raw value for calculations
    tr.querySelector('.subtotal').setAttribute('data-raw', line);
    // Call hitungTotal to update totals
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
    
    // Set price directly as number
    const hargaInput = tr.querySelector('.harga');
    hargaInput.value = price;
    
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
let barcodeBuffer = '';
let barcodeTimeout = null;
let isProcessing = false;
let searchTimeout = null;
const BARCODE_TIMEOUT = 50; // Reduced to 50ms for faster response
const MIN_BARCODE_LENGTH = 1; // Reduced to 1 for immediate search
const SEARCH_DELAY = 150; // Delay for real-time search to avoid too many requests

// Real-time product search functionality
function performRealTimeSearch(query) {
    const searchResults = document.getElementById('search-results');
    const searchResultsBody = document.getElementById('search-results-body');
    const searchCount = document.getElementById('search-count');
    
    if (!query || query.length < 1) {
        searchResults.style.display = 'none';
        return;
    }
    
    console.log('Searching for products with barcode starting with:', query);
    
    // Search in products with priority for barcode prefix match
    const results = searchableProducts.filter(product => {
        // Priority 1: Barcode starts with query (exact prefix match)
        if (product.barcode && product.barcode.startsWith(query)) {
            console.log('Found barcode prefix match:', product.barcode, 'for product:', product.nama);
            return true;
        }
        // Priority 2: Product name contains query (fallback for name search)
        if (product.searchText.includes(query.toLowerCase())) {
            console.log('Found name match:', product.nama, 'for query:', query);
            return true;
        }
        return false;
    })
    .sort((a, b) => {
        // Sort by priority: barcode prefix matches first
        const aStartsWithBarcode = a.barcode && a.barcode.startsWith(query);
        const bStartsWithBarcode = b.barcode && b.barcode.startsWith(query);
        
        if (aStartsWithBarcode && !bStartsWithBarcode) return -1;
        if (!aStartsWithBarcode && bStartsWithBarcode) return 1;
        
        // If both or neither start with barcode, sort by name
        return a.nama.localeCompare(b.nama);
    })
    .slice(0, 10); // Limit to 10 results for performance
    
    console.log('Search results count:', results.length);
    
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
                Tidak ada produk dengan barcode yang diawali "${query}"
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
    
    // Only show search results for numeric input (barcode search) and manual typing
    if (/^\d+$/.test(value)) {
        // Set timeout for real-time search for numeric input
        searchTimeout = setTimeout(() => {
            // Double check we're not processing a barcode scan
            if (!isProcessing) {
                performRealTimeSearch(value);
            }
        }, SEARCH_DELAY);
    } else {
        // Hide search results for non-numeric input
        document.getElementById('search-results').style.display = 'none';
    }
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
    console.log('Auto-processing barcode:', barcode);
    
    const barcodeInput = document.getElementById('barcode-scanner');
    const scanIndicator = document.getElementById('scan-indicator');
    const searchResults = document.getElementById('search-results');
    
    // Hide search results when processing exact barcode
    searchResults.style.display = 'none';
    
    // Clear input immediately for silent processing
    barcodeInput.value = '';
    
    // Update UI to show processing
    scanIndicator.textContent = 'Memproses...';
    scanIndicator.parentElement.className = 'input-group-text bg-warning text-dark';
    
    try {
        const product = productData[barcode];
        
        if (product) {
            console.log('Product found:', product);
            
            // Validate stock before adding
            if (product.stok <= 0) {
                throw new Error('Produk ' + product.nama + ' stok habis!');
            }
            
            // Product found - add to table silently
            addProductByBarcode(product);
            
            // Success feedback
            scanIndicator.textContent = 'Produk ditambahkan';
            scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
            
            // Play success sound
            playBeep(true);
            
            // Show success notification
            showNotification('Produk ditambahkan: ' + product.nama, 'success');
            
        } else {
            console.log('Product not found for barcode:', barcode);
            
            // Product not found - show error without search results
            scanIndicator.textContent = 'Produk tidak ditemukan';
            scanIndicator.parentElement.className = 'input-group-text bg-danger text-white';
            
            // Show error notification
            showNotification('Produk dengan barcode ' + barcode + ' tidak ditemukan', 'error');
            
            // Play error sound
            playBeep(false);
        }
    } catch (error) {
        console.error('Error processing barcode:', error);
        
        // Error feedback
        scanIndicator.textContent = 'Error';
        scanIndicator.parentElement.className = 'input-group-text bg-danger text-white';
        
        // Show specific error message
        showNotification(error.message || 'Terjadi kesalahan saat memproses barcode', 'error');
        
        // Play error sound
        playBeep(false);
    }
    
    // Reset status after 2 seconds
    setTimeout(() => {
        scanIndicator.textContent = 'Siap Scan';
        scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
        isProcessing = false;
        
        // Ensure focus is maintained
        maintainFocus();
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
        const hargaInput = targetRow.querySelector('.harga');
        const diskonInput = targetRow.querySelector('.diskon');
        
        console.log('Setting product data in row:', {
            productId: product.id,
            name: product.nama,
            price: product.harga
        });
        
        select.value = product.id;
        qtyInput.value = 1;
        hargaInput.value = product.harga;
        diskonInput.value = 0;
        
        // Update stock info
        setPriceFromSelect(targetRow);
        
        // Recalculate
        recalcRow(targetRow);
        hitungTotal();
        
        // Highlight row
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
    // Simple beep using Web Audio API
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = success ? 800 : 300;
        oscillator.type = 'sine';
        gainNode.gain.value = 0.1;
        
        oscillator.start();
        setTimeout(() => oscillator.stop(), success ? 100 : 200);
    } catch (e) {
        // Audio not supported, ignore
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('detailTableJual');
    const addBtn = document.getElementById('addRowJual');
    const barcodeInput = document.getElementById('barcode-scanner');
    
    // AUTOMATIC BARCODE SCANNING SYSTEM
    
    // Maintain focus on barcode input at all times
    function ensureBarcodeInputFocus() {
        // Don't steal focus if user is actively using dropdown or other form elements
        const activeElement = document.activeElement;
        
        if (activeElement && (
            activeElement.tagName === 'SELECT' ||
            activeElement.tagName === 'INPUT' ||
            activeElement.tagName === 'TEXTAREA' ||
            activeElement.classList.contains('form-control') ||
            activeElement.classList.contains('form-select') ||
            activeElement.hasAttribute('data-dropdown-focused') ||
            activeElement.hasAttribute('data-user-focused')
        )) {
            return; // Don't steal focus
        }
        
        // Don't steal focus if any dropdown is currently focused
        if (document.querySelector('select[data-dropdown-focused="true"]')) {
            return;
        }
        
        // Don't steal focus if any input is marked as user-focused
        if (document.querySelector('input[data-user-focused="true"]')) {
            return;
        }
        
        // Don't steal focus if modal is open
        if (document.querySelector('.modal.show')) {
            return;
        }
        
        // Don't steal focus if user is actively typing in any input field
        if (document.querySelector('input:focus, textarea:focus, select:focus')) {
            return;
        }
        
        // Only focus barcode input if no other form element is focused
        if (document.activeElement !== barcodeInput && 
            !document.activeElement.matches('input, select, textarea, button')) {
            barcodeInput.focus();
        }
    }
    
    // Set initial focus
    barcodeInput.focus();
    
    // Maintain focus every 2000ms (increased frequency to be less aggressive)
    setInterval(ensureBarcodeInputFocus, 2000);
    
    // Enhanced keyboard handling for better UX
    document.addEventListener('keydown', function(e) {
        // Skip if user is typing in other inputs (except barcode input)
        if (e.target.tagName === 'INPUT' && e.target.id !== 'barcode-scanner') {
            return;
        }
        
        // Skip if user is interacting with select dropdown
        if (e.target.tagName === 'SELECT') {
            return;
        }
        
        // Skip if modal is open
        if (document.querySelector('.modal.show')) {
            return;
        }
        
        // Skip if dropdown is open
        if (document.querySelector('select:focus')) {
            return;
        }
        
        // Skip special keys
        if (e.ctrlKey || e.altKey || e.metaKey) {
            return;
        }
        
        // Handle Escape key to clear search results
        if (e.key === 'Escape') {
            e.preventDefault();
            document.getElementById('search-results').style.display = 'none';
            barcodeInput.value = '';
            barcodeInput.focus();
            return;
        }
        
        // Handle Arrow keys for search result navigation
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            const searchResults = document.getElementById('search-results');
            if (searchResults.style.display !== 'none') {
                e.preventDefault();
                navigateSearchResults(e.key === 'ArrowDown' ? 1 : -1);
                return;
            }
        }
        
        // Skip navigation keys when dropdown is focused
        if (['ArrowLeft', 'ArrowRight', 'Tab'].includes(e.key)) {
            return;
        }
        
        // Handle Enter key (barcode scanners usually send Enter at the end)
        if (e.key === 'Enter') {
            e.preventDefault();
            const currentValue = barcodeInput.value.trim();
            if (currentValue && currentValue.length >= MIN_BARCODE_LENGTH) {
                // Check if search results are visible and select first result
                const searchResults = document.getElementById('search-results');
                if (searchResults.style.display !== 'none') {
                    const firstResult = searchResults.querySelector('.search-result-item');
                    if (firstResult) {
                        firstResult.click();
                        return;
                    }
                }
                processAutomaticBarcode(currentValue);
            }
            return;
        }
        
        // Handle printable characters
        if (e.key.length === 1) {
            // Don't interfere if user is typing in dropdown or other form elements
            if (e.target.tagName === 'SELECT' || e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }
            
            // Ensure barcode input is focused
            if (document.activeElement !== barcodeInput) {
                barcodeInput.focus();
            }
            
            // Let the character be typed naturally, then handle it
            setTimeout(() => {
                handleBarcodeInput(e.key);
            }, 1);
        }
    });

// Navigate search results with arrow keys
function navigateSearchResults(direction) {
    const results = document.querySelectorAll('.search-result-item');
    if (results.length === 0) return;
    
    let currentIndex = -1;
    results.forEach((result, index) => {
        if (result.classList.contains('selected')) {
            currentIndex = index;
            result.classList.remove('selected');
            result.style.backgroundColor = '';
        }
    });
    
    currentIndex += direction;
    if (currentIndex < 0) currentIndex = results.length - 1;
    if (currentIndex >= results.length) currentIndex = 0;
    
    const selectedResult = results[currentIndex];
    selectedResult.classList.add('selected');
    selectedResult.style.backgroundColor = '#e3f2fd';
    selectedResult.scrollIntoView({ block: 'nearest' });
}
    
    // Handle direct input to barcode field - Enhanced with real-time search
    barcodeInput.addEventListener('input', function(e) {
        const value = e.target.value.trim();
        const scanIndicator = document.getElementById('scan-indicator');
        
        // If input is cleared, reset buffer and hide search
        if (!value) {
            barcodeBuffer = '';
            if (barcodeTimeout) {
                clearTimeout(barcodeTimeout);
            }
            document.getElementById('search-results').style.display = 'none';
            
            // Reset scan indicator to "Siap Scan" when input is cleared
            if (scanIndicator) {
                scanIndicator.textContent = 'Siap Scan';
                scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
            }
            return;
        }
        
        // Handle rapid input (typical of barcode scanners)
        const currentTime = Date.now();
        const timeDiff = barcodeInput.lastInputTime ? (currentTime - barcodeInput.lastInputTime) : 1000;
        
        // If input is very rapid (< 50ms between characters), it's likely a barcode scanner
        if (timeDiff < 50) {
            // Rapid input detected - likely from scanner, hide search results and process silently
            document.getElementById('search-results').style.display = 'none';
            handleBarcodeInput(value.slice(-1)); // Get last character for buffer
            barcodeInput.lastInputTime = currentTime;
            return;
        }
        
        // If input is slower, treat as manual typing and show search results
        barcodeInput.lastInputTime = currentTime;
        
        // Only show search results for numeric input (barcode search) and manual typing
        // Don't show search results for text input
        if (/^\d+$/.test(value)) {
            // Numeric input - show search results for barcode search only for manual typing
            if (value.length >= 1 && value.length < 8 && !isProcessing) {
                // Handle real-time search for numeric input (barcode search) - only for manual input
                handleBarcodeInputEnhanced(value);
            } else if (value.length >= 8) {
                // Long numeric input - try to process as complete barcode
                document.getElementById('search-results').style.display = 'none';
                setTimeout(() => {
                    if (barcodeInput.value === value) {
                        processAutomaticBarcode(value);
                    }
                }, 100);
            }
        } else {
            // Non-numeric input - hide search results completely
            document.getElementById('search-results').style.display = 'none';
        }
    });
    
    // Prevent form submission on Enter in barcode input
    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });
    
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
    
    // END AUTOMATIC BARCODE SCANNING SYSTEM
    
    // Auto-focus barcode input when pressing F2
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F2') {
            e.preventDefault();
            barcodeInput.focus();
            barcodeInput.select();
        }
    });

    // toggleSumberDana handled globally above

    // Initial toggle
    toggleSumberDana();
    
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
            
            console.log('Product select changed!');
            console.log('Selected value:', e.target.value);
            
            if (!e.target.value) {
                hargaInput.value = 0;
                recalcRow(tr);
                hitungTotal();
                return;
            }
            
            // Ambil harga dari data-price
            const selectedOption = e.target.options[e.target.selectedIndex];
            const harga = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            
            console.log('Data-price attribute:', selectedOption.getAttribute('data-price'));
            console.log('Parsed harga:', harga);
            
            // Set harga
            hargaInput.value = harga;
            
            console.log('Input value after setting:', hargaInput.value);
            
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

