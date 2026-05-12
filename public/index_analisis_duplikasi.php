<?php
/**
 * Index untuk semua tools analisis duplikasi pembayaran beban
 * Akses: http://localhost/index_analisis_duplikasi.php
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tools Analisis Duplikasi Pembayaran Beban</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container { max-width: 1000px; margin: 0 auto; }
        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .tool-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        .tool-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        .tool-card h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 20px;
        }
        .tool-card .badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .tool-card .badge.recommended {
            background: #ff6b6b;
        }
        .tool-card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .tool-card .features {
            background: #f5f5f5;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 13px;
        }
        .tool-card .features li {
            margin-left: 20px;
            margin-bottom: 5px;
            color: #555;
        }
        .tool-card a {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }
        .tool-card a:hover {
            background: #764ba2;
        }
        .tool-card a.secondary {
            background: #999;
            margin-left: 10px;
        }
        .tool-card a.secondary:hover {
            background: #777;
        }
        .docs-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .docs-section h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .docs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        .doc-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .doc-item h4 {
            color: #667eea;
            margin-bottom: 8px;
        }
        .doc-item p {
            color: #666;
            font-size: 13px;
            line-height: 1.5;
        }
        .doc-item a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        .doc-item a:hover {
            text-decoration: underline;
        }
        .quick-start {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .quick-start h3 {
            color: #856404;
            margin-bottom: 10px;
        }
        .quick-start ol {
            margin-left: 20px;
            color: #856404;
        }
        .quick-start li {
            margin-bottom: 8px;
        }
        .quick-start a {
            color: #667eea;
            font-weight: bold;
            text-decoration: none;
        }
        .quick-start a:hover {
            text-decoration: underline;
        }
        .footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            opacity: 0.8;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <h1>🔍 Tools Analisis Duplikasi Pembayaran Beban</h1>
        <p>Analisis journal entries pembayaran beban pada tanggal 28-29 April 2026</p>
    </div>

    <!-- Quick Start -->
    <div class="quick-start">
        <h3>⚡ Quick Start</h3>
        <ol>
            <li>Klik <strong>"Analisis Duplikasi"</strong> untuk melihat hasil analisis</li>
            <li>Jika ada duplikasi, ikuti rekomendasi cleanup</li>
            <li>Verifikasi hasil setelah cleanup</li>
        </ol>
    </div>

    <!-- Tools Grid -->
    <div class="tools-grid">
        <!-- Tool 1: Main Analysis -->
        <div class="tool-card">
            <span class="badge recommended">⭐ REKOMENDASI</span>
            <h3>📊 Analisis Duplikasi</h3>
            <p>Analisis lengkap duplikasi journal entries pembayaran beban dengan tampilan HTML yang rapi.</p>
            <ul class="features">
                <li>✓ Lihat semua entries</li>
                <li>✓ Detail lines setiap entry</li>
                <li>✓ Deteksi duplikasi otomatis</li>
                <li>✓ Summary statistik</li>
            </ul>
            <a href="debug_pembayaran_beban.php">Buka Analisis</a>
        </div>

        <!-- Tool 2: Table Structure -->
        <div class="tool-card">
            <span class="badge">📋 INFO</span>
            <h3>📐 Struktur Tabel</h3>
            <p>Lihat struktur tabel journal_entries dan journal_lines, sample data, dan statistik database.</p>
            <ul class="features">
                <li>✓ Struktur tabel</li>
                <li>✓ Sample data</li>
                <li>✓ Statistik database</li>
                <li>✓ Ref types distribution</li>
            </ul>
            <a href="check_table_structure_pembayaran.php">Lihat Struktur</a>
        </div>

        <!-- Tool 3: SQL Queries -->
        <div class="tool-card">
            <span class="badge">🔧 MANUAL</span>
            <h3>💾 Query SQL</h3>
            <p>File SQL dengan 5 query untuk analisis manual di MySQL client.</p>
            <ul class="features">
                <li>✓ Query entries</li>
                <li>✓ Query detail lines</li>
                <li>✓ Query duplikasi</li>
                <li>✓ Query nominal sama</li>
            </ul>
            <a href="../query_pembayaran_beban.sql" download>Download SQL</a>
        </div>

        <!-- Tool 4: Documentation -->
        <div class="tool-card">
            <span class="badge">📖 DOCS</span>
            <h3>📚 Dokumentasi</h3>
            <p>Panduan lengkap cara menggunakan tools, interpretasi hasil, dan cara cleanup.</p>
            <ul class="features">
                <li>✓ Panduan lengkap</li>
                <li>✓ Interpretasi hasil</li>
                <li>✓ Cara cleanup</li>
                <li>✓ Troubleshooting</li>
            </ul>
            <a href="../PANDUAN_ANALISIS_DUPLIKASI_PEMBAYARAN_BEBAN.md" target="_blank">Baca Panduan</a>
        </div>

        <!-- Tool 5: Summary -->
        <div class="tool-card">
            <span class="badge">📝 RINGKASAN</span>
            <h3>📄 Ringkasan</h3>
            <p>Ringkasan lengkap semua tools, cara penggunaan, dan best practices.</p>
            <ul class="features">
                <li>✓ Daftar file</li>
                <li>✓ Cara penggunaan</li>
                <li>✓ Best practices</li>
                <li>✓ Rekomendasi</li>
            </ul>
            <a href="../RINGKASAN_ANALISIS_DUPLIKASI.md" target="_blank">Baca Ringkasan</a>
        </div>

        <!-- Tool 6: Examples -->
        <div class="tool-card">
            <span class="badge">💡 CONTOH</span>
            <h3>🎯 Contoh Output</h3>
            <p>Contoh output analisis untuk berbagai skenario (clean, duplikasi, multiple duplikasi).</p>
            <ul class="features">
                <li>✓ Skenario clean</li>
                <li>✓ Skenario duplikasi</li>
                <li>✓ Multiple duplikasi</li>
                <li>✓ Rekomendasi cleanup</li>
            </ul>
            <a href="../CONTOH_OUTPUT_ANALISIS.md" target="_blank">Lihat Contoh</a>
        </div>
    </div>

    <!-- Documentation Section -->
    <div class="docs-section">
        <h2>📚 Dokumentasi & Resources</h2>
        <div class="docs-grid">
            <div class="doc-item">
                <h4>🚀 Mulai Cepat</h4>
                <p>Panduan step-by-step untuk memulai analisis dalam 5 menit.</p>
                <a href="../PANDUAN_ANALISIS_DUPLIKASI_PEMBAYARAN_BEBAN.md#cara-mengakses-analisis" target="_blank">Baca →</a>
            </div>
            <div class="doc-item">
                <h4>🔍 Cara Kerja</h4>
                <p>Penjelasan detail tentang analisis yang dilakukan dan kriteria duplikasi.</p>
                <a href="../RINGKASAN_ANALISIS_DUPLIKASI.md#-analisis-yang-dilakukan" target="_blank">Baca →</a>
            </div>
            <div class="doc-item">
                <h4>✅ Interpretasi Hasil</h4>
                <p>Cara membaca dan menginterpretasi hasil analisis dengan benar.</p>
                <a href="../RINGKASAN_ANALISIS_DUPLIKASI.md#-interpretasi-hasil" target="_blank">Baca →</a>
            </div>
            <div class="doc-item">
                <h4>🛠️ Cleanup Guide</h4>
                <p>Panduan step-by-step untuk membersihkan duplikasi dengan aman.</p>
                <a href="../RINGKASAN_ANALISIS_DUPLIKASI.md#-cara-membersihkan-duplikasi" target="_blank">Baca →</a>
            </div>
            <div class="doc-item">
                <h4>🐛 Troubleshooting</h4>
                <p>Solusi untuk masalah umum yang mungkin terjadi.</p>
                <a href="../RINGKASAN_ANALISIS_DUPLIKASI.md#-troubleshooting" target="_blank">Baca →</a>
            </div>
            <div class="doc-item">
                <h4>💡 Best Practices</h4>
                <p>Rekomendasi best practice untuk menjaga data tetap clean.</p>
                <a href="../RINGKASAN_ANALISIS_DUPLIKASI.md#-rekomendasi-best-practice" target="_blank">Baca →</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Tools Analisis Duplikasi Pembayaran Beban v1.0 | Dibuat: 2026-04-29</p>
    </div>
</div>

</body>
</html>
