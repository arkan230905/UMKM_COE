<div class="table-responsive">
  <table class="table table-dark table-striped table-hover align-middle">
    <thead>
      <tr>
        <th>Periode</th>
        <th>Beban Penyusutan</th>
        <th>Akumulasi</th>
        <th>Nilai Buku</th>
      </tr>
    </thead>
    <tbody>
      @forelse(($rows ?? []) as $r)
        <tr>
          <td>{{ $r['period'] ?? '-' }}</td>
          <td>Rp {{ number_format($r['depreciation'] ?? 0,0,',','.') }}</td>
          <td>Rp {{ number_format($r['accumulated'] ?? 0,0,',','.') }}</td>
          <td>Rp {{ number_format($r['book_value'] ?? 0,0,',','.') }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-center">Data tidak tersedia (lengkapi data aset terlebih dahulu).</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
