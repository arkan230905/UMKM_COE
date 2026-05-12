<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Barcode - Semua Produk</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        
        .print-controls {
            background: #fff; padding: 20px; border-radius: 8px;
            margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .print-controls h2 { margin-bottom: 15px; color: #333; }
        .control-row { display: flex; gap: 20px; align-items: center; flex-wrap: wrap; margin-bottom: 15px; }
        .control-group { display: flex; flex-direction: column; gap: 5px; }
        .control-group label { font-size: 12px; color: #666; font-weight: 600; }
        .control-group input, .control-group select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        
        .labels-preview { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .labels-container { display: flex; flex-wrap: wrap; gap: 10px; }
        
        .barcode-label {
            width: 50mm; height: 30mm; background: #fff; border: 1px dashed #ccc;
            padding: 3mm; display: flex; flex-direction: column; align-items: center;
            justify-content: space-between; page-break-inside: avoid;
        }
        .barcode-label.size-small { width: 40mm; height: 25mm; padding: 2mm; }
        .barcode-label.size-large { width: 60mm; height: 40mm; padding: 4mm; }
        
        .label-product-name { font-size: 7px; font-weight: bold; text-align: center; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .label-barcode-svg { flex: 1; display: flex; align-items: center; justify-content: center; }
        .label-barcode-number { font-family: 'Courier New', monospace; font-size: 7px; letter-spacing: 1px; }
        .label-price { font-size: 9px; font-weight: bold; }
        
        @media print {
            body { background: white; padding: 0; margin: 0; }
            .print-controls { display: none !important; }
            .labels-preview { box-shadow: none; padding: 0; }
            .labels-preview h3 { display: none; }
            .barcode-label { border: none; margin: 1mm; }
            @page { size: A4; margin: 5mm; }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <h2>🏷️ Cetak Label Barcode - Semua Produk</h2>
        <div class="control-row">
            <div class="control-group">
                <label>Jumlah Label per Produk</label>
                <input type="number" id="labelCount" value="1" min="1" max="20" onchange="generateLabels()">
            </div>
            <div class="control-group">
                <label>Ukuran Label</label>
                <select id="labelSize" onchange="generateLabels()">
                    <option value="small">Kecil (40x25mm)</option>
                    <option value="medium" selected>Sedang (50x30mm)</option>
                    <option value="large">Besar (60x40mm)</option>
                </select>
            </div>
            <div class="control-group">
                <label>Tampilkan Harga</label>
                <select id="showPrice" onchange="generateLabels()">
                    <option value="yes" selected>Ya</option>
                    <option value="no">Tidak</option>
                </select>
            </div>
        </div>
        <div class="control-row">
            <button class="btn btn-primary" onclick="window.print()">🖨️ Cetak Semua Label</button>
            <button class="btn btn-secondary" onclick="window.history.back()">← Kembali</button>
        </div>
    </div>
    
    <div class="labels-preview">
        <h3>Preview Label ({{ count($produks) }} Produk)</h3>
        <div class="labels-container" id="labelsContainer"></div>
    </div>
    
    <script>
        const products = [
            @foreach($produks as $p)
            { name: "{{ addslashes($p->nama_produk) }}", barcode: "{{ $p->barcode }}", price: "Rp {{ number_format($p->harga_jual ?? 0, 0, ',', '.') }}" },
            @endforeach
        ];
        
        function generateLabels() {
            const count = parseInt(document.getElementById('labelCount').value) || 1;
            const size = document.getElementById('labelSize').value;
            const showPrice = document.getElementById('showPrice').value === 'yes';
            const container = document.getElementById('labelsContainer');
            container.innerHTML = '';
            
            let barcodeHeight = 22;
            if (size === 'small') barcodeHeight = 16;
            if (size === 'large') barcodeHeight = 30;
            
            let idx = 0;
            products.forEach((product, pIdx) => {
                if (!product.barcode) return;
                for (let i = 0; i < count; i++) {
                    const label = document.createElement('div');
                    label.className = `barcode-label size-${size}`;
                    label.innerHTML = `
                        <div class="label-product-name">${product.name}</div>
                        <div class="label-barcode-svg"><svg class="bc-${idx}"></svg></div>
                        <div class="label-barcode-number">${product.barcode}</div>
                        ${showPrice ? `<div class="label-price">${product.price}</div>` : ''}
                    `;
                    container.appendChild(label);
                    
                    try {
                        JsBarcode(`.bc-${idx}`, product.barcode, { format: "EAN13", width: 1.1, height: barcodeHeight, displayValue: false, margin: 0, background: "transparent" });
                    } catch (e) {
                        JsBarcode(`.bc-${idx}`, product.barcode, { format: "CODE128", width: 0.9, height: barcodeHeight, displayValue: false, margin: 0, background: "transparent" });
                    }
                    idx++;
                }
            });
        }
        
        document.addEventListener('DOMContentLoaded', generateLabels);
    </script>
</body>
</html>
