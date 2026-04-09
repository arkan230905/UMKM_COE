// Optimized Sales Page JavaScript with Real-time Search and Fast Barcode Scanning

// Global variables for search and barcode functionality
let searchTimeout = null;
let barcodeBuffer = '';
let barcodeTimeout = null;
let isProcessing = false;
const SEARCH_DELAY = 300; // 300ms delay for real-time search
const BARCODE_TIMEOUT = 100; // 100ms timeout for barcode completion
const MIN_SEARCH_LENGTH = 1; // Minimum search length
const MIN_BARCODE_LENGTH = 3; // Minimum barcode length

// Global utility functions
function formatCurrency(value) {
    if (value === null || value === undefined || isNaN(value)) {
        return 'Rp 0';
    }
    const roundedValue = Math.round(parseFloat(value) * 1000) / 1000;
    return 'Rp ' + roundedValue.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 3 });
}

function parseCurrency(formattedValue) {
    if (!formattedValue) return 0;
    return parseFloat(formattedValue.toString().replace(/[^\d]/g, '')) || 0;
}

// Real-time product search function
async function searchProducts(query) {
    if (query.length < MIN_SEARCH_LENGTH) {
        hideSearchResults();
        return;
    }

    try {
        const response = await fetch(`/transaksi/api/products/search?q=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            showSearchResults(data.data, query);
        } else {
            showNoResults(query);
        }
    } catch (error) {
        console.error('Search error:', error);
        hideSearchResults();
    }
}