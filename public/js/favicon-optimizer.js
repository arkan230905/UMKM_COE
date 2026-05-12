/**
 * Favicon Optimizer untuk Logo Asli - FORCE OVERRIDE
 * Memastikan logo.png ditampilkan dan MENGHAPUS favicon Laravel
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🎨 MEMAKSA logo asli mengganti favicon Laravel...');
    
    // Fungsi untuk MENGHAPUS semua favicon dan MEMAKSA logo asli
    function forceReplaceWithOriginalLogo() {
        // HAPUS SEMUA favicon yang ada (termasuk Laravel)
        const allFavicons = document.querySelectorAll('link[rel*="icon"]');
        allFavicons.forEach(link => {
            console.log('🗑️ Menghapus favicon:', link.href);
            link.remove();
        });
        
        // PAKSA gunakan logo asli dengan prioritas TERTINGGI
        const faviconConfigs = [
            { sizes: '128x128', priority: 'highest' },
            { sizes: '96x96', priority: 'high' },
            { sizes: '64x64', priority: 'medium' },
            { sizes: '48x48', priority: 'low' },
            { sizes: '32x32', priority: 'fallback' }
        ];
        
        faviconConfigs.forEach(config => {
            const favicon = document.createElement('link');
            favicon.rel = 'icon';
            favicon.type = 'image/png';
            favicon.sizes = config.sizes;
            favicon.href = '/images/logo.png?' + new Date().getTime();
            favicon.setAttribute('data-priority', config.priority);
            document.head.appendChild(favicon);
            console.log(`✅ Logo asli ditambahkan: ${config.sizes} (${config.priority})`);
        });
        
        // Shortcut icon untuk kompatibilitas maksimal
        const shortcut = document.createElement('link');
        shortcut.rel = 'shortcut icon';
        shortcut.type = 'image/png';
        shortcut.href = '/images/logo.png?' + new Date().getTime();
        document.head.appendChild(shortcut);
        
        console.log('🚀 LOGO ASLI berhasil MENGGANTI favicon Laravel!');
    }
    
    // Jalankan SEGERA
    forceReplaceWithOriginalLogo();
    
    // Force refresh BERKALI-KALI untuk memastikan
    setTimeout(forceReplaceWithOriginalLogo, 100);
    setTimeout(forceReplaceWithOriginalLogo, 500);
    setTimeout(forceReplaceWithOriginalLogo, 1000);
    setTimeout(forceReplaceWithOriginalLogo, 2000);
    
    // CSS untuk memastikan logo asli terlihat JELAS
    const style = document.createElement('style');
    style.textContent = `
        /* MEMAKSA logo asli terlihat BESAR dan MENGGANTI Laravel */
        link[rel*="icon"] {
            image-rendering: -webkit-optimize-contrast !important;
            image-rendering: crisp-edges !important;
            image-rendering: high-quality !important;
        }
        
        /* Prioritas TERTINGGI untuk ukuran besar */
        link[rel="icon"][sizes="128x128"] {
            -webkit-filter: contrast(1.6) brightness(1.5) saturate(1.5) !important;
            filter: contrast(1.6) brightness(1.5) saturate(1.5) !important;
        }
        
        /* Sembunyikan favicon Laravel jika masih ada */
        link[href*="favicon.ico"],
        link[href*="laravel"] {
            display: none !important;
        }
    `;
    document.head.appendChild(style);
    
    console.log('🎯 Logo Laravel DIHAPUS, Logo asli DIPAKSA tampil!');
});