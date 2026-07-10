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

    public function index(Request $request, $perusahaan_slug = null)
    {
        $carts = Cart::with(['produk' => function ($query) {
            $query->withoutGlobalScopes();
        }])
            ->where('user_id', auth()->id())
            ->get();

        if ($carts->isEmpty()) {
            return redirect()->route('pelanggan.cart', ['perusahaan_slug' => request()->route('perusahaan_slug')])
                ->with('error', 'Keranjang kosong!');
        }

        $total = $carts->sum('subtotal');
        
        $perusahaan_slug = $perusahaan_slug ?? $request->route('perusahaan_slug');
        
        // Try to get from middleware attributes first
        $perusahaan = $request->attributes->get('perusahaan');
        
        if (!$perusahaan) {
            $perusahaan = \App\Models\Perusahaan::withoutGlobalScope('user')->where(function ($q) use ($perusahaan_slug) {
                $q->where('slug', strtolower($perusahaan_slug))
                  ->orWhere('kode', strtoupper($perusahaan_slug));
            })->first();
        }

        // Keep fallback for slug if somehow null
        $perusahaan_slug = $perusahaan ? ($perusahaan->slug ?: strtolower(str_replace(' ', '-', $perusahaan->kode))) : $perusahaan_slug;

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

    public function getOngkir(Request $request, $perusahaan_slug = null)
    {
        $request->validate([
            'alamat' => 'required|string',
        ]);

        $alamat = $request->alamat;
        $user = auth()->user();
        
        $perusahaan_slug = $perusahaan_slug ?? $request->route('perusahaan_slug');
        
        // Try to get from middleware attributes first
        $perusahaan = $request->attributes->get('perusahaan');
        
        if (!$perusahaan) {
            $perusahaan = \App\Models\Perusahaan::withoutGlobalScope('user')->where(function ($q) use ($perusahaan_slug) {
                $q->where('slug', strtolower($perusahaan_slug))
                  ->orWhere('kode', strtoupper($perusahaan_slug));
            })->first();
        }

        if (!$perusahaan) {
            return response()->json([
                'success' => false,
                'message' => 'Data perusahaan tidak ditemukan. Periksa kode perusahaan pada URL.',
                'ongkir' => 0
            ], 400);
        }

        // Check if store location is set
        if (!$perusahaan->latitude || !$perusahaan->longitude) {
            return response()->json([
                'success' => false,
                'message' => 'Koordinat perusahaan belum diatur. Silakan atur titik lokasi perusahaan di menu Tentang Perusahaan.',
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
        $ongkir = OngkirSetting::withoutGlobalScopes()
            ->where('user_id', $perusahaan->user_id)
            ->where('status', true)
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
            'all_settings' => OngkirSetting::withoutGlobalScopes()->where('user_id', $perusahaan->user_id)->where('status', true)->get(['id', 'jarak_min', 'jarak_max', 'harga_ongkir'])->toArray(),
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

    public function process(Request $request, $perusahaan_slug = null)
    {
        $isDelivery = $request->input('jenis_pengiriman', 'delivery') === 'delivery';

        $request->validate([
            'jenis_pengiriman' => 'required|in:delivery,ambil_di_toko',
            'nama_penerima' => 'required|string|max:255',
            'telepon_penerima' => 'required|string|max:20',
            'alamat_pengiriman' => $isDelivery ? 'required|string' : 'nullable|string',
            'latitude_pengiriman' => $isDelivery ? 'required|numeric' : 'nullable|numeric',
            'longitude_pengiriman' => $isDelivery ? 'required|numeric' : 'nullable|numeric',
            'kecamatan' => 'nullable|string',
            'kota' => 'nullable|string',
            'kode_pos' => 'nullable|string',
            'detail_alamat' => 'nullable|string',
            'catatan' => 'nullable|string',
            'provinsi' => 'nullable|string',
            'kelurahan' => 'nullable|string',
            'negara' => 'nullable|string',
            'biaya_ongkir' => $isDelivery ? 'required|numeric' : 'nullable|numeric',
        ]);

        $checkoutData = $request->all();
        if (!$isDelivery) {
            $checkoutData['biaya_ongkir'] = 0;
        }

        $carts = Cart::with(['produk' => function ($query) {
            $query->withoutGlobalScopes();
        }])->where('user_id', auth()->id())->get();

        if ($carts->isEmpty()) {
            return back()->with('error', 'Keranjang kosong!');
        }

        session(['checkout_data' => $checkoutData]);

        return redirect()->route('pelanggan.checkout.payment', ['perusahaan_slug' => $perusahaan_slug ?? request()->route('perusahaan_slug')]);
    }

    public function payment(Request $request, $perusahaan_slug = null)
    {
        $perusahaan_slug = $perusahaan_slug ?? $request->route('perusahaan_slug');
        
        $perusahaan = $request->attributes->get('perusahaan');
        
        if (!$perusahaan) {
            $perusahaan = \App\Models\Perusahaan::withoutGlobalScope('user')->where(function ($q) use ($perusahaan_slug) {
                $q->where('slug', strtolower($perusahaan_slug))
                  ->orWhere('kode', strtoupper($perusahaan_slug));
            })->firstOrFail();
        }
        $checkoutData = session('checkout_data');
        
        if (!$checkoutData) {
            return redirect()->route('pelanggan.checkout', ['perusahaan_slug' => $perusahaan_slug])->with('error', 'Silakan isi data pengiriman terlebih dahulu.');
        }

        $carts = Cart::with(['produk' => function ($query) {
            $query->withoutGlobalScopes();
        }])->where('user_id', auth()->id())->get();

        if ($carts->isEmpty()) {
            return redirect()->route('pelanggan.cart', ['perusahaan_slug' => $perusahaan_slug])->with('error', 'Keranjang kosong!');
        }

        $subtotal = $carts->sum('subtotal');
        $ppn = $subtotal * 0.11;
        $ongkir = $checkoutData['biaya_ongkir'] ?? 0;
        $total = $subtotal + $ppn + $ongkir;

        $rekeningBanks = \App\Models\Coa::withoutGlobalScopes()
            ->where('user_id', $perusahaan->user_id)
            ->whereNotNull('nomor_rekening')
            ->where('nomor_rekening', '!=', '')
            ->get();

        $midtransEnabled = !empty(config('midtrans.server_key')) && !empty(config('midtrans.client_key'));

        $defaultMidtransVA = [
            'bca' => ['code' => 'bca', 'name' => 'BCA'],
            'bni' => ['code' => 'bni', 'name' => 'BNI'],
            'bri' => ['code' => 'bri', 'name' => 'BRI'],
            'echannel' => ['code' => 'echannel', 'name' => 'MANDIRI'],
            'permata' => ['code' => 'permata', 'name' => 'PERMATA'],
            'cimb' => ['code' => 'cimb', 'name' => 'CIMB'],
        ];

        $supportedVABanks = [];
        $midtransMap = [
            'bca' => 'bca',
            'bni' => 'bni',
            'bri' => 'bri',
            'mandiri' => 'echannel',
            'permata' => 'permata',
            'cimb' => 'cimb',
        ];

        foreach ($rekeningBanks as $rek) {
            $namaAkun = strtolower($rek->nama_akun);
            foreach ($midtransMap as $key => $code) {
                if (str_contains($namaAkun, $key)) {
                    $supportedVABanks[$code] = $defaultMidtransVA[$code];
                }
            }
        }

        // Jika tidak ada mapping bank, gunakan default bank VA Midtrans
        if (empty($supportedVABanks)) {
            $supportedVABanks = $defaultMidtransVA;
        }

        $supportedVABanks = array_values($supportedVABanks);

        return view('pelanggan.payment', compact('carts', 'subtotal', 'ppn', 'ongkir', 'total', 'perusahaan', 'perusahaan_slug', 'rekeningBanks', 'supportedVABanks', 'midtransEnabled'));
    }

    public function processPayment(Request $request, $perusahaan_slug = null)
    {
        \Log::info('CheckoutController::processPayment called', [
            'payment_gateway' => $request->payment_gateway,
            'user_id' => auth()->id(),
        ]);
        $isManualTransfer = $request->payment_gateway === 'transfer' && $request->metode_transfer === 'manual';
        $isMidtransVA = $request->payment_gateway === 'transfer' && $request->metode_transfer === 'midtrans_va';
        
        $request->validate([
            'payment_gateway' => 'required|in:transfer,tunai',
            'metode_transfer' => 'nullable|in:midtrans_va,manual',
            'rekening_id' => $isManualTransfer ? 'required' : 'nullable',
            'bank_va' => $isMidtransVA ? 'required|in:bca,bni,bri,echannel,permata,cimb' : 'nullable',
            'bukti_pembayaran' => $isManualTransfer ? 'required|file|mimes:jpg,jpeg,png,pdf|max:5120' : 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'rekening_id.required' => 'Silakan pilih rekening tujuan transfer.',
            'bank_va.required' => 'Silakan pilih Bank Virtual Account.',
            'bukti_pembayaran.required' => 'Bukti pembayaran wajib diupload untuk transfer manual.',
        ]);

        $checkoutData = session('checkout_data');
        if (!$checkoutData) {
            return redirect()->route('pelanggan.checkout', ['perusahaan_slug' => request()->route('perusahaan_slug')])->with('error', 'Sesi checkout tidak ditemukan. Silakan ulangi checkout.');
        }

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
            
            // Get the company from the route parameter
            $perusahaan_slug = $perusahaan_slug ?? $request->route('perusahaan_slug');
            
            // Try to get from middleware attributes first
            $perusahaan = $request->attributes->get('perusahaan');
            
            if (!$perusahaan) {
                $perusahaan = \App\Models\Perusahaan::withoutGlobalScope('user')->where(function ($q) use ($perusahaan_slug) {
                    $q->where('slug', strtolower($perusahaan_slug))
                      ->orWhere('kode', strtoupper($perusahaan_slug));
                })->first();
            }
            
            // For Midtrans and Manual Transfer, ongkir logic from COD is skipped here, but we should calculate it correctly.
            // Wait, previously ongkir was calculated only if COD! We should calculate it for all methods if it's delivery.
            
            $jenisPengiriman = $checkoutData['jenis_pengiriman'] ?? 'delivery';
            
            $latPengiriman = $checkoutData['latitude_pengiriman'] ?? null;
            $lonPengiriman = $checkoutData['longitude_pengiriman'] ?? null;
            $jarak = null;
            
            if ($jenisPengiriman === 'delivery') {
                if ($perusahaan && $perusahaan->latitude && $perusahaan->longitude && $latPengiriman && $lonPengiriman) {
                    $distance = $this->distanceService->calculateHaversineDistance(
                        $perusahaan->latitude,
                        $perusahaan->longitude,
                        $latPengiriman,
                        $lonPengiriman
                    );
                    
                    $jarak = round($distance, 2);
                    $ongkirSetting = \App\Models\OngkirSetting::withoutGlobalScopes()
                        ->where('user_id', $perusahaan->user_id)
                        ->where('status', true)
                        ->where('jarak_min', '<=', $jarak)
                        ->where(function ($query) use ($jarak) {
                            $query->whereNull('jarak_max')
                                ->orWhere('jarak_max', '>=', $jarak);
                        })
                        ->orderBy('jarak_min', 'desc')
                        ->first();
                        
                    if ($ongkirSetting) {
                        $ongkir = $ongkirSetting->harga_ongkir;
                    }
                } else {
                    return redirect()->back()->with('error', 'Gagal memproses pesanan: Lokasi perusahaan atau pelanggan belum lengkap. Mohon hubungi admin.');
                }
            } else {
                $ongkir = 0;
            }
            
            // Extract actual gateway based on new UI structure
            $actualGateway = $request->payment_gateway;
            if ($actualGateway === 'transfer') {
                $actualGateway = $request->metode_transfer === 'manual' ? 'manual_transfer' : 'midtrans';
            }

            // Determine if ongkir should be 0 (Ambil di Toko)
            if ($actualGateway === 'tunai' && ($checkoutData['jenis_pengiriman'] ?? 'delivery') === 'ambil_di_toko') {
                $ongkir = 0;
            }

            $totalAmount = $subtotal + $ppn + $ongkir;

            // Determine stored method and note based on payment gateway
            $catatanInput = $checkoutData['catatan'] ?? null;
            $bankTujuanTransfer = null;
            
            if ($actualGateway === 'manual_transfer') {
                $storedMethod = 'transfer';
                
                // Retrieve bank info if provided
                if ($request->has('rekening_id')) {
                    $bank = \App\Models\Coa::withoutGlobalScopes()->find($request->rekening_id);
                    if ($bank) {
                        $bankTujuanTransfer = $bank->nama_akun . ' - ' . $bank->nomor_rekening . ' a.n. ' . $bank->atas_nama;
                    }
                }
            } elseif ($actualGateway === 'tunai') {
                $storedMethod = 'tunai';
            } else {
                $storedMethod = null;
            }

            // Handle Bukti Pembayaran Upload
            $buktiPembayaranPath = null;
            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                $filename = time() . '_' . $file->getClientOriginalName();
                $buktiPembayaranPath = $file->storeAs('bukti_pembayaran', $filename, 'public');
            }

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'perusahaan_id' => $perusahaan->user_id,
                'nomor_order' => Order::generateNomorOrder(),
                'subtotal_amount' => $subtotal,
                'ppn_amount' => $ppn,
                'ongkir_amount' => $ongkir,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_method' => $storedMethod,
                'payment_gateway' => $actualGateway,
                'bukti_pembayaran' => $buktiPembayaranPath,
                'bank_tujuan_transfer' => $bankTujuanTransfer,
                'payment_status' => 'pending',
                'jenis_pengiriman' => $jenisPengiriman,
                'nama_penerima' => $checkoutData['nama_penerima'] ?? '',
                'alamat_pengiriman' => $checkoutData['alamat_pengiriman'] ?? '',
                'telepon_penerima' => $checkoutData['telepon_penerima'] ?? '',
                'catatan' => $catatanInput,
                'latitude' => $checkoutData['latitude_pengiriman'] ?? null,
                'longitude' => $checkoutData['longitude_pengiriman'] ?? null,
                'detail_alamat' => $checkoutData['detail_alamat'] ?? null,
                'kelurahan' => $checkoutData['kelurahan'] ?? null,
                'kecamatan' => $checkoutData['kecamatan'] ?? null,
                'kota' => $checkoutData['kota'] ?? null,
                'provinsi' => $checkoutData['provinsi'] ?? null,
                'negara' => $checkoutData['negara'] ?? 'Indonesia',
                'kode_pos' => $checkoutData['kode_pos'] ?? null,
                'company_address' => $perusahaan->alamat,
                'company_latitude' => $perusahaan->latitude,
                'company_longitude' => $perusahaan->longitude,
                'distance_km' => $jarak ?? null,
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

            // Handle payment status based on payment gateway
            \Log::info('CheckoutController: Handling payment gateway', [
                'payment_gateway' => $actualGateway,
                'order_id' => $order->id,
            ]);
            
            if ($actualGateway === 'manual_transfer' || $actualGateway === 'tunai') {
                \Log::info('CheckoutController: Processing transfer manual/tunai payment', ['order_id' => $order->id]);
                // Transfer manual/tunai - pending payment, no Midtrans token
                $order->update([
                    'payment_status' => 'pending',
                    'status' => 'pending',
                ]);
            } else {
                \Log::info('CheckoutController: Processing Midtrans payment', ['order_id' => $order->id]);
                $snapToken = $this->midtrans->createTransaction($order, $order->items, $request->bank_va);
                
                $order->update([
                    'midtrans_order_id' => $order->nomor_order,
                    'snap_token' => $snapToken,
                    'bank_va' => $request->bank_va,
                    'payment_status' => 'pending',
                ]);
            }

            // Clear cart
            Cart::where('user_id', auth()->id())->delete();

            // Create notification
            $notificationMsg = '';
            if ($actualGateway === 'manual_transfer') {
                $notificationMsg = "Pesanan {$order->nomor_order} berhasil dibuat. Silakan transfer ke rekening yang telah ditampilkan.";
            } elseif ($actualGateway === 'tunai') {
                $notificationMsg = "Pesanan {$order->nomor_order} berhasil dibuat secara tunai.";
            } else {
                $notificationMsg = "Pesanan {$order->nomor_order} berhasil dibuat. Silakan selesaikan pembayaran Midtrans.";
            }
            
            Notification::createNotification(
                auth()->id(),
                'order_created',
                'Pesanan Dibuat',
                $notificationMsg,
                ['order_id' => $order->id]
            );

            DB::commit();

            $msg = 'Pesanan berhasil dibuat! Nomor Pesanan: ' . $order->nomor_order;
            if ($actualGateway === 'manual_transfer') {
                $msg = 'Pesanan berhasil dibuat! Bukti pembayaran telah diterima. Nomor Pesanan: ' . $order->nomor_order;
            } elseif ($actualGateway === 'tunai') {
                $msg = 'Pesanan berhasil dibuat dan sedang menunggu persetujuan admin. Nomor Pesanan: ' . $order->nomor_order;
            }

            if ($request->wantsJson()) {
                session()->forget('checkout_data');
                
                if ($actualGateway === 'midtrans') {
                    return response()->json([
                        'success' => true,
                        'snap_token' => $snapToken,
                        'order_id' => $order->id,
                        'redirect_url' => route('pelanggan.orders.show', ['perusahaan_slug' => $perusahaan_slug, 'order' => $order->id])
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'redirect_url' => route('pelanggan.orders.show', ['perusahaan_slug' => $perusahaan_slug, 'order' => $order->id]),
                        'message' => $msg
                    ]);
                }
            }

            if ($actualGateway === 'midtrans') {
                return back()->with(['snap_token' => $snapToken, 'order_id' => $order->id]);
            }

            // Clear checkout session for normal requests
            session()->forget('checkout_data');

            return redirect()->route('pelanggan.orders.show', [
                'perusahaan_slug' => $perusahaan_slug, 
                'order' => $order->id
            ])->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Checkout gagal: ' . $e->getMessage()
                ], 400);
            }
            return back()->with('error', 'Checkout gagal: ' . $e->getMessage());
        }
    }
}

