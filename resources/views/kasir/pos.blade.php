@extends('layouts.kasir')

@section('title', 'Point of Sale')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Point of Sale</h1>
            <p class="text-muted">Kasir: {{ $kasir['nama'] }} ({{ $kasir['jabatan'] }})</p>
        </div>
        <div class="text-end">
            <small class="text-muted">{{ $perusahaan['nama'] }}</small><br>
            <small class="text-muted">Kode: {{ $perusahaan['kode'] }}</small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('kasir.store') }}" method="POST" id="posForm">
        @csrf
        
        <div class="row">
            <!-- Product Selection -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-search"></i> Pilih Produk</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search & Barcode Scanner -->
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" id="searchProduct" class="form-control" placeholder="Cari produk (nama, kode, atau barcode)...">
                                <button type="button" class="btn btn-primary" id="scanBarcodeBtn" onclick="toggleBarcodeScanner()">
                                    <i class="fas fa-qrcode"></i> Scan Barcode
                                </button>
                            </div>
                        </div>

                        <!-- Barcode Scanner Modal -->
                        <div class="modal fade" id="barcodeScannerModal" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="fas fa-qrcode"></i> Scan Barcode Produk
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <div id="scanner-container">
                                            <video id="barcode-scanner" width="100%" height="300" style="border: 2px solid #007bff; border-radius: 8px;"></video>
                                            <div class="mt-3">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> 
                                                    Arahkan kamera ke barcode produk untuk scan otomatis
                                                </div>
                                                <div id="scan-result" class="alert alert-success" style="display: none;">
                                                    <i class="fas fa-check-circle"></i> 
                                                    <span id="scan-result-text"></span>
                                                </div>
                                                <div id="scan-error" class="alert alert-danger" style="display: none;">
                                                    <i class="fas fa-exclamation-triangle"></i> 
                                                    <span id="scan-error-text"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="scanner-loading" style="display: none;">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2">Mengaktifkan kamera...</p>
                                        </div>
                                        <div id="scanner-error" style="display: none;">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-camera"></i> 
                                                Tidak dapat mengakses kamera. Pastikan browser memiliki izin kamera.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        <button type="button" class="btn btn-primary" onclick="restartScanner()">
                                            <i class="fas fa-redo"></i> Scan Ulang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Product Grid -->
                        <div class="row" id="productGrid">
                            @foreach($produks as $produk)
                                <div class="col-md-4 mb-3 product-item" data-name="{{ strtolower($produk->nama_produk) }}" data-code="{{ strtolower($produk->kode_produk) }}" data-barcode="{{ strtolower($produk->barcode ?? '') }}">
                                    <div class="card product-card" onclick="addToCart({{ $produk->id }}, '{{ $produk->nama_produk }}', {{ $produk->harga_jual }}, {{ $produk->stok }})">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">{{ $produk->nama_produk }}</h6>
                                            <p class="card-text">
                                                <small class="text-muted">{{ $produk->kode_produk }}</small><br>
                                                <strong>Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</strong><br>
                                                <span class="badge bg-info">Stok: {{ $produk->stok }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart & Payment -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-shopping-cart"></i> Keranjang</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm" id="cartTable">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cartBody">
                                <!-- Cart items will be added here -->
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th colspan="3">TOTAL:</th>
                                    <th id="grandTotal">Rp 0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>

                        <hr>

                        <!-- Payment -->
                        <div class="mb-3">
                            <label class="form-label">Metode Bayar</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="cash">Tunai</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sumber Dana</label>
                            <select name="sumber_dana" id="sumber_dana" class="form-select" required>
                                @foreach($kasbank as $kb)
                                    <option value="{{ $kb->kode_akun }}">{{ $kb->nama_akun }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah Bayar</label>
                            <input type="number" name="bayar" id="bayar" class="form-control" min="0" step="100" required onchange="calculateChange()">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kembalian</label>
                            <input type="text" id="kembalian" class="form-control" readonly>
                        </div>

                        <button type="submit" class="btn btn-success w-100" id="processBtn" disabled>
                            <i class="fas fa-cash-register"></i> Proses Transaksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://unpkg.com/@zxing/library@latest"></script>

<script>
let cart = [];
let cartIndex = 0;
let barcodeScanner = null;
let scannerStream = null;
let scannerModal = null;
let lastScannedCode = '';
let lastScanTime = 0;

// Initialize modal
document.addEventListener('DOMContentLoaded', function() {
    scannerModal = new bootstrap.Modal(document.getElementById('barcodeScannerModal'));
    
    // Stop scanner when modal is closed
    document.getElementById('barcodeScannerModal').addEventListener('hidden.bs.modal', function() {
        stopBarcodeScanner();
    });
});

// Search functionality
document.getElementById('searchProduct').addEventListener('input', function() {
    const search = this.value.toLowerCase();
    const products = document.querySelectorAll('.product-item');
    
    products.forEach(product => {
        const name = product.dataset.name;
        const code = product.dataset.code;
        const barcode = product.dataset.barcode;
        
        if (name.includes(search) || code.includes(search) || barcode.includes(search)) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
});

// Barcode Scanner Functions
function toggleBarcodeScanner() {
    scannerModal.show();
    startBarcodeScanner();
}

async function startBarcodeScanner() {
    const video = document.getElementById('barcode-scanner');
    const loadingDiv = document.getElementById('scanner-loading');
    const errorDiv = document.getElementById('scanner-error');
    const containerDiv = document.getElementById('scanner-container');
    
    // Show loading
    containerDiv.style.display = 'none';
    loadingDiv.style.display = 'block';
    errorDiv.style.display = 'none';
    
    try {
        // Request camera access
        scannerStream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'environment' } // Use back camera on mobile
        });
        
        video.srcObject = scannerStream;
        video.play();
        
        // Hide loading, show scanner
        loadingDiv.style.display = 'none';
        containerDiv.style.display = 'block';
        
        // Initialize ZXing barcode reader
        const codeReader = new ZXing.BrowserMultiFormatReader();
        
        // Start continuous scanning
        codeReader.decodeFromVideoDevice(null, 'barcode-scanner', (result, err) => {
            if (result) {
                const barcode = result.text;
                const currentTime = Date.now();
                
                // Prevent duplicate scans within 2 seconds
                if (barcode !== lastScannedCode || (currentTime - lastScanTime) > 2000) {
                    lastScannedCode = barcode;
                    lastScanTime = currentTime;
                    handleBarcodeScanned(barcode);
                }
            }
            
            if (err && !(err instanceof ZXing.NotFoundException)) {
                console.error('Barcode scan error:', err);
            }
        });
        
        barcodeScanner = codeReader;
        
    } catch (error) {
        console.error('Camera access error:', error);
        loadingDiv.style.display = 'none';
        errorDiv.style.display = 'block';
    }
}

function stopBarcodeScanner() {
    if (barcodeScanner) {
        barcodeScanner.reset();
        barcodeScanner = null;
    }
    
    if (scannerStream) {
        scannerStream.getTracks().forEach(track => track.stop());
        scannerStream = null;
    }
    
    // Reset UI
    document.getElementById('scan-result').style.display = 'none';
    document.getElementById('scan-error').style.display = 'none';
}

function restartScanner() {
    stopBarcodeScanner();
    document.getElementById('scan-result').style.display = 'none';
    document.getElementById('scan-error').style.display = 'none';
    startBarcodeScanner();
}

function handleBarcodeScanned(barcode) {
    console.log('Barcode scanned:', barcode);
    
    // Find product by barcode
    const products = @json($produks);
    const product = products.find(p => p.barcode === barcode || p.kode_produk === barcode);
    
    if (product) {
        // Show success message
        document.getElementById('scan-result-text').textContent = 
            `Produk ditemukan: ${product.nama_produk} - Rp ${product.harga_jual.toLocaleString('id-ID')}`;
        document.getElementById('scan-result').style.display = 'block';
        document.getElementById('scan-error').style.display = 'none';
        
        // Add to cart
        addToCart(product.id, product.nama_produk, product.harga_jual, product.stok);
        
        // Play success sound (optional)
        playBeep();
        
        // Auto close modal after 1 second
        setTimeout(() => {
            scannerModal.hide();
        }, 1000);
        
    } else {
        // Show error message
        document.getElementById('scan-error-text').textContent = 
            `Produk dengan barcode "${barcode}" tidak ditemukan!`;
        document.getElementById('scan-error').style.display = 'block';
        document.getElementById('scan-result').style.display = 'none';
        
        // Play error sound (optional)
        playErrorBeep();
    }
}

function playBeep() {
    // Create a simple beep sound
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.frequency.value = 800;
    oscillator.type = 'sine';
    
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.1);
}

