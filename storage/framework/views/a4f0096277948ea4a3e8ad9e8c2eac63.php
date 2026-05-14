<?php $__env->startSection('title', 'Laporan Stok'); ?>

<?php
    /**
     * Format number with proper decimal handling
     * Removes ,00 when no decimal parts, keeps decimals when they exist
     */
    function formatNumberClean($number, $decimals = 2) {
        if ($number == 0) return '0';
        
        // Format with specified decimals first
        $formatted = number_format($number, $decimals, ',', '.');
        
        // Check if there's a decimal point
        $commaPos = strrpos($formatted, ',');
        if ($commaPos !== false) {
            // If decimals part is all zeros, remove it
            $decimalPart = substr($formatted, $commaPos + 1);
            if (preg_match('/^0+$/', $decimalPart)) {
                // Remove decimal part and comma
                return substr($formatted, 0, $commaPos);
            }
            
            // Remove trailing zeros after decimal point
            $formatted = rtrim(rtrim($formatted, '0'), ',');
        }
        
        return $formatted;
    }
    
    /**
     * Format currency with proper decimal handling
     */
    function formatCurrency($number, $decimals = 2) {
        return 'RP' . formatNumberClean($number, $decimals);
    }
    
    /**
     * Format quantity with unit-specific decimal rules
     */
    function formatQuantity($number, $unitName) {
        $decimals = 0;
        
        // Units that should show decimals
        if (!in_array($unitName, ['Potong', 'Ekor', 'Buah', 'Pcs', 'Gram'])) {
            $decimals = 2;
        }
        
        return formatNumberClean($number, $decimals) . ' ' . $unitName;
    }
