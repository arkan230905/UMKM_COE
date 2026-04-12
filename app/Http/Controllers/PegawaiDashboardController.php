<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Pegawai;
use App\Models\Penggajian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PegawaiDashboardController extends Controller
{
    /**
     * Display the pegawai dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('login')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        // Get today's attendance
        $todayAttendance = Presensi::where('pegawai_id', $pegawai->kode_pegawai)
            ->whereDate('tgl_presensi', now())
            ->first();

        // Get this month attendance summary
        $thisMonthAttendance = Presensi::where('pegawai_id', $pegawai->kode_pegawai)
            ->whereMonth('tgl_presensi', now()->month)
            ->whereYear('tgl_presensi', now()->year)
            ->get();

        $stats = [
            'total_hadir' => $thisMonthAttendance->where('status', 'hadir')->count(),
            'total_hari_kerja' => now()->daysInMonth,
            'persentasi_kehadiran' => $thisMonthAttendance->count() > 0 
                ? round(($thisMonthAttendance->where('status', 'hadir')->count() / $thisMonthAttendance->count()) * 100, 1)
                : 0,
            'today_status' => $todayAttendance ? [
                'jam_masuk' => $todayAttendance->jam_masuk,
                'jam_keluar' => $todayAttendance->jam_keluar,
                'status' => $todayAttendance->status,
                'sudah_lengkap' => !empty($todayAttendance->jam_keluar)
            ] : null
        ];

        // Get recent attendance (last 7 days)
        $recentAttendance = Presensi::where('pegawai_id', $pegawai->kode_pegawai)
            ->whereDate('tgl_presensi', '>=', now()->subDays(7))
            ->orderBy('tgl_presensi', 'desc')
            ->get();

        return view('pegawai.dashboard', compact('pegawai', 'stats', 'todayAttendance', 'recentAttendance'));
    }

    /**
     * Display riwayat presensi pegawai.
     */
    public function riwayatPresensi(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('login')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        $query = Presensi::where('pegawai_id', $pegawai->id);
        
        // Filter by month/year if provided
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('tgl_presensi', $request->month)
                  ->whereYear('tgl_presensi', $request->year);
        }
        
        $attendances = $query->orderBy('tgl_presensi', 'desc')->paginate(20);
        
        return view('pegawai.riwayat-presensi', compact('pegawai', 'attendances'));
    }

    /**
     * Display rekap harian presensi (semua pegawai yang hadir pada tanggal tertentu).
     */
    public function rekapHarian(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('login')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        // Default tanggal adalah hari ini
        $tanggal = $request->get('tanggal', now()->toDateString());
        
        // Ambil semua presensi pada tanggal tersebut
        $attendances = Presensi::with('pegawai')
            ->whereDate('tgl_presensi', $tanggal)
            ->orderBy('jam_masuk', 'asc')
            ->get();
        
        return view('pegawai.rekap-harian', compact('pegawai', 'attendances', 'tanggal'));
    }

    /**
     * Display daftar slip gaji pegawai.
     */
    public function slipGajiIndex(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('login')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        // Query penggajian milik pegawai login
        $query = Penggajian::with('pegawai')
            ->where('pegawai_id', $pegawai->id)
            ->whereIn('status_pembayaran', ['disetujui', 'lunas']);

        // Filter by month/year if provided
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('tanggal_penggajian', $request->month)
                  ->whereYear('tanggal_penggajian', $request->year);
        }

        $penggajians = $query->orderBy('tanggal_penggajian', 'desc')->paginate(10);

        return view('pegawai.slip-gaji.index', compact('pegawai', 'penggajians'));
    }

    /**
     * Display detail slip gaji.
     */
    public function slipGajiShow($id)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('login')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        // Ambil penggajian dan pastikan milik pegawai login
        $penggajian = Penggajian::with('pegawai')->findOrFail($id);

        // Security: cek apakah penggajian milik pegawai login
        if ($penggajian->pegawai_id !== $pegawai->id) {
            abort(403, 'Anda tidak memiliki akses ke slip gaji ini.');
        }

        // Cek status penggajian - hanya tampilkan yang sudah disetujui/dibayar
        if (!in_array($penggajian->status_pembayaran, ['disetujui', 'lunas'])) {
            abort(403, 'Slip gaji belum tersedia. Penggajian ini belum disetujui atau dibayar.');
        }

        // Hitung komponen gaji
        $jenis = strtolower($pegawai->jenis_pegawai ?? 'btktl');

        if ($jenis === 'btkl') {
            $gajiDasar = (float)($penggajian->tarif_per_jam ?? 0) * (float)($penggajian->total_jam_kerja ?? 0);
        } else {
            $gajiDasar = (float)($penggajian->gaji_pokok ?? 0);
        }

        $totalGajiHitung = $gajiDasar
            + (float)($penggajian->tunjangan ?? 0)
            + (float)($penggajian->asuransi ?? 0)
            + (float)($penggajian->bonus ?? 0)
            - (float)($penggajian->potongan ?? 0);

        return view('pegawai.slip-gaji.show', compact('pegawai', 'penggajian', 'gajiDasar', 'totalGajiHitung', 'jenis'));
    }

    /**
     * Generate PDF slip gaji.
     */
    public function slipGajiPdf($id)
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('login')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        // Ambil penggajian dan pastikan milik pegawai login
        $penggajian = Penggajian::with('pegawai')->findOrFail($id);

        // Security: cek apakah penggajian milik pegawai login
        if ($penggajian->pegawai_id !== $pegawai->id) {
            abort(403, 'Anda tidak memiliki akses ke slip gaji ini.');
        }

        // Cek status penggajian
        if (!in_array($penggajian->status_pembayaran, ['disetujui', 'lunas'])) {
            abort(403, 'Slip gaji belum tersedia. Penggajian ini belum disetujui atau dibayar.');
        }

        // Hitung komponen gaji
        $jenis = strtolower($pegawai->jenis_pegawai ?? 'btktl');

        if ($jenis === 'btkl') {
            $gajiDasar = (float)($penggajian->tarif_per_jam ?? 0) * (float)($penggajian->total_jam_kerja ?? 0);
        } else {
            $gajiDasar = (float)($penggajian->gaji_pokok ?? 0);
        }

        $totalGajiHitung = $gajiDasar
            + (float)($penggajian->tunjangan ?? 0)
            + (float)($penggajian->asuransi ?? 0)
            + (float)($penggajian->bonus ?? 0)
            - (float)($penggajian->potongan ?? 0);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pegawai.slip-gaji.pdf', compact('pegawai', 'penggajian', 'gajiDasar', 'totalGajiHitung', 'jenis'));
        
        $filename = 'slip-gaji-' . $pegawai->nama . '-' . $penggajian->tanggal_penggajian->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}
