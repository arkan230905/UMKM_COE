<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Barcode - {{ $produk->nama_produk }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .print-controls {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .print-controls h2 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .control-row {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .control-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .control-group label {
            font-size: 12px;
            color: #666;
            font-weight: 600;
        }
        
        .control-group input, .control-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        /* Label Preview Container */
        .labels-preview {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .labels-preview h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .labels-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-start;
        }
        
        /* Individual Label - Ukuran standar label 50x30mm */
        .barcode-label {
            width: 50mm;
            height: 30mm;
            background: #fff;
            border: 1px dashed #ccc;
            padding: 3mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        
        .barcode-label.size-small {
            width: 40mm;
            height: 25mm;
            padding: 2mm;
        }
        
        .barcode-label.size-large {
            width: 60mm;
            height: 40mm;
            padding: 4mm;
        }
        
        .label-product-name {
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            color: #000;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            line-height: 1.2;
        }
        
        .barcode-label.size-small .label-product-name {
            font-size: 6px;
        }
        
        .barcode-label.size-large .label-product-name {
            font-size: 10px;
        }
        
        .label-barcode-svg {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .label-barcode-number {
            font-family: 'Courier New', monospace;
            font-size: 8px;
            color: #000;
            letter-spacing: 1px;
        }
        
        .barcode-label.size-small .label-barcode-number {
            font-size: 6px;
        }
        
        .barcode-label.size-large .label-barcode-number {
            font-size: 10px;
        }
        
        .label-price {
            font-size: 10px;
            font-weight: bold;
            color: #000;
        }
        
        .barcode-label.size-small .label-price {
            font-size: 8px;
        }
        
        .barcode-label.size-large .label-price {
            font-size: 12px;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            
            .print-controls {
                display: none !important;
            }
            
            .labels-preview {
                box-shadow: none;
                padding: 0;
                background: white;
            }
            
            .labels-preview h3 {
                display: none;
            }
            
            .labels-container {
                gap: 0;
            }
            
            .barcode-label {
                border: none;
                margin: 1mm;
            }
            
            @page {
                size: A4;
                margin: 5mm;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <h2><i class="fas fa-barcode"></i> Cetak Label Barcode</h2>
        
        <div class="control-row">
            <div class="control-group">
                <label>Produk</label>
                <strong>{{ $produk->nama_produk }}</strong>
            </div>
            <div class="control-group">
                <label>Barcode</label>
                <strong>{{ $produk->barcode }}</strong>
            </div>
            <div class="control-group">
                <label>Harga Jual</label>
                <strong>Rp {{ number_format($produk->harga_jual ?? 0, 0, ',', '.') }}</strong>
            </div>
        </div>
        
        <div class="control-row">
            <div class="control-group">
                <label>Jumlah Label</label>
                <input type="number" id="labelCount" value="12" min="1" max="100" onchange="generateLabels()">
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
            <button class="btn btn-primary" onclick="window.print()">
                🖨️ Cetak Label
            </button>
            <button class="btn btn-secondary" onclick="window.history.back()">
                ← Kembali
            </button>
        </div>
    </div>
    
    <div class="labels-preview">
        <h3>Preview Label ({{ $produk->nama_produk }})</h3>
        <div class="labels-container" id="labelsContainer">
            <!-- Labels will be generated here -->
        </div>
    </div>
    
    <script>
        const productData = {
            name: "{{ addslashes($produk->nama_produk) }}",
            barcode: "{{ $produk->barcode }}",
            price: "Rp {{ number_format($produk->harga_jual ?? 0, 0, ',', '.') }}"
        };
        
        function generateLabels() {
            const count = parseInt(document.getElementById('labelCount').value) || 12;
            const size = document.getElementById('labelSize').value;
            const showPrice = document.getElementById('showPrice').value === 'yes';
            const container = document.getElementById('labelsContainer');
            
            // Clear existing labels
            container.innerHTML = '';
            
            // Generate labels
            for (let i = 0; i < count; i++) {
                const label = document.createElement('div');
                label.className = `barcode-label size-${size}`;
                
                let barcodeHeight = 25;
                if (size === 'small') barcodeHeight = 18;
                if (size === 'large') barcodeHeight = 35;
                
                label.innerHTML = `
                    <div class="label-product-name">${productData.name}</div>
                    <div class="label-barcode-svg">
                        <svg class="barcode-svg-${i}"></svg>
                    </div>
                    <div class="label-barcode-number">${productData.barcode}</div>
                    ${showPrice ? `<div class="label-price">${productData.price}</div>` : ''}
                `;
                
                container.appendChild(label);
                
                // Generate barcode
                try {
                    JsBarcode(`.barcode-svg-${i}`, productData.barcode, {
                        format: "EAN13",
                        width: 1.2,
                        height: barcodeHeight,
                        displayValue: false,
                        margin: 0,
                        background: "transparent"
                    });
                } catch (e) {
                    JsBarcode(`.barcode-svg-${i}`, productData.barcode, {
                        format: "CODE128",
                        width: 1,
                        height: barcodeHeight,
                        displayValue: false,
                        margin: 0,
                        background: "transparent"
                    });
                }
            }
        }
        
        // Generate labels on page load
        document.addEventListener('DOMContentLoaded', generateLabels);
    </script>
</body>
</html>
