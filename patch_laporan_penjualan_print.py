import re

with open('resources/views/laporan/penjualan/index.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace Export PDF link with window.print() button
target_link = """            <a href="{{ route('laporan.penjualan.export') }}" class="btn btn-danger">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>"""
replacement_button = """            <button type="button" onclick="window.print()" class="btn btn-danger no-print">
                <i class="fas fa-print me-1"></i> Cetak PDF
            </button>"""
content = content.replace(target_link, replacement_button)

# Add no-print class to the Filter Form card (we need to be careful to just target the filter form)
# Let's replace class="card mb-4" for the form with class="card mb-4 no-print"
# We know the form has:
#     <!-- Filter Form -->
#     <div class="card mb-4">
target_filter = """    <!-- Filter Form -->
    <div class="card mb-4">"""
replacement_filter = """    <!-- Filter Form -->
    <div class="card mb-4 no-print">"""
content = content.replace(target_filter, replacement_filter)

# Add no-print class to the nav-tabs
target_nav = """    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="laporanTabs" role="tablist">"""
replacement_nav = """    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4 no-print" id="laporanTabs" role="tablist">"""
content = content.replace(target_nav, replacement_nav)

# Add CSS for @media print
css_addition = """
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            background: white !important;
        }
        .container {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        .table th, .table td {
            border: 1px solid #ddd !important;
            padding: 8px !important;
        }
        /* Ensure the active tab prints properly and inactive hides */
        .tab-pane:not(.active) {
            display: none !important;
        }
        .tab-pane.active {
            display: block !important;
            opacity: 1 !important;
        }
    }
</style>
"""

# Append CSS at the end of the content
if "@media print {" not in content:
    content = content.replace("@endsection", css_addition + "\n@endsection")

with open('resources/views/laporan/penjualan/index.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("patched laporan penjualan index for printing")