function playErrorBeep() {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.frequency.value = 400;
    oscillator.type = 'sine';
    
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.2);
}

// Keyboard shortcut for barcode scanner (F2 key)
document.addEventListener('keydown', function(e) {
    if (e.key === 'F2') {
        e.preventDefault();
        toggleBarcodeScanner();
    }
});

function addToCart(produkId, namaProduk, hargaJual, stok) {
    // Check if product already in cart
    const existingIndex = cart.findIndex(item => item.produk_id === produkId);
    
    if (existingIndex !== -1) {
        // Increase quantity
        if (cart[existingIndex].jumlah < stok) {
            cart[existingIndex].jumlah++;
            updateCartDisplay();
        } else {
            alert('Stok tidak mencukupi!');
        }
    } else {
        // Add new item
        cart.push({
            produk_id: produkId,
            nama_produk: namaProduk,
            jumlah: 1,
            harga_jual: hargaJual,
            stok: stok
        });
        updateCartDisplay();
    }
}

function updateCartDisplay() {
    const tbody = document.getElementById('cartBody');
    tbody.innerHTML = '';
    
    let total = 0;
    
    cart.forEach((item, index) => {
        const subtotal = item.jumlah * item.harga_jual;
        total += subtotal;
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="hidden" name="items[${index}][produk_id]" value="${item.produk_id}">
                <input type="hidden" name="items[${index}][jumlah]" value="${item.jumlah}">
                <input type="hidden" name="items[${index}][harga_jual]" value="${item.harga_jual}">
                <small>${item.nama_produk}</small>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" value="${item.jumlah}" min="1" max="${item.stok}" onchange="updateQuantity(${index}, this.value)">
            </td>
            <td><small>Rp ${item.harga_jual.toLocaleString('id-ID')}</small></td>
            <td><small>Rp ${subtotal.toLocaleString('id-ID')}</small></td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeFromCart(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
    
    document.getElementById('grandTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('processBtn').disabled = cart.length === 0;
    
    calculateChange();
}

function updateQuantity(index, newQuantity) {
    const qty = parseInt(newQuantity);
    if (qty > 0 && qty <= cart[index].stok) {
        cart[index].jumlah = qty;
        updateCartDisplay();
    }
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

function calculateChange() {
    const total = cart.reduce((sum, item) => sum + (item.jumlah * item.harga_jual), 0);
    const bayar = parseFloat(document.getElementById('bayar').value) || 0;
    const kembalian = bayar - total;
    
    document.getElementById('kembalian').value = kembalian >= 0 ? 'Rp ' + kembalian.toLocaleString('id-ID') : 'Kurang bayar';
}

// Form validation
document.getElementById('posForm').addEventListener('submit', function(e) {
    if (cart.length === 0) {
        e.preventDefault();
        alert('Keranjang masih kosong!');
        return;
    }
    
    const total = cart.reduce((sum, item) => sum + (item.jumlah * item.harga_jual), 0);
    const bayar = parseFloat(document.getElementById('bayar').value) || 0;
    
    if (bayar < total) {
        e.preventDefault();
        alert('Jumlah bayar tidak mencukupi!');
        return;
    }
});
</script>

<style>
.product-card {
    cursor: pointer;
    transition: all 0.3s;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

#cartTable {
    font-size: 0.9rem;
}
</style>
@endsection