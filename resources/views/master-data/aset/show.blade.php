@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
  <div class="mb-6">
    <h1 class="text-2xl font-semibold mb-2">Detail Aset</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
      <div>
        <div class="text-gray-500">Kode Aset</div>
        <div class="font-medium">{{ $aset->kode_aset }}</div>
      </div>
      <div>
        <div class="text-gray-500">Nama Aset</div>
        <div class="font-medium">{{ $aset->nama_aset }}</div>
      </div>
      <div>
        <div class="text-gray-500">Kategori</div>
        <div class="font-medium">{{ optional($aset->kategori)->nama ?? '-' }}</div>
      </div>
      <div>
        <div class="text-gray-500">Harga Perolehan</div>
        <div class="font-medium">Rp {{ number_format((float) $aset->harga_perolehan, 2, ',', '.') }}</div>
      </div>
      <div>
        <div class="text-gray-500">Biaya Perolehan</div>
        <div class="font-medium">Rp {{ number_format((float) ($aset->biaya_perolehan ?? 0), 2, ',', '.') }}</div>
      </div>
      <div>
        <div class="text-gray-500">Total Perolehan</div>
        <div class="font-medium">Rp {{ number_format(((float) $aset->harga_perolehan + (float) ($aset->biaya_perolehan ?? 0)), 2, ',', '.') }}</div>
      </div>
      <div>
        <div class="text-gray-500">Nilai Residu</div>
        <div class="font-medium">Rp {{ number_format((float) ($aset->nilai_residu ?? $aset->nilai_sisa ?? 0), 2, ',', '.') }}</div>
      </div>
      <div>
        <div class="text-gray-500">Umur Manfaat</div>
        <div class="font-medium">{{ $aset->umur_manfaat ?? $aset->umur_ekonomis_tahun }} tahun</div>
      </div>
      <div>
        <div class="text-gray-500">Tanggal Akuisisi</div>
        <div class="font-medium">{{ \Carbon\Carbon::parse($aset->tanggal_akuisisi ?? $aset->tanggal_beli)->translatedFormat('d M Y') }}</div>
      </div>
    </div>
  </div>

  <div class="bg-white shadow rounded-md overflow-hidden">
    <div class="px-4 py-3 border-b font-medium text-gray-800">Penyusutan (Metode Garis Lurus)</div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tahun</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Beban Penyusutan</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Akumulasi Penyusutan</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nilai Buku Akhir</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 text-gray-800">
          @forelse($depreciationSchedule as $row)
            <tr>
              <td class="px-4 py-2 text-gray-800">{{ $row['tahun'] }}</td>
              <td class="px-4 py-2 text-gray-800">Rp {{ number_format((float) ($row['biaya_penyusutan'] ?? 0), 2, ',', '.') }}</td>
              <td class="px-4 py-2 text-gray-800">Rp {{ number_format((float) ($row['akumulasi_penyusutan'] ?? 0), 2, ',', '.') }}</td>
              <td class="px-4 py-2 text-gray-800">Rp {{ number_format((float) ($row['nilai_buku'] ?? 0), 2, ',', '.') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada jadwal penyusutan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-6">
    <a href="{{ route('master-data.aset.index') }}" class="inline-flex items-center px-4 py-2 rounded bg-gray-100 hover:bg-gray-200 text-gray-700">Kembali</a>
  </div>
</div>
@endsection
