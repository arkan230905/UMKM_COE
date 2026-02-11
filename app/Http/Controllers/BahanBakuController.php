<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Satuan;
use App\Services\BomSyncService;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    // Menampilkan semua data bahan baku
    public function index()
    {
        $bahanBaku = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->get();
        
        // Hitung harga rata-rata untuk setiap bahan baku
        foreach ($bahanBaku as $bahan) {
            $averageHarga = $this->getAverageHargaSatuan($bahan->id);
            
            // Jika ada harga rata-rata, gunakan itu. Jika tidak, gunakan harga default
            if ($averageHarga > 0) {
                $bahan->harga_satuan_display = $averageHarga;
            } else {
                $bahan->harga_satuan_display = $bahan->harga_satuan;
            }
        }
        
        return view('master-data.bahan-baku.index', compact('bahanBaku'));
    }

    // Menampilkan detail bahan baku
    public function show($id)
    {
        $bahanBaku = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->findOrFail($id);
        
        // Hitung harga rata-rata untuk display
        $averageHarga = $this->getAverageHargaSatuan($bahanBaku->id);
        
        // Jika ada harga rata-rata, gunakan itu. Jika tidak, gunakan harga default
        if ($averageHarga > 0) {
            $bahanBaku->harga_satuan_display = $averageHarga;
        } else {
            $bahanBaku->harga_satuan_display = $bahanBaku->harga_satuan;
        }
        
        return view('master-data.bahan-baku.show', compact('bahanBaku'));
    }

    // Menampilkan form tambah data
    public function create()
    {
        $satuans = Satuan::all();
        return view('master-data.bahan-baku.create', compact('satuans'));
    }

    // Simpan data baru ke database
    public function store(Request $request)
    {
        // Convert comma decimal inputs to dot format for validation and storage
        $this->convertCommaToDecimal($request);
        
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'satuan_id' => 'required|exists:satuans,id',
            'stok' => 'nullable|numeric|min:0',
            'harga_satuan' => 'required|numeric|min:0',
            'kode_bahan' => 'nullable|string|max:50|unique:bahan_bakus,kode_bahan',
            'stok_minimum' => 'nullable|numeric|min:0',
            'deskripsi' => 'nullable|string|max:1000',
            'sub_satuan_1_id' => 'required|exists:satuans,id',
            'sub_satuan_1_konversi' => 'required|numeric|min:0.01',
            'sub_satuan_1_nilai' => 'required|numeric|min:0.01',
            'sub_satuan_2_id' => 'required|exists:satuans,id',
            'sub_satuan_2_konversi' => 'required|numeric|min:0.01',
            'sub_satuan_2_nilai' => 'required|numeric|min:0.01',
            'sub_satuan_3_id' => 'required|exists:satuans,id',
            'sub_satuan_3_konversi' => 'required|numeric|min:0.01',
            'sub_satuan_3_nilai' => 'required|numeric|min:0.01',
        ]);

        // Auto generate kode_bahan if not provided
        $kodeBahan = $request->kode_bahan;
        if (empty($kodeBahan)) {
            $lastBahan = BahanBaku::orderBy('id', 'desc')->first();
            $nextNumber = $lastBahan ? $lastBahan->id + 1 : 1;
            $kodeBahan = 'BB' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
        
        BahanBaku::create([
            'nama_bahan' => $request->nama_bahan,
            'kode_bahan' => $kodeBahan,
            'satuan_id' => $request->satuan_id,
            'stok' => $request->stok ?? 0,
            'harga_satuan' => $request->harga_satuan,
            'stok_minimum' => $request->stok_minimum ?? 0,
            'deskripsi' => $request->deskripsi,
            'sub_satuan_1_id' => $request->sub_satuan_1_id,
            'sub_satuan_1_konversi' => $request->sub_satuan_1_konversi,
            'sub_satuan_1_nilai' => $request->sub_satuan_1_nilai,
            'sub_satuan_2_id' => $request->sub_satuan_2_id,
            'sub_satuan_2_konversi' => $request->sub_satuan_2_konversi,
            'sub_satuan_2_nilai' => $request->sub_satuan_2_nilai,
            'sub_satuan_3_id' => $request->sub_satuan_3_id,
            'sub_satuan_3_konversi' => $request->sub_satuan_3_konversi,
            'sub_satuan_3_nilai' => $request->sub_satuan_3_nilai,
        ]);

        return redirect()->route('master-data.bahan-baku.index')->with('success', 'Data bahan baku berhasil ditambahkan!');
    }

    // Menampilkan form edit
    public function edit($id)
    {
        $bahanBaku = BahanBaku::with('satuan')->findOrFail($id);
        $satuans = Satuan::all();
        return view('master-data.bahan-baku.edit', compact('bahanBaku', 'satuans'));
    }

    // Update data
    public function update(Request $request, $id)
    {
        // Convert comma decimal inputs to dot format for validation and storage
        $this->convertCommaToDecimal($request);
        
        $validatedData = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'satuan_id' => 'required|exists:satuans,id',
            'stok' => 'nullable|numeric|min:0',
            'harga_satuan' => 'required|numeric|min:0',
            'stok_minimum' => 'nullable|numeric|min:0',
            'deskripsi' => 'nullable|string|max:1000',
            'sub_satuan_1_id' => 'required|exists:satuans,id',
            'sub_satuan_1_konversi' => 'required|numeric|min:0.01',
            'sub_satuan_1_nilai' => 'required|numeric|min:0.01',
            'sub_satuan_2_id' => 'required|exists:satuans,id',
            'sub_satuan_2_konversi' => 'required|numeric|min:0.01',
            'sub_satuan_2_nilai' => 'required|numeric|min:0.01',
            'sub_satuan_3_id' => 'required|exists:satuans,id',
            'sub_satuan_3_konversi' => 'required|numeric|min:0.01',
            'sub_satuan_3_nilai' => 'required|numeric|min:0.01',
        ]);

        $bahanBaku = BahanBaku::findOrFail($id);
        
        // Update properties one by one and save
        $bahanBaku->nama_bahan = $request->nama_bahan;
        $bahanBaku->satuan_id = $request->satuan_id;
        $bahanBaku->stok = $request->stok ?? 0;
        $bahanBaku->harga_satuan = $request->harga_satuan;
        $bahanBaku->stok_minimum = $request->stok_minimum ?? 0;
        $bahanBaku->deskripsi = $request->deskripsi;
        $bahanBaku->sub_satuan_1_id = $request->sub_satuan_1_id;
        $bahanBaku->sub_satuan_1_konversi = $request->sub_satuan_1_konversi;
        $bahanBaku->sub_satuan_1_nilai = $request->sub_satuan_1_nilai;
        $bahanBaku->sub_satuan_2_id = $request->sub_satuan_2_id;
        $bahanBaku->sub_satuan_2_konversi = $request->sub_satuan_2_konversi;
        $bahanBaku->sub_satuan_2_nilai = $request->sub_satuan_2_nilai;
        $bahanBaku->sub_satuan_3_id = $request->sub_satuan_3_id;
        $bahanBaku->sub_satuan_3_konversi = $request->sub_satuan_3_konversi;
        $bahanBaku->sub_satuan_3_nilai = $request->sub_satuan_3_nilai;
        
        // Save changes
        $bahanBaku->save();

        // Sync BOM when bahan baku price changes
        BomSyncService::syncBomFromMaterialChange('bahan_baku', $bahanBaku->id);

        return redirect()->route('master-data.bahan-baku.index')->with('success', 'Data bahan baku berhasil diperbarui!');
    }

    // Hapus data
    public function destroy($id)
    {
        try {
            $bahanBaku = BahanBaku::findOrFail($id);
            
            // Check for foreign key constraints before deleting
            $constraints = [];
            
            // Check BOM Job BBB references
            $bomJobBbbCount = \DB::table('bom_job_bbb')->where('bahan_baku_id', $id)->count();
            if ($bomJobBbbCount > 0) {
                $constraints[] = "BOM Job Costing ({$bomJobBbbCount} record(s))";
            }
            
            // Check BOM Details references
            $bomDetailsCount = \DB::table('bom_details')->where('bahan_baku_id', $id)->count();
            if ($bomDetailsCount > 0) {
                $constraints[] = "BOM Details ({$bomDetailsCount} record(s))";
            }
            
            // Check Pembelian Details references
            $pembelianDetailsCount = \DB::table('pembelian_details')->where('bahan_baku_id', $id)->count();
            if ($pembelianDetailsCount > 0) {
                $constraints[] = "Pembelian Details ({$pembelianDetailsCount} record(s))";
            }
            
            // Check Produksi Details references
            $produksiDetailsCount = \DB::table('produksi_details')->where('bahan_baku_id', $id)->count();
            if ($produksiDetailsCount > 0) {
                $constraints[] = "Produksi Details ({$produksiDetailsCount} record(s))";
            }
            
            // If there are constraints, prevent deletion and show error
            if (!empty($constraints)) {
                $constraintList = implode(', ', $constraints);
                return redirect()->route('master-data.bahan-baku.index')
                    ->with('error', "Tidak dapat menghapus bahan baku '{$bahanBaku->nama_bahan}' karena masih digunakan di: {$constraintList}. Hapus data terkait terlebih dahulu.");
            }
            
            // If no constraints, proceed with deletion
            $bahanBaku->delete();
            
            return redirect()->route('master-data.bahan-baku.index')
                ->with('success', "Data bahan baku '{$bahanBaku->nama_bahan}' berhasil dihapus!");
                
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle any other database constraint errors
            if ($e->getCode() == '23000') {
                return redirect()->route('master-data.bahan-baku.index')
                    ->with('error', 'Tidak dapat menghapus data karena masih digunakan di tabel lain. Hapus data terkait terlebih dahulu.');
            }
            
            return redirect()->route('master-data.bahan-baku.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
                
        } catch (\Exception $e) {
            return redirect()->route('master-data.bahan-baku.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get average harga satuan untuk bahan baku
     */
    public function getAverageHargaSatuan($bahanBakuId)
    {
        $bahanBaku = BahanBaku::findOrFail($bahanBakuId);
        
        // Ambil semua pembelian detail untuk bahan baku ini
        $details = \App\Models\PembelianDetail::where('bahan_baku_id', $bahanBakuId)
            ->with(['pembelian'])
            ->get();
        
        if ($details->isEmpty()) {
            return 0;
        }
        
        // Hitung total harga dan total quantity
        $totalHarga = 0;
        $totalQuantity = 0;
        
        foreach ($details as $detail) {
            $totalHarga += ($detail->harga_satuan ?? 0) * ($detail->jumlah ?? 0);
            $totalQuantity += ($detail->jumlah ?? 0);
        }
        
        // Hitung harga rata-rata
        $averageHarga = $totalQuantity > 0 ? $totalHarga / $totalQuantity : 0;
        
        return $averageHarga;
    }

    /**
     * Convert comma decimal inputs to dot format for proper validation and storage
     */
    private function convertCommaToDecimal(Request $request)
    {
        $fieldsToConvert = [
            'harga_satuan', 'stok', 'stok_minimum',
            'sub_satuan_1_konversi', 'sub_satuan_1_nilai',
            'sub_satuan_2_konversi', 'sub_satuan_2_nilai', 
            'sub_satuan_3_konversi', 'sub_satuan_3_nilai'
        ];
        
        foreach ($fieldsToConvert as $field) {
            if ($request->has($field) && $request->input($field) !== null) {
                $value = $request->input($field);
                $convertedValue = $this->smartNumberConversion($value);
                $request->merge([$field => $convertedValue]);
            }
        }
    }
    
    /**
     * Smart number conversion that handles various Indonesian number formats
     */
    private function smartNumberConversion($value)
    {
        // Remove any spaces
        $value = trim($value);
        
        // If empty, return as is
        if (empty($value)) {
            return $value;
        }
        
        // Count dots and commas to determine format
        $dotCount = substr_count($value, '.');
        $commaCount = substr_count($value, ',');
        
        // If no dots or commas, it's already a clean number
        if ($dotCount === 0 && $commaCount === 0) {
            return $value;
        }
        
        // Find positions of last dot and comma
        $lastDotPos = strrpos($value, '.');
        $lastCommaPos = strrpos($value, ',');
        
        // Case 1: Only commas (Indonesian decimal: 1,5 or 1000,50)
        if ($commaCount > 0 && $dotCount === 0) {
            return str_replace(',', '.', $value);
        }
        
        // Case 2: Only dots
        if ($dotCount > 0 && $commaCount === 0) {
            // If multiple dots, treat all but last as thousand separators
            if ($dotCount > 1) {
                $parts = explode('.', $value);
                $lastPart = array_pop($parts);
                // If last part has 3 digits, it's likely a thousand separator (e.g., 1.000.000)
                if (strlen($lastPart) === 3 && ctype_digit($lastPart)) {
                    return implode('', array_merge($parts, [$lastPart]));
                } else {
                    // Last part is decimal (e.g., 1.000.50)
                    return implode('', $parts) . '.' . $lastPart;
                }
            } else {
                // Single dot - check if it's thousand separator or decimal
                $parts = explode('.', $value);
                if (count($parts) === 2) {
                    $beforeDot = $parts[0];
                    $afterDot = $parts[1];
                    
                    // If after dot has exactly 3 digits and all are digits, and before dot is 1-3 digits
                    // it's likely a thousand separator (e.g., 1.000, 15.000)
                    if (strlen($afterDot) === 3 && ctype_digit($afterDot) && 
                        strlen($beforeDot) >= 1 && strlen($beforeDot) <= 3 && ctype_digit($beforeDot)) {
                        return $beforeDot . $afterDot;
                    } else {
                        // Otherwise it's a decimal separator (e.g., 2.5, 50.75)
                        return $value;
                    }
                } else {
                    return $value;
                }
            }
        }
        
        // Case 3: Both dots and commas
        if ($dotCount > 0 && $commaCount > 0) {
            // Determine which is the decimal separator based on position
            if ($lastCommaPos > $lastDotPos) {
                // Comma is decimal separator (e.g., 1.000,50)
                $integerPart = substr($value, 0, $lastCommaPos);
                $decimalPart = substr($value, $lastCommaPos + 1);
                $integerPart = str_replace('.', '', $integerPart); // Remove thousand separators
                return $integerPart . '.' . $decimalPart;
            } else {
                // Dot is decimal separator (e.g., 1,000.50)
                $integerPart = substr($value, 0, $lastDotPos);
                $decimalPart = substr($value, $lastDotPos + 1);
                $integerPart = str_replace(',', '', $integerPart); // Remove thousand separators
                return $integerPart . '.' . $decimalPart;
            }
        }
        
        // Fallback: return original value
        return $value;
    }
}