?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-boxes me-2"></i>Laporan Stok
        </h2>
        <div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('item_id')): ?>
                <a href="<?php echo e(route('laporan.stok.export', request()->query())); ?>" class="btn btn-danger">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-1">
                <i class="fas fa-filter me-2"></i>Filter Laporan
            </h5>
            
            <form method="GET" action="<?php echo e(route('laporan.stok')); ?>" class="d-flex align-items-center gap-2 flex-wrap" style="margin-left: 30px;">
                <div class="d-flex shadow-sm" style="border-radius: 20px; overflow: hidden; background: white; min-width: 400px;">
                    <select name="tipe" class="form-select border-0" id="tipeSelect" style="padding: 8px 15px; background: white; border-radius: 20px 0 0 0; outline: none; box-shadow: none; font-size: 14px;">
                        <option value="material" <?php echo e(request('tipe', 'material') == 'material' ? 'selected' : ''); ?>>Bahan Baku</option>
                        <option value="product" <?php echo e(request('tipe') == 'product' ? 'selected' : ''); ?>>Produk</option>
                        <option value="bahan_pendukung" <?php echo e(request('tipe') == 'bahan_pendukung' ? 'selected' : ''); ?>>Bahan Pendukung</option>
                    </select>
                    
                    <select name="item_id" class="form-select border-0" id="itemSelect" style="padding: 8px 15px; background: white; border-radius: 0 20px 20px 0; outline: none; box-shadow: none; border-left: 1px solid #e0e0e0; font-size: 14px;">
                        <option value="">Pilih Item</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tipe == 'material'): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $materials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($m->id); ?>" <?php echo e(request('item_id') == $m->id ? 'selected' : ''); ?>>
                                    <?php echo e($m->nama_bahan); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php elseif($tipe == 'product'): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>" <?php echo e(request('item_id') == $p->id ? 'selected' : ''); ?>>
                                    <?php echo e($p->nama_produk); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php elseif($tipe == 'bahan_pendukung'): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bahanPendukungs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($bp->id); ?>" <?php echo e(request('item_id') == $bp->id ? 'selected' : ''); ?>>
                                    <?php echo e($bp->nama_bahan); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                
                <!-- Satuan Filter -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('item_id')): ?>
                    <div class="d-flex shadow-sm" style="border-radius: 20px; overflow: hidden; background: white;">
                        <select name="satuan_id" class="form-select border-0" style="padding: 8px 15px; background: white; border-radius: 20px; outline: none; box-shadow: none; font-size: 14px;">
                            <option value="">Semua Satuan</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($availableSatuans)): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableSatuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($satuan['id']); ?>" <?php echo e(request('satuan_id') == $satuan['id'] ? 'selected' : ''); ?>>
                                        <?php echo e($satuan['nama']); ?><?php echo e($satuan['is_primary'] ? ' (Utama)' : ''); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <button type="submit" class="btn shadow-sm" style="border-radius: 20px; padding: 8px 20px; background: #8B7355; color: white; border: none; font-size: 14px;">
                    <i class="fas fa-search me-1"></i>Tampilkan
                </button>
            </form>
        </div>
    </div>

    <!-- Debug Information (temporary) -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($debug_info) && request('item_id') == 5): ?>
    <div class="alert alert-info">
        <strong>Debug Info:</strong><br>
        Total Movements: <?php echo e($debug_info['total_movements']); ?><br>
        Daily Stock Count: <?php echo e($debug_info['daily_stock_count']); ?><br>
        Has Manual Conversion: <?php echo e($debug_info['has_manual_conversion']); ?><br>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($debug_info['sample_movement'])): ?>
        Sample Movement: Qty=<?php echo e($debug_info['sample_movement']['qty']); ?>, Cost=<?php echo e($debug_info['sample_movement']['total_cost']); ?>, Type=<?php echo e($debug_info['sample_movement']['ref_type']); ?><br>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        Potong Conversion Factor: <?php echo e($debug_info['potong_conversion_factor']); ?>

    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Stock Cards for All Items -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('item_id')): ?>
        <?php
            // Get selected item data
            $selectedItem = null;
            $itemName = '';
            
            if($tipe == 'material') {
                $selectedItem = $materials->find(request('item_id'));
                $itemName = $selectedItem->nama_bahan ?? 'Item';
            } elseif($tipe == 'product') {
                $selectedItem = $products->find(request('item_id'));
                $itemName = $selectedItem->nama_produk ?? 'Item';
            } elseif($tipe == 'bahan_pendukung') {
                $selectedItem = $bahanPendukungs->find(request('item_id'));
                $itemName = $selectedItem->nama_bahan ?? 'Item';
            }
            
            // Get available units for this item from database
            $units = [];
            if($selectedItem && isset($availableSatuans)) {
                foreach($availableSatuans as $satuan) {
                    $units[] = [
                        'id' => $satuan['id'],
                        'name' => $satuan['nama'],
                        'is_primary' => $satuan['is_primary'],
                        'conversion' => $satuan['conversion_to_primary'] ?? 1,
                        'price_conversion' => $satuan['price_conversion'] ?? $satuan['conversion_to_primary'] ?? 1, // Use price_conversion if available
                        'price' => $selectedItem->harga_satuan ?? 0 // Use actual price from database, no fallback
                    ];
                }
            }
            
            // Fallback untuk item tanpa satuan
            if(empty($units)) {
                $units = [
                    ['id' => '1', 'name' => 'Unit Utama', 'conversion' => 1, 'price' => $selectedItem->harga_satuan ?? 0],
                ];
            }
            
            $selectedUnit = request('satuan_id', '');
            $showAllUnits = empty($selectedUnit);
        ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAllUnits || $selectedUnit == $unit['id']): ?>
                <?php
                    // Use EXACT EXCEL DATA - matching your spreadsheet exactly
                    $baseQty = (float)($selectedItem->stok ?? 0); // Actual stock from database
                    $basePrice = (float)($selectedItem->harga_satuan ?? 0); // Actual price from database
                    
                    // Special handling for bahan pendukung - always 200 units starting stock
                    if($tipe == 'bahan_pendukung') {
                        $baseQty = 200; // Fixed for bahan pendukung as per user requirement
                    }
                    
                    $baseTotal = $baseQty * $basePrice;
                    
                    // Calculate converted quantities using ACTUAL conversion ratios from database
                    $convertedQty = $baseQty * $unit['conversion'];
                    $convertedPrice = isset($unit['price_conversion']) && $unit['price_conversion'] > 0 ? 
                        $basePrice / $unit['price_conversion'] : 
                        ($unit['conversion'] > 0 ? $basePrice / $unit['conversion'] : $basePrice);
                    
                    // Calculate biaya bahan per unit using BomJobCosting (same as produksi)
                    $biayaBahanPerUnit = 0;
                ?>
                
                <?php
                    // Use actual transaction data from controller instead of static data
                    $stockData = [];
                    
                    if (isset($dailyStock) && count($dailyStock) > 0) {
                        // Convert controller data to view format with unit conversions
                        foreach ($dailyStock as $transaction) {
                            // ALWAYS use master data conversion rate for saldo awal and produksi
                            $conversionRate = $unit['conversion']; // Always use current/master rate
                            
                            // Convert quantities to selected unit using master conversion rate
                            $convertedSaldoAwalQty = $transaction['saldo_awal_qty'] * $conversionRate;
                            
                            // Initialize production variables
                            $convertedProduksiQty = 0;
                            $convertedProduksiHarga = 0;
                            
                            // SPECIAL HANDLING FOR PRODUCTION DATA
                            // For production movements, use appropriate data based on target unit
                            if ($transaction['ref_type'] === 'production' && isset($transaction['qty_as_input']) && isset($transaction['satuan_as_input'])) {
                                $originalQty = (float)$transaction['qty_as_input'];
                                $originalSatuan = $transaction['satuan_as_input'];
                                
                                // CORRECT LOGIC: 
                                // - If displaying in original unit (Potong), use original qty (160)
                                // - If displaying in base unit (Kilogram), use stored qty (40) to avoid double conversion
                                if (strtolower($originalSatuan) === strtolower($unit['name'])) {
                                    // Displaying in original unit - use original quantity
                                    $convertedProduksiQty = $originalQty;
                                } elseif ($unit['is_primary']) {
                                    // Displaying in primary/base unit - use stored converted quantity
                                    $convertedProduksiQty = $transaction['produksi_qty'];
                                } else {
                                    // Displaying in other sub unit - convert from stored base qty
                                    // For kg to gram: 40 kg × 1000 = 40,000 gram
                                    // For kg to ons: 40 kg × 10 = 400 ons
                                    $conversionMultiplier = $unit['conversion']; // Use conversion_to_primary directly
                                    $convertedProduksiQty = $transaction['produksi_qty'] * $conversionMultiplier;
                                }
                            } else {
                                // Not a production movement or no original data, use standard conversion
                                $convertedProduksiQty = $transaction['produksi_qty'] * $conversionRate;
                            }
                            
                            // SPECIAL CALCULATION for final stock
                            // For initial stock row, saldo akhir should equal saldo awal (no other transactions on that row)
                            if ($transaction['ref_type'] === 'initial_stock') {
                                $convertedSaldoAkhirQty = $convertedSaldoAwalQty; // Same as saldo awal for initial stock row
                            } else {
                                // For other transactions, calculate saldo akhir using the master conversion rate
                                $convertedSaldoAkhirQty = $transaction['saldo_akhir_qty'] * $unit['conversion'];
                            }
                            
                            // For products, use sales data instead of purchase data
                            if($tipe == 'product') {
                                $convertedPembelianQty = isset($transaction['penjualan_qty']) ? $transaction['penjualan_qty'] * $unit['conversion'] : 0;
                                $priceConversion = isset($unit['price_conversion']) ? $unit['price_conversion'] : $unit['conversion'];
                                
                                // Fix: Calculate price even if penjualan_qty is 0, use transaction data
                                if (isset($transaction['penjualan_qty']) && $transaction['penjualan_qty'] > 0) {
                                    $convertedPembelianHarga = isset($transaction['penjualan_nilai']) ? 
                                        ($priceConversion > 0 ? $transaction['penjualan_nilai'] / $transaction['penjualan_qty'] / $priceConversion : 0) : 0;
                                } else {
                                    // For sales without cost data, use selling price from sales details
                                    $convertedPembelianHarga = 0; // Will be calculated below
                                }
                                
                                $convertedPembelianTotal = isset($transaction['penjualan_nilai']) ? $transaction['penjualan_nilai'] : 0;
                                
                                // Add separate penjualan data for products
                                $convertedPenjualanQty = $convertedPembelianQty;
                                $convertedPenjualanHarga = $convertedPembelianHarga;
                                $convertedPenjualanTotal = $convertedPembelianTotal;
                            } else {
                                // For materials and bahan pendukung, use appropriate conversion rate
                                $purchaseConversionRate = $unit['conversion']; // Default to master rate
                                
                                // ONLY check manual conversion data for PURCHASE transactions
                                if ($transaction['ref_type'] === 'purchase' &&
                                    isset($transaction['manual_conversion_data']) && $transaction['manual_conversion_data'] && 
                                    isset($transaction['manual_conversion_data']['sub_satuan_id']) &&
                                    $transaction['manual_conversion_data']['sub_satuan_id'] == $unit['id']) {
                                    // Use manual conversion factor ONLY for this specific purchase transaction
                                    $purchaseConversionRate = (float)($transaction['manual_conversion_data']['manual_conversion_factor'] ?? $unit['conversion']);
                                }
                                
                                // For purchase transactions, use the appropriate conversion rate
                                $convertedPembelianQty = $transaction['pembelian_qty'] * $purchaseConversionRate;
                                $convertedPembelianTotal = $transaction['pembelian_nilai'];
                                
                                // Pembelian price will be calculated in the main logic below
                                
                                // No sales data for non-products
                                $convertedPenjualanQty = 0;
                                $convertedPenjualanHarga = 0;
                                $convertedPenjualanTotal = 0;
                            }
                            
                            // Convert prices using CONSISTENT unit cost approach
                            // Calculate base unit cost and consistent unit price
                            $priceConversion = isset($unit['price_conversion']) ? $unit['price_conversion'] : $unit['conversion'];
                            
                            // Calculate unit prices only for columns that have transactions
                            $convertedSaldoAwalHarga = 0;
                            $convertedPembelianHarga = 0;
                            $convertedProduksiHarga = 0;
                            $convertedSaldoAkhirHarga = 0;
                            
                            // Saldo Awal - only show price if there's initial stock
                            if ($transaction['saldo_awal_qty'] > 0 && $transaction['saldo_awal_nilai'] > 0) {
                                $baseUnitCost = $transaction['saldo_awal_nilai'] / $transaction['saldo_awal_qty'];
                                
                                // Use historical conversion rate for initial stock price calculation
                                $saldoAwalPriceConversion = $conversionRate; // Use the same rate as quantity conversion
                                $convertedSaldoAwalHarga = $saldoAwalPriceConversion > 0 ? $baseUnitCost / $saldoAwalPriceConversion : $baseUnitCost;
                            }
                            
                            // Pembelian - show price if there's purchase (including negative retur)
                            if ($transaction['pembelian_qty'] != 0 && $transaction['pembelian_nilai'] != 0) {
                                $baseUnitCost = abs($transaction['pembelian_nilai']) / abs($transaction['pembelian_qty']);
                                $convertedPembelianHarga = $priceConversion > 0 ? $baseUnitCost / $priceConversion : $baseUnitCost;
                            }
                            
                            // Produksi - only show price if there's production usage
                            if ($transaction['produksi_qty'] > 0 && $transaction['produksi_nilai'] > 0) {
                                // For production, use correct price calculation based on unit type
                                if (isset($transaction['qty_as_input']) && isset($transaction['satuan_as_input'])) {
                                    $originalQty = (float)$transaction['qty_as_input'];
                                    $originalSatuan = $transaction['satuan_as_input'];
                                    
                                    // Use original price per unit from stock movement total
                                    $originalPricePerUnit = $transaction['produksi_nilai'] / $originalQty;
                                    
                                    // Find the conversion factor for original unit
                                    $originalConversionFactor = 1;
                                    foreach ($availableSatuans as $availableUnit) {
                                        if (strtolower($availableUnit['nama']) === strtolower($originalSatuan)) {
                                            $originalConversionFactor = $availableUnit['conversion_to_primary'] ?? 1;
                                            break;
                                        }
                                    }
                                    
                                    if (strtolower($originalSatuan) === strtolower($unit['name'])) {
                                        // Displaying in original unit - use original price
                                        $convertedProduksiHarga = $originalPricePerUnit;
                                    } elseif ($unit['is_primary']) {
                                        // Displaying in primary unit - convert price from original unit
                                        $convertedProduksiHarga = $originalPricePerUnit / $originalConversionFactor;
                                    } else {
                                        // Displaying in other sub unit - convert price appropriately
                                        $convertedProduksiHarga = $originalPricePerUnit * $unit['conversion'] / $originalConversionFactor;
                                    }
                                } else {
                                    // Fallback to standard calculation
                                    $baseUnitCost = $transaction['produksi_nilai'] / $transaction['produksi_qty'];
                                    $convertedProduksiHarga = $priceConversion > 0 ? $baseUnitCost / $priceConversion : $baseUnitCost;
                                }
                            }
                            
                            // Saldo Akhir - calculate from final values
                            if ($transaction['saldo_akhir_qty'] > 0 && $transaction['saldo_akhir_nilai'] > 0) {
                                $baseUnitCost = $transaction['saldo_akhir_nilai'] / $transaction['saldo_akhir_qty'];
                                $convertedSaldoAkhirHarga = $priceConversion > 0 ? $baseUnitCost / $priceConversion : $baseUnitCost;
                            }
                            
                            $stockData[] = [
                                'tanggal' => \Carbon\Carbon::parse($transaction['tanggal'])->format('d/m/Y'),
                                'saldo_awal_qty' => $convertedSaldoAwalQty,
                                'saldo_awal_harga' => $convertedSaldoAwalHarga, // Only show if there's initial stock
                                'saldo_awal_total' => $transaction['saldo_awal_nilai'],
                                'pembelian_qty' => $convertedPembelianQty,
                                'pembelian_harga' => $convertedPembelianHarga, // Only show if there's purchase
                                'pembelian_total' => $convertedPembelianTotal,
                                'penjualan_qty' => $convertedPenjualanQty ?? 0,
                                'penjualan_harga' => $convertedPenjualanHarga ?? 0, // Only show if there's sale
                                'penjualan_total' => $convertedPenjualanTotal ?? 0,
                                'produksi_qty' => $convertedProduksiQty,
                                'produksi_harga' => $convertedProduksiHarga, // Only show if there's production
                                'produksi_total' => $transaction['produksi_nilai'],
                                'saldo_akhir_qty' => $convertedSaldoAkhirQty,
                                'saldo_akhir_harga' => $convertedSaldoAkhirHarga, // Always show final price
                                'saldo_akhir_total' => $transaction['saldo_akhir_nilai'],
                                'ref_type' => $transaction['ref_type'] ?? '',
                                'is_opening_balance' => $transaction['is_opening_balance'] ?? false
                            ];
                        }
                    } else {
                        // Fallback: show initial stock if no transactions
                        $stockData = [
                            [
                                'tanggal' => '01/03/2026',
                                'saldo_awal_qty' => $convertedQty,
                                'saldo_awal_harga' => $convertedPrice,
                                'saldo_awal_total' => $baseTotal,
                                'pembelian_qty' => 0,
                                'pembelian_harga' => 0,
                                'pembelian_total' => 0,
                                'penjualan_qty' => 0,
                                'penjualan_harga' => 0,
                                'penjualan_total' => 0,
                                'produksi_qty' => 0,
                                'produksi_harga' => 0,
                                'produksi_total' => 0,
                                'saldo_akhir_qty' => $convertedQty,
                                'saldo_akhir_harga' => $convertedPrice,
                                'saldo_akhir_total' => $baseTotal,
                                'ref_type' => 'initial_stock',
                                'is_opening_balance' => false
                            ]
                        ];
                    }
                ?>
                
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #6c9f6c; color: white;">
                        <h5 class="mb-0">
                            Kartu Stok - <?php echo e($itemName); ?> (Satuan <?php echo e($unit['name']); ?>)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0" style="font-size: 12px; border: 2px solid #000; background-color: #f5f0e8;">
                                <thead style="background-color: #f5f0e8; border: 2px solid #000;">
                                    <tr style="border: 1px solid #000;">
                                        <th rowspan="2" class="text-center align-middle" style="width: 80px; border: 1px solid #000; background-color: #f5f0e8;">Tanggal</th>
                                        <th rowspan="2" class="text-center align-middle" style="width: 120px; border: 1px solid #000; background-color: #f5f0e8;">Keterangan</th>
                                        <th colspan="3" class="text-center" style="border: 1px solid #000; background-color: #f5f0e8;">Stok Awal</th>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tipe == 'product'): ?>
                                            <th colspan="3" class="text-center" style="border: 1px solid #000; background-color: #f5f0e8;">Penjualan</th>
                                        <?php else: ?>
                                            <th colspan="3" class="text-center" style="border: 1px solid #000; background-color: #f5f0e8;">Pembelian</th>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <th colspan="3" class="text-center" style="border: 1px solid #000; background-color: #f5f0e8;">Produksi</th>
                                        <th colspan="3" class="text-center" style="border: 1px solid #000; background-color: #f5f0e8;">Total Stok Dalam Satuan <?php echo e($unit['name']); ?></th>
                                    </tr>
                                    <tr style="border: 1px solid #000;">
                                        <th class="text-center" style="width: 60px; border: 1px solid #000; background-color: #f5f0e8;">Qty</th>
                                        <th class="text-center" style="width: 80px; border: 1px solid #000; background-color: #f5f0e8;">Harga</th>
                                        <th class="text-center" style="width: 100px; border: 1px solid #000; background-color: #f5f0e8;">Total</th>
                                        <th class="text-center" style="width: 60px; border: 1px solid #000; background-color: #f5f0e8;">Qty</th>
                                        <th class="text-center" style="width: 80px; border: 1px solid #000; background-color: #f5f0e8;">Harga</th>
                                        <th class="text-center" style="width: 100px; border: 1px solid #000; background-color: #f5f0e8;">Total</th>
                                        <th class="text-center" style="width: 60px; border: 1px solid #000; background-color: #f5f0e8;">Qty</th>
                                        <th class="text-center" style="width: 80px; border: 1px solid #000; background-color: #f5f0e8;">Harga</th>
                                        <th class="text-center" style="width: 100px; border: 1px solid #000; background-color: #f5f0e8;">Total</th>
                                        <th class="text-center" style="width: 60px; border: 1px solid #000; background-color: #f5f0e8;">Qty</th>
                                        <th class="text-center" style="width: 80px; border: 1px solid #000; background-color: #f5f0e8;">Harga</th>
                                        <th class="text-center" style="width: 100px; border: 1px solid #000; background-color: #f5f0e8;">Total</th>
                                    </tr>
                                </thead>
                                <tbody style="background-color: #f5f0e8;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stockData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            // Generate transaction description based on ref_type
                                            $keterangan = '';
                                            if (isset($row['is_opening_balance']) && $row['is_opening_balance']) {
                                                $keterangan = 'Saldo Awal Bulan';
                                            } elseif (isset($row['ref_type'])) {
                                                switch($row['ref_type']) {
                                                    case 'initial_stock':
                                                        $keterangan = 'Stok Awal';
                                                        break;
                                                    case 'purchase':
                                                        $keterangan = 'Pembelian';
                                                        break;
                                                    case 'sale':
                                                        $keterangan = 'Sale';
                                                        break;
                                                    case 'production':
                                                        if ($tipe === 'product') {
                                                            $keterangan = 'Hasil Produksi';
                                                        } else {
                                                            $keterangan = 'Pemakaian Produksi';
                                                        }
                                                        break;
                                                    case 'adjustment':
                                                        $keterangan = 'Penyesuaian Stok';
                                                        break;
                                                    case 'retur':
                                                        $keterangan = 'Retur';
                                                        break;
                                                    case 'opening_balance':
                                                        $keterangan = 'Saldo Awal Bulan';
                                                        break;
                                                    default:
                                                        $keterangan = ucfirst(str_replace('_', ' ', $row['ref_type']));
                                                }
                                            } else {
                                                $keterangan = 'Transaksi';
                                            }
                                        ?>
                                        <tr class="<?php echo e((isset($row['is_opening_balance']) && $row['is_opening_balance']) ? 'table-info' : ''); ?>" style="border: 1px solid #000; background-color: #f5f0e8;">
                                            <td class="text-center" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e($row['tanggal']); ?></td>
                                            <td class="text-center" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e($keterangan); ?></td>
                                            <td class="text-end" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e((isset($row['saldo_awal_qty']) && $row['saldo_awal_qty'] != 0) ? formatQuantity($row['saldo_awal_qty'], $unit['name']) : ''); ?></td>
                                            <td class="text-end" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e($row['saldo_awal_harga'] > 0 ? formatCurrency($row['saldo_awal_harga']) : ''); ?></td>
                                            <td class="text-end" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e($row['saldo_awal_total'] > 0 ? formatCurrency($row['saldo_awal_total'], 0) : ''); ?></td>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tipe == 'product'): ?>
                                                <!-- For products, show sales data instead of purchase data -->
                                                <td class="text-end" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e(isset($row['penjualan_qty']) && $row['penjualan_qty'] != 0 ? formatQuantity($row['penjualan_qty'], $unit['name']) : ''); ?></td>
                                                <td class="text-end" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e(isset($row['penjualan_harga']) && $row['penjualan_harga'] > 0 ? formatCurrency($row['penjualan_harga']) : ''); ?></td>
                                                <td class="text-end" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e(isset($row['penjualan_total']) && $row['penjualan_total'] > 0 ? formatCurrency($row['penjualan_total'], 0) : ''); ?></td>
                                            <?php else: ?>
                                                <!-- For materials and bahan pendukung, show purchase data -->

                                                <td class="text-end" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e(isset($row['pembelian_qty']) && $row['pembelian_qty'] != 0 ? formatQuantity($row['pembelian_qty'], $unit['name']) : ''); ?></td>
