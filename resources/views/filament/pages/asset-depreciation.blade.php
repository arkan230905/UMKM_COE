<x-filament::page>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-semibold">{{ $this->record->nama_aset }}</h2>
            <div class="mt-2 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-600">
                <div>
                    <div class="text-gray-500">Nilai Perolehan</div>
                    <div class="font-medium">Rp {{ number_format((float) $this->record->harga_perolehan, 2, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Nilai Sisa</div>
                    <div class="font-medium">Rp {{ number_format((float) $this->record->nilai_sisa, 2, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Umur Ekonomis</div>
                    <div class="font-medium">{{ $this->record->umur_ekonomis }} tahun</div>
                </div>
                <div>
                    <div class="text-gray-500">Tanggal Perolehan</div>
                    <div class="font-medium">{{ \\Carbon\\Carbon::parse($this->record->tanggal_perolehan)->translatedFormat('d M Y') }}</div>
                </div>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament::page>
