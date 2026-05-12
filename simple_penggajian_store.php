<?php
/**
 * Simplified penggajian store method that only uses existing table columns
 */

// This is the simplified store method content that should replace the existing one

public function store(Request $request)
{
    // Mulai transaksi database
    DB::beginTransaction();

    try {
        // Validasi input dasar
        $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tanggal_penggajian' => 'required|date',
            'bonus' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'gaji_pokok' => 'nullable|numeric|min:0',
            'total_jam_kerja' => 'nullable|numeric|min:0',
        ]);

        $pegawai = Pegawai::findOrFail($request->pegawai_id);

        // Data dari form
        $gajiPokok = (float) ($request->gaji_pokok ?? 0);
        $tunjangan = (float) ($request->tunjangan ?? 0);
        $bonus = (float) ($request->bonus ?? 0);
        $potongan = (float) ($request->potongan ?? 0);
        $totalJamKerja = (float) ($request->total_jam_kerja ?? 0);

        // Hitung total gaji sederhana
        $totalGaji = $gajiPokok + $tunjangan + $bonus - $potongan;

        // Simpan ke tabel penggajian dengan field yang ada saja
        $penggajian = new Penggajian([
            'pegawai_id' => $pegawai->id,
            'tanggal_penggajian' => $request->tanggal_penggajian,
            'gaji_pokok' => $gajiPokok,
            'tunjangan' => $tunjangan,
            'potongan' => $potongan,
            'total_jam_kerja' => $totalJamKerja,
            'total_gaji' => $totalGaji,
        ]);

        if (!$penggajian->save()) {
            throw new \Exception('Gagal menyimpan data penggajian ke database');
        }

        \Log::info('Data penggajian berhasil disimpan', [
            'penggajian_id' => $penggajian->id,
            'total_gaji' => $totalGaji,
        ]);

        // Commit transaksi
        DB::commit();

        return redirect()->route('transaksi.penggajian.index')
            ->with('success', 'Data penggajian berhasil ditambahkan!');

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return back()->withErrors($e->errors())->withInput();
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error in PenggajianController@store: ' . $e->getMessage());

        return back()->withErrors(['error' => 'Gagal menyimpan penggajian: ' . $e->getMessage()])->withInput();
    }
}