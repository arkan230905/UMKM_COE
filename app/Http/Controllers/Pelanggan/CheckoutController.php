<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Notification;
use App\Models\OngkirSetting;
use App\Services\MidtransService;
use App\Services\DistanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected $midtrans;
    protected $distanceService;
    protected $addressService;
    protected $rajaOngkirService;

    public function __construct(
        MidtransService $midtrans, 
        DistanceService $distanceService, 
        \App\Services\IndonesiaAddressService $addressService,
        \App\Services\RajaOngkirService $rajaOngkirService
    ) {
        $this->midtrans = $midtrans;
        $this->distanceService = $distanceService;
        $this->addressService = $addressService;
        $this->rajaOngkirService = $rajaOngkirService;
    }

    public function index()
    {
        $carts = Cart::with(['produk' => function ($query) {
            $query->withoutGlobalScopes();
        }])
            ->where('user_id', auth()->id())
            ->get();

        if ($carts->isEmpty()) {
            return redirect()->route('pelanggan.cart')
                ->with('error', 'Keranjang kosong!');
        }

        $total = $carts->sum('subtotal');
        
        // Get owner's company info for bank details
        $firstCart = $carts->first();
        $ownerId = $firstCart->produk->user_id;
        $ownerUser = \App\Models\User::find($ownerId);
        $perusahaan = $ownerUser ? $ownerUser->perusahaan : null;

        // Get perusahaan_slug for URL generation
        $perusahaan_slug = $perusahaan ? ($perusahaan->slug ?: strtolower(str_replace(' ', '-', $perusahaan->kode))) : '';

        return view('pelanggan.checkout', compact('carts', 'total', 'perusahaan', 'perusahaan_slug'));
    }

    public function getAddressSuggestions(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:3',
        ]);

        $query = $request->q;
        
        try {
            $suggestions = $this->distanceService->getAddressSuggestions($query);
            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil saran alamat',
                'suggestions' => []
            ], 400);
        }
    }

    public function getProvinces()
    {
        $provinces = $this->rajaOngkirService->getProvinces();
        return response()->json([
            'success' => true,
            'data' => $provinces
        ]);
    }

    public function getCities(Request $request)
    {
        $request->validate(['province_id' => 'required']);
        
        $cities = $this->rajaOngkirService->getCities($request->province_id);
        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    public function getDistricts(Request $request)
    {
        $request->validate(['city_id' => 'required']);
        
        $subDistricts = $this->rajaOngkirService->getSubDistricts($request->city_id);
        return response()->json([
            'success' => true,
            'data' => $subDistricts
        ]);
    }

    public function getSubDistricts(Request $request)
    {
        $request->validate(['district_id' => 'required']);
        
        // RajaOngkir uses subdistrict as the final level
        $subDistricts = $this->rajaOngkirService->getSubDistricts($request->district_id);
        return response()->json([
            'success' => true,
            'data' => $subDistricts
        ]);
    }

    public function calculateOngkir(Request $request)
    {
        $request->validate([
            'destination_city_id' => 'required|integer',
            'weight' => 'nullable|integer|min:100'
        ]);

        $user = auth()->user();
        $destinationCityId = $request->destination_city_id;
        $weight = $request->weight ?? 1000; // Default 1kg

        // Store location is Bandung (City ID: 94 in RajaOngkir)
        $originCityId = 94;

        $result = $this->rajaOngkirService->calculateShipping($originCityId, $destinationCityId, $weight, 'jne');

        if ($result['success']) {
            $shippingData = $result['data'][0]; // Get first courier result
            $cost = $shippingData['costs'][0]['cost'][0]['value'] ?? 0;

            return response()->json([
                'success' => true,
                'ongkir' => $cost,
                'courier' => $shippingData['name'],
                'service' => $shippingData['costs'][0]['service'],
                'description' => $shippingData['costs'][0]['description'],
                'etd' => $shippingData['costs'][0]['cost'][0]['etd'] ?? 'N/A'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal menghitung ongkir'
        ], 400);
    }

    public function debugOngkir(Request $request)
    {
        // Get the first cart item to find the owner/seller
        $firstCart = Cart::where('user_id', auth()->id())
            ->with(['produk' => function ($query) {
                $query->withoutGlobalScopes();
            }])
            ->first();

        if (!$firstCart || !$firstCart->produk) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong',
            ], 400);
        }

        // Get the owner/seller of the product
        $ownerId = $firstCart->produk->user_id;
        $ownerUser = \App\Models\User::find($ownerId);
        $perusahaan = $ownerUser ? $ownerUser->perusahaan : null;

        if (!$perusahaan) {
            return response()->json([
                'success' => false,
                'message' => 'Perusahaan tidak ditemukan',
            ], 400);
        }

        // Get test address from request
        $testAddress = $request->alamat ?? 'Fakultas Ilmu Terapan, Jalan Sukabirus, Sukacahaya, Dayeuhkolot, Kabupaten Bandung, West Java, Java, 40257, Indonesia';
        $testLat = $request->latitude;
        $testLon = $request->longitude;

        // Geocode test address if coordinates not provided
        if (!$testLat || !$testLon) {
            $geocodeResult = $this->distanceService->geocodeAddress($testAddress);
            if ($geocodeResult['success']) {
                $testLat = $geocodeResult['latitude'];
                $testLon = $geocodeResult['longitude'];
            }
        }

        // Calculate distance
        $distance = $this->distanceService->calculateHaversineDistance(
            $perusahaan->latitude,
            $perusahaan->longitude,
            $testLat,
            $testLon
        );

        // Find matching ongkir
        $ongkir = \App\Models\OngkirSetting::where('status', true)
            ->where('jarak_min', '<=', $distance)
            ->where(function ($query) use ($distance) {
                $query->whereNull('jarak_max')
                    ->orWhere('jarak_max', '>=', $distance);
            })
            ->orderBy('jarak_min', 'desc')
            ->first();

        // Get all ongkir settings for reference
        $allSettings = \App\Models\OngkirSetting::where('status', true)
            ->orderBy('jarak_min', 'asc')
            ->get(['jarak_min', 'jarak_max', 'harga_ongkir']);

        return response()->json([
            'success' => true,
            'debug_info' => [
                'store' => [
                    'nama' => $perusahaan->nama,
                    'latitude' => $perusahaan->latitude,
                    'longitude' => $perusahaan->longitude,
                ],
                'test_address' => [
                    'alamat' => $testAddress,
                    'latitude' => $testLat,
                    'longitude' => $testLon,
                ],
                'distance_calculation' => [
                    'distance_km' => round($distance, 4),
                    'distance_rounded' => round($distance, 2),
                ],
                'ongkir_matched' => $ongkir ? [
                    'jarak_min' => $ongkir->jarak_min,
                    'jarak_max' => $ongkir->jarak_max,
                    'harga_ongkir' => $ongkir->harga_ongkir,
                    'label' => $ongkir->getJarakLabel(),
                ] : null,
                'all_ongkir_settings' => $allSettings->map(function ($setting) {
                    return [
                        'jarak_min' => $setting->jarak_min,
                        'jarak_max' => $setting->jarak_max,
                        'harga_ongkir' => $setting->harga_ongkir,
                        'label' => $setting->getJarakLabel(),
                    ];
                })->toArray(),
            ]
        ]);
    }

    public function getOngkir(Request $request)
    {
        $request->validate([
            'alamat' => 'required|string',
        ]);

        $alamat = $request->alamat;
        $user = auth()->user();

        // Get the first cart item to find the owner/seller
        $firstCart = Cart::where('user_id', auth()->id())
            ->with(['produk' => function ($query) {
                $query->withoutGlobalScopes();
            }])
            ->first();

        if (!$firstCart || !$firstCart->produk) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong',
                'ongkir' => 0
            ], 400);
        }

        // Get the owner/seller of the product
        $ownerId = $firstCart->produk->user_id;
        $ownerUser = \App\Models\User::find($ownerId);

        if (!$ownerUser) {
            return response()->json([
                'success' => false,
                'message' => 'Penjual tidak ditemukan',
                'ongkir' => 0
            ], 400);
        }

        // Get the company details from the owner
        $perusahaan = $ownerUser->perusahaan;

        if (!$perusahaan) {
            return response()->json([
                'success' => false,
                'message' => 'Toko penjual tidak ditemukan',
                'ongkir' => 0
            ], 400);
        }

        // Check if store location is set
        if (!$perusahaan->latitude || !$perusahaan->longitude) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi toko penjual belum diatur',
                'ongkir' => 0
            ], 400);
        }

        // Get coordinates if provided from frontend autocomplete
        $latitude = $request->latitude;
        $longitude = $request->longitude;

        if ($latitude && $longitude) {
            // Calculate distance directly using provided coordinates
            $distance = $this->distanceService->calculateHaversineDistance(
                $perusahaan->latitude,
                $perusahaan->longitude,
                $latitude,
                $longitude
            );
            $distanceResult = [
                'success' => true,
                'distance_km' => round($distance, 2),
                'distance_text' => round($distance, 2) . ' km',
                'destination_address' => $alamat,
            ];
        } else {
            // Calculate distance using Geocoding as fallback
            $distanceResult = $this->distanceService->calculateDistanceToAddress(
                $perusahaan->latitude,
                $perusahaan->longitude,
                $alamat
            );
        }

        if (!$distanceResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $distanceResult['message'],
                'ongkir' => 0
            ], 400);
        }

        $jarak = $distanceResult['distance_km'];

        // Debug log
        \Log::info('Ongkir Calculation - getOngkir', [
            'store_lat' => $perusahaan->latitude,
            'store_lon' => $perusahaan->longitude,
            'dest_lat' => $latitude ?? 'from_geocode',
            'dest_lon' => $longitude ?? 'from_geocode',
            'jarak' => $jarak,
            'alamat' => $alamat,
        ]);

        // Find matching ongkir setting
        $ongkir = OngkirSetting::where('status', true)
            ->where('jarak_min', '<=', $jarak)
            ->where(function ($query) use ($jarak) {
                $query->whereNull('jarak_max')
                    ->orWhere('jarak_max', '>=', $jarak);
            })
            ->orderBy('jarak_min', 'desc')
            ->first();

        // Debug log hasil query
        \Log::info('Ongkir Result - getOngkir', [
            'jarak' => $jarak,
            'ongkir_id' => $ongkir?->id,
            'ongkir_range' => $ongkir ? ($ongkir->jarak_min . '-' . ($ongkir->jarak_max ?? '∞')) : 'NOT FOUND',
            'ongkir_harga' => $ongkir?->harga_ongkir,
            'all_settings' => OngkirSetting::where('status', true)->get(['id', 'jarak_min', 'jarak_max', 'harga_ongkir'])->toArray(),
        ]);

        if (!$ongkir) {
            return response()->json([
                'success' => false,
                'message' => 'Ongkir tidak tersedia untuk jarak ' . $jarak . ' km',
                'ongkir' => 0
            ], 400);
        }

        // Format range km untuk display
        $rangeText = $ongkir->jarak_min . '-' . ($ongkir->jarak_max ?? '∞') . ' km';

        return response()->json([
            'success' => true,
            'ongkir' => $ongkir->harga_ongkir,
            'jarak' => $jarak,
            'jarak_text' => $distanceResult['distance_text'],
            'range_km' => $rangeText,
            'alamat_tujuan' => $distanceResult['destination_address'],
            'message' => 'Ongkir ditemukan',
            'store_info' => [
                'nama' => $perusahaan->nama ?? 'Toko',
                'alamat' => $perusahaan->alamat ?? 'Alamat tidak tersedia',
            ]
        ]);
    }

    public function process(Request $request)
    {
        \Log::info('CheckoutController::process() called', [
            'payment_method' => $request->payment_method,
            'user_id' => auth()->id(),
        ]);
        
        $request->validate([
            'nama_penerima' => 'required|string|max:255',
            'alamat_pengiriman' => 'required|string',
            'telepon_penerima' => 'required|string|max:20',
            'payment_method' => 'required|in:qris,va_bca,va_bni,va_bri,va_mandiri,transfer,cod,kasir',
            'catatan' => 'nullable|string',
        ]);

        $carts = Cart::with(['produk' => function ($query) {
            $query->withoutGlobalScopes();
        }])
            ->where('user_id', auth()->id())
            ->get();

        if ($carts->isEmpty()) {
            return back()->with('error', 'Keranjang kosong!');
        }

        DB::beginTransaction();
        try {
            // Validasi stok
            foreach ($carts as $cart) {
                if (!$cart->produk) {
                    throw new \Exception("Produk tidak ditemukan di keranjang!");
                }
                if ($cart->produk->stok < $cart->qty) {
                    throw new \Exception("Stok {$cart->produk->nama_produk} tidak mencukupi!");
                }
            }

            // Hitung Ongkir secara aman di backend
            $subtotal = $carts->sum('subtotal');
            $ppn = $subtotal * 0.11;
            $ongkir = 0;
            
            // Get the owner/seller from the first product in cart
            $ownerId = $carts->first()->produk->user_id;
            $ownerUser = \App\Models\User::find($ownerId);
            $perusahaan = $ownerUser ? $ownerUser->perusahaan : null;
            
            // CRITICAL: Only calculate ongkir for COD method
            // For Kasir (pick up at store), ongkir is always 0
            if ($request->payment_method === 'cod' && $perusahaan && $perusahaan->latitude && $perusahaan->longitude) {
                
                $latPengiriman = $request->latitude_pengiriman;
                $lonPengiriman = $request->longitude_pengiriman;
                
                \Log::info('Ongkir Calculation - process() START', [
                    'store_lat' => $perusahaan->latitude,
                    'store_lon' => $perusahaan->longitude,
                    'lat_pengiriman' => $latPengiriman,
                    'lon_pengiriman' => $lonPengiriman,
                    'alamat_pengiriman' => $request->alamat_pengiriman,
                ]);
                
                if ($latPengiriman && $lonPengiriman) {
                    $distance = $this->distanceService->calculateHaversineDistance(
                        $perusahaan->latitude,
                        $perusahaan->longitude,
                        $latPengiriman,
                        $lonPengiriman
                    );
                    $distanceResult = [
                        'success' => true,
                        'distance_km' => round($distance, 2)
                    ];
                    
                    \Log::info('Distance calculated from coordinates', [
                        'distance_km' => $distanceResult['distance_km'],
                    ]);
                } else {
                    $distanceResult = $this->distanceService->calculateDistanceToAddress(
                        $perusahaan->latitude,
                        $perusahaan->longitude,
                        $request->alamat_pengiriman
                    );
                    
                    \Log::info('Distance calculated from geocoding', [
                        'distance_km' => $distanceResult['distance_km'] ?? 'FAILED',
                    ]);
                }
                
                if ($distanceResult['success']) {
                    $jarak = $distanceResult['distance_km'];
                    $ongkirSetting = \App\Models\OngkirSetting::where('status', true)
                        ->where('jarak_min', '<=', $jarak)
                        ->where(function ($query) use ($jarak) {
                            $query->whereNull('jarak_max')
                                ->orWhere('jarak_max', '>=', $jarak);
                        })
                        ->orderBy('jarak_min', 'desc')
                        ->first();
                    
                    \Log::info('Ongkir Setting Found', [
                        'jarak' => $jarak,
                        'ongkir_id' => $ongkirSetting?->id,
                        'ongkir_range' => $ongkirSetting ? ($ongkirSetting->jarak_min . '-' . ($ongkirSetting->jarak_max ?? '∞')) : 'NOT FOUND',
                        'ongkir_harga' => $ongkirSetting?->harga_ongkir,
                    ]);
                        
                    if ($ongkirSetting) {
                        $ongkir = $ongkirSetting->harga_ongkir;
                    }
                }
            }
            // For Kasir method, ongkir remains 0
            
            $totalAmount = $subtotal + $ppn + $ongkir;

            // Determine stored method and note based on payment method
            $storedMethod = $request->payment_method;
            $catatanInput = $request->catatan;
            
            $rincian = " | Rincian: Subtotal Rp " . number_format($subtotal, 0, ',', '.') . 
                       ", PPN Rp " . number_format($ppn, 0, ',', '.') . 
                       ", Ongkir Rp " . number_format($ongkir, 0, ',', '.');
                       
            if ($request->payment_method === 'kasir') {
                $prefixNote = 'Metode: Bayar di Kasir (Pick Up). ';
                $catatanInput = $prefixNote . (string) $catatanInput . $rincian;
            } elseif ($request->payment_method === 'cod') {
                $prefixNote = 'Metode: COD (Cash On Delivery). ';
                $catatanInput = $prefixNote . (string) $catatanInput . $rincian;
            } else {
                $catatanInput = (string) $catatanInput . $rincian;
            }

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'nomor_order' => Order::generateNomorOrder(),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_method' => $storedMethod,
                'payment_status' => 'pending',
                'nama_penerima' => $request->nama_penerima,
                'alamat_pengiriman' => $request->alamat_pengiriman,
                'telepon_penerima' => $request->telepon_penerima,
                'catatan' => $catatanInput,
            ]);

            // Create order items & kurangi stok
            foreach ($carts as $cart) {
                \Log::info('Processing cart item', [
                    'cart_id' => $cart->id,
                    'produk_id' => $cart->produk_id,
                    'qty' => $cart->qty,
                    'produk' => $cart->produk ? $cart->produk->nama_produk : 'NULL',
                ]);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'produk_id' => $cart->produk_id,
                    'qty' => $cart->qty,
                    'harga' => $cart->harga,
                    'subtotal' => $cart->subtotal,
                ]);

                // Kurangi stok - CRITICAL: Bypass UserScope untuk update produk milik owner lain
                if ($cart->produk) {
                    $oldStok = $cart->produk->stok;

                    // Use raw query to bypass global scope
                    \App\Models\Produk::withoutGlobalScopes()
                        ->where('id', $cart->produk_id)
                        ->decrement('stok', $cart->qty);
                    
                    $newStok = \App\Models\Produk::withoutGlobalScopes()
                        ->find($cart->produk_id)->stok;
                    
                    \Log::info('Stock decremented', [
                        'produk_id' => $cart->produk_id,
                        'old_stok' => $oldStok,
                        'qty' => $cart->qty,
                        'new_stok' => $newStok,
                    ]);
                    
                    // CRITICAL: Create stock_movements record for audit trail
                    // This is needed so dashboard calculates stok_tersedia correctly
                    try {
                        \App\Models\StockMovement::create([
                            'user_id' => $cart->produk->user_id,  // Owner ID
                            'item_type' => 'product',
                            'item_id' => $cart->produk_id,
                            'tanggal' => now()->toDateString(),
                            'direction' => 'out',  // Stock going out (sale)
                            'qty' => $cart->qty,
                            'satuan' => $cart->produk->satuan_id ? $cart->produk->satuan->nama : 'pcs',
                            'unit_cost' => $cart->harga,
                            'total_cost' => $cart->subtotal,
                            'ref_type' => 'sale',
                            'ref_id' => $order->id,
                            'keterangan' => "Penjualan Order #{$order->nomor_order}",
                        ]);
                        
                        \Log::info('Stock movement created', [
                            'produk_id' => $cart->produk_id,
                            'qty' => $cart->qty,
                            'order_id' => $order->id,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to create stock movement', [
                            'produk_id' => $cart->produk_id,
                            'error' => $e->getMessage(),
                        ]);
                        // Don't throw - let checkout continue
                    }
                } else {
                    \Log::warning('Produk is null for cart item', ['cart_id' => $cart->id]);
                }
            }

            // TRIGGER MANUAL: Sinkronisasi Order ke Penjualan untuk owner
            // This MUST be called AFTER OrderItems are created
            try {
                \Log::info('CheckoutController: Starting Order to Penjualan sync', ['order_id' => $order->id]);
                $service = new \App\Services\OrderToSalesService();
                $result = $service->syncOrderToPenjualan($order);
                \Log::info('CheckoutController: Order to Penjualan sync completed', ['order_id' => $order->id]);
                
                // Handle both single and multi-owner results
                $penjualans = is_array($result) ? $result : [$result];
                
                foreach ($penjualans as $penjualan) {
                    \Log::info('CheckoutController: Order synced to Penjualan successfully', [
                        'order_id' => $order->id,
                        'penjualan_id' => $penjualan->id,
                        'nomor_penjualan' => $penjualan->nomor_penjualan,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('CheckoutController: Failed to sync order to penjualan', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Don't throw - let checkout continue
            }

            // Handle payment status based on payment method
            \Log::info('CheckoutController: Handling payment method', [
                'payment_method' => $request->payment_method,
                'order_id' => $order->id,
            ]);
            
            if ($request->payment_method === 'kasir') {
                \Log::info('CheckoutController: Processing kasir payment', ['order_id' => $order->id]);
                
                // Kasir (Pick up at store) - mark as paid and completed immediately
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'completed',
                    'paid_at' => now(),
                ]);
                
                \Log::info('CheckoutController: Order updated to paid', ['order_id' => $order->id]);
                
                // Also update Penjualan payment status to paid
                // This will trigger Penjualan observer to create journals
                try {
                    $penjualans = \App\Models\Penjualan::where('order_id', $order->id)->get();
                    \Log::info('CheckoutController: Found penjualans to update', [
                        'order_id' => $order->id,
                        'penjualan_count' => $penjualans->count(),
                    ]);
                    
                    foreach ($penjualans as $penjualan) {
                        \Log::info('CheckoutController: Updating penjualan', [
                            'penjualan_id' => $penjualan->id,
                            'current_status' => $penjualan->payment_status,
                        ]);
                        
                        $penjualan->update([
                            'payment_status' => 'paid',
                            'payment_confirmed_at' => now(),
                        ]);
                        
                        \Log::info('CheckoutController: Penjualan updated', [
                            'penjualan_id' => $penjualan->id,
                            'new_status' => 'paid',
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('CheckoutController: Failed to update penjualan payment status for kasir', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } elseif ($request->payment_method === 'cod') {
                \Log::info('CheckoutController: Processing COD payment', ['order_id' => $order->id]);
                // COD (Cash On Delivery) - pending payment
                $order->update([
                    'payment_status' => 'pending',
                    'status' => 'pending',
                ]);
            } elseif ($request->payment_method === 'transfer') {
                \Log::info('CheckoutController: Processing transfer payment', ['order_id' => $order->id]);
                // Transfer manual - pending payment, no Midtrans token
                $order->update([
                    'payment_status' => 'pending',
                    'status' => 'pending',
                ]);
            } else {
                \Log::info('CheckoutController: Processing Midtrans payment', ['order_id' => $order->id]);
                // Get Midtrans Snap Token for other payment methods (QRIS, VA, etc)
                $snapToken = $this->midtrans->createTransaction($order, $order->items);
                $order->update(['snap_token' => $snapToken]);
            }

            // Clear cart
            Cart::where('user_id', auth()->id())->delete();

            // Create notification
            $notificationMsg = '';
            if ($request->payment_method === 'kasir') {
                $notificationMsg = "Pesanan {$order->nomor_order} berhasil dibuat. Silakan ambil di toko kami.";
            } elseif ($request->payment_method === 'cod') {
                $notificationMsg = "Pesanan {$order->nomor_order} berhasil dibuat. Bayar saat barang tiba.";
            } elseif ($request->payment_method === 'transfer') {
                $notificationMsg = "Pesanan {$order->nomor_order} berhasil dibuat. Silakan transfer ke rekening yang telah ditampilkan.";
            } else {
                $notificationMsg = "Pesanan {$order->nomor_order} berhasil dibuat. Silakan lakukan pembayaran.";
            }
            
            Notification::createNotification(
                auth()->id(),
                'order_created',
                'Pesanan Dibuat',
                $notificationMsg,
                ['order_id' => $order->id]
            );

            DB::commit();

            $msg = '';
            if ($request->payment_method === 'kasir') {
                $msg = 'Pesanan berhasil dibuat! Silakan ambil di toko kami. Nomor Pesanan: ' . $order->nomor_order;
            } elseif ($request->payment_method === 'cod') {
                $msg = 'Pesanan berhasil dibuat! Bayar saat barang tiba. Nomor Pesanan: ' . $order->nomor_order;
            } elseif ($request->payment_method === 'transfer') {
                $msg = 'Pesanan berhasil dibuat! Silakan transfer ke rekening yang telah ditampilkan. Nomor Pesanan: ' . $order->nomor_order;
            } else {
                $msg = 'Pesanan berhasil dibuat! Silakan lakukan pembayaran. Nomor Pesanan: ' . $order->nomor_order;
            }

            return redirect()->route('pelanggan.orders.show', $order->id)
                ->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Checkout gagal: ' . $e->getMessage());
        }
    }
}