<td class="text-end" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e($row['pembelian_harga'] > 0 ? formatCurrency($row['pembelian_harga']) : ''); ?></td>
                                                <td class="text-end" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e($row['pembelian_total'] != 0 ? formatCurrency($row['pembelian_total'], 0) : ''); ?></td>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <td class="text-end" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e(isset($row['produksi_qty']) && $row['produksi_qty'] != 0 ? formatQuantity($row['produksi_qty'], $unit['name']) : ''); ?></td>
                                            <td class="text-end" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e($row['produksi_harga'] > 0 ? formatCurrency($row['produksi_harga']) : ''); ?></td>
                                            <td class="text-end" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e($row['produksi_total'] > 0 ? formatCurrency($row['produksi_total'], 0) : ''); ?></td>
                                            <td class="text-end fw-bold" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e(formatQuantity($row['saldo_akhir_qty'], $unit['name'])); ?></td>
                                            <td class="text-end fw-bold" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e($row['saldo_akhir_harga'] > 0 ? formatCurrency($row['saldo_akhir_harga']) : ''); ?></td>
                                            <td class="text-end fw-bold" style="border: 1px solid #000; background-color: #f5f0e8;"><?php echo e($row['saldo_akhir_total'] > 0 ? formatCurrency($row['saldo_akhir_total'], 0) : ''); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Pilih Item untuk Melihat Laporan Stok</h5>
                <p class="text-muted">Silakan pilih "Ayam Kampung" dari dropdown di atas untuk melihat kartu stok.</p>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipeSelect = document.getElementById('tipeSelect');
    const itemSelect = document.getElementById('itemSelect');
    
    // Handle material type change
    tipeSelect.addEventListener('change', function() {
        // Clear item selection
        itemSelect.value = '';
        
        // Submit form to reload with new material type
        this.form.submit();
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/laporan/stok/index.blade.php ENDPATH**/ ?>